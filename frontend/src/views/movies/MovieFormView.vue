<template>
  <div class="max-w-3xl mx-auto">
    <div class="bg-white rounded-lg shadow-md p-6">
      <h1 class="text-3xl font-bold text-gray-900 mb-6">
        {{ isEdit ? 'Modifier un film' : 'Nouveau film' }}
      </h1>

      <form @submit.prevent="handleSubmit" class="space-y-6">
        <div v-if="error" class="bg-red-50 p-4 rounded-md">
          <p class="text-red-800">{{ error }}</p>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">
            Titre <span class="text-red-500">*</span>
          </label>
          <input
            v-model="form.name"
            type="text"
            required
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-gray-900 focus:border-gray-900"
          />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
          <textarea
            v-model="form.description"
            rows="4"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-gray-900 focus:border-gray-900"
          ></textarea>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Durée (minutes)</label>
            <input
              v-model.number="form.duration"
              type="number"
              min="30"
              max="400"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-gray-900 focus:border-gray-900"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Date de sortie</label>
            <input
              v-model="form.releaseData"
              type="date"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-gray-900 focus:border-gray-900"
            />
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Budget (€)</label>
            <input
              v-model.number="form.budget"
              type="number"
              step="0.01"
              min="0"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-gray-900 focus:border-gray-900"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Nombre d'entrées</label>
            <input
              v-model.number="form.nbEntries"
              type="number"
              min="0"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-gray-900 focus:border-gray-900"
            />
          </div>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">URL</label>
          <input
            v-model="form.url"
            type="url"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-gray-900 focus:border-gray-900"
          />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">
            Réalisateur <span class="text-red-500">*</span>
            <span v-if="directors.length > 0" class="text-xs text-gray-500">({{ directors.length }} réalisateurs)</span>
          </label>
          <select
            v-model="form.director"
            required
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-gray-900 focus:border-gray-900 text-gray-900 bg-white"
          >
            <option value="" class="text-gray-500">Sélectionnez un réalisateur</option>
            <option
              v-for="director in directors"
              :key="director.id"
              :value="`/api/directors/${director.id}`"
              class="text-gray-900 bg-white"
            >
              {{ director.firstname }} {{ director.lastname }}
            </option>
          </select>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Poster (Image)</label>
          <input
            type="file"
            accept="image/*"
            @change="handleFileChange"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-gray-900 focus:border-gray-900"
          />
          <p class="text-sm text-gray-500 mt-1">Format: JPG, PNG (max 5MB)</p>
        </div>

        <div class="flex items-center space-x-4">
          <label class="flex items-center">
            <input
              v-model="form.draft"
              type="checkbox"
              class="rounded border-gray-300 text-gray-900 focus:ring-gray-900"
            />
            <span class="ml-2 text-sm text-gray-700">Brouillon</span>
          </label>

          <label class="flex items-center">
            <input
              v-model="form.online"
              type="checkbox"
              class="rounded border-gray-300 text-gray-900 focus:ring-gray-900"
            />
            <span class="ml-2 text-sm text-gray-700">En ligne</span>
          </label>
        </div>

        <div class="flex space-x-4">
          <button
            type="submit"
            :disabled="submitting"
            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-gray-900 hover:bg-black disabled:opacity-50"
          >
            {{ submitting ? 'Enregistrement...' : (isEdit ? 'Mettre à jour' : 'Créer') }}
          </button>
          <button
            type="button"
            @click="$router.back()"
            class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
          >
            Annuler
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import api from '../../services/api'

const route = useRoute()
const router = useRouter()

const isEdit = computed(() => !!route.params.id)

const form = ref({
  name: '',
  description: '',
  duration: null,
  releaseData: '',
  budget: null,
  nbEntries: null,
  url: '',
  director: '',
  draft: false,
  online: true
})

const directors = ref([])
const posterFile = ref(null)
const error = ref(null)
const submitting = ref(false)

const loadDirectors = async () => {
  try {
    const response = await api.get('/api/directors')
    directors.value = response.data['hydra:member'] || response.data.member || response.data
    console.log('Réalisateurs chargés:', directors.value.length, 'items')
  } catch (err) {
    console.error('Erreur lors du chargement des réalisateurs', err)
  }
}

const loadMovie = async () => {
  if (!isEdit.value) return

  try {
    const response = await api.get(`/api/movies/${route.params.id}`)
    const movie = response.data

    form.value = {
      name: movie.name || '',
      description: movie.description || '',
      duration: movie.duration || null,
      releaseData: movie.releaseData ? movie.releaseData.split('T')[0] : '',
      budget: movie.budget || null,
      nbEntries: movie.nbEntries || null,
      url: movie.url || '',
      director: movie.director ? `/api/directors/${movie.director.id}` : '',
      draft: movie.draft || false,
      online: movie.online !== undefined ? movie.online : true
    }
  } catch (err) {
    error.value = 'Erreur lors du chargement du film'
    console.error(err)
  }
}

const handleFileChange = (event) => {
  posterFile.value = event.target.files[0]
}

const uploadPoster = async () => {
  if (!posterFile.value) return null

  try {
    console.log('Fichier à uploader:', posterFile.value)

    const formData = new FormData()
    formData.append('file', posterFile.value)

    console.log('FormData créé, envoi de la requête...')

    // Ne PAS définir Content-Type manuellement pour multipart/form-data
    // Le navigateur doit le faire automatiquement avec le boundary
    const response = await api.post('/api/media_objects', formData)

    console.log('Status de la réponse:', response.status)
    console.log('Headers de la réponse:', response.headers)
    console.log('Réponse complète:', response)
    console.log('Type de response.data:', typeof response.data)
    console.log('Réponse data:', response.data)
    console.log('Réponse data stringifiée:', JSON.stringify(response.data))

    if (!response.data) {
      console.error('response.data est null ou undefined')
      throw new Error('Pas de données retournées par l\'API')
    }

    // API Platform retourne @id
    if (response.data['@id']) {
      console.log('ID trouvé via @id:', response.data['@id'])
      return response.data['@id']
    } else if (response.data.id) {
      console.log('ID trouvé via id:', response.data.id)
      return `/api/media_objects/${response.data.id}`
    } else {
      console.error('Aucun ID trouvé dans la réponse')
      console.log('Clés disponibles:', Object.keys(response.data))
      throw new Error('Impossible de récupérer l\'ID de l\'image uploadée')
    }
  } catch (err) {
    console.error('Erreur lors de l\'upload de l\'image', err)
    console.error('Détails de l\'erreur:', err.response)
    console.error('Data de l\'erreur:', err.response?.data)
    throw err
  }
}

const handleSubmit = async () => {
  error.value = null
  submitting.value = true

  try {
    let posterIri = null
    if (posterFile.value) {
      posterIri = await uploadPoster()
    }

    const movieData = {
      name: form.value.name,
      description: form.value.description || null,
      duration: form.value.duration || null,
      releaseData: form.value.releaseData || null,
      budget: form.value.budget || null,
      nbEntries: form.value.nbEntries || null,
      url: form.value.url || null,
      director: form.value.director,
      draft: form.value.draft,
      online: form.value.online
    }

    if (posterIri) {
      movieData.poster = posterIri
    }

    if (isEdit.value) {
      await api.put(`/api/movies/${route.params.id}`, movieData, {
        headers: {
          'Content-Type': 'application/ld+json'
        }
      })
    } else {
      await api.post('/api/movies', movieData, {
        headers: {
          'Content-Type': 'application/ld+json'
        }
      })
    }

    router.push('/movies')
  } catch (err) {
    console.error('Erreur complète:', err)
    console.error('Réponse:', err.response?.data)

    // Extraire le message d'erreur le plus détaillé possible
    const errorData = err.response?.data
    if (errorData?.['hydra:description']) {
      error.value = errorData['hydra:description']
    } else if (errorData?.detail) {
      error.value = errorData.detail
    } else if (errorData?.message) {
      error.value = errorData.message
    } else if (errorData?.violations && errorData.violations.length > 0) {
      error.value = errorData.violations.map(v => `${v.propertyPath}: ${v.message}`).join(', ')
    } else {
      error.value = 'Erreur lors de l\'enregistrement: ' + (err.message || 'Erreur inconnue')
    }
  } finally {
    submitting.value = false
  }
}

onMounted(() => {
  loadDirectors()
  if (isEdit.value) {
    loadMovie()
  }
})
</script>
