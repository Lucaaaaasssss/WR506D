<template>
  <div>
    <div class="mb-8">
      <h1 class="text-3xl font-bold text-gray-900 mb-4">Films</h1>

      <!-- Filters -->
      <div class="bg-white p-4 rounded-lg shadow space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Recherche</label>
            <input
              v-model="filters.search"
              type="text"
              placeholder="Rechercher un film..."
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
              @input="loadMovies"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Trier par</label>
            <select
              v-model="filters.sort"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
              @change="loadMovies"
            >
              <option value="createdAt:desc">Plus récents</option>
              <option value="createdAt:asc">Plus anciens</option>
              <option value="name:asc">Nom (A-Z)</option>
              <option value="name:desc">Nom (Z-A)</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Réalisateur</label>
            <input
              v-model="filters.director"
              type="text"
              placeholder="Nom du réalisateur..."
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
              @input="loadMovies"
            />
          </div>
        </div>
      </div>
    </div>

    <div v-if="loading" class="flex justify-center items-center py-12">
      <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
    </div>

    <div v-else-if="error" class="bg-red-50 p-4 rounded-md">
      <p class="text-red-800">{{ error }}</p>
    </div>

    <div v-else-if="movies.length === 0" class="text-center py-8">
      <p class="text-gray-600">Aucun film trouvé.</p>
    </div>

    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <div
        v-for="movie in movies"
        :key="movie.id"
        class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow"
      >
        <div class="h-64 bg-gray-200 flex items-center justify-center">
          <img
            v-if="movie.poster?.contentUrl"
            :src="movie.poster.contentUrl"
            :alt="movie.name"
            class="w-full h-full object-cover"
          />
          <span v-else class="text-gray-400">Pas d'image</span>
        </div>
        <div class="p-4">
          <h3 class="text-xl font-bold text-gray-900 mb-2">{{ movie.name }}</h3>
          <p class="text-gray-600 text-sm mb-2 line-clamp-2">
            {{ movie.description || 'Pas de description' }}
          </p>
          <p v-if="getDirectorName(movie.director)" class="text-sm text-gray-500 mb-2">
            Réalisateur: {{ getDirectorName(movie.director) }}
          </p>
          <div class="flex justify-between items-center">
            <span class="text-sm text-gray-500">
              {{ movie.duration ? `${movie.duration} min` : '' }}
            </span>
            <router-link
              :to="{ name: 'movie-detail', params: { id: movie.id } }"
              class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700"
            >
              Voir détails
            </router-link>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '../../services/api'

const movies = ref([])
const directors = ref({})
const loading = ref(false)
const error = ref(null)

const filters = ref({
  search: '',
  sort: 'createdAt:desc',
  director: ''
})

const loadDirectors = async () => {
  try {
    const response = await api.get('/api/directors?itemsPerPage=1000')
    const directorsList = response.data['hydra:member'] || response.data.member || []

    // Créer un map ID -> Director pour un accès rapide
    directors.value = {}
    directorsList.forEach(director => {
      directors.value[director.id] = director
    })
  } catch (err) {
    console.error('Erreur lors du chargement des réalisateurs', err)
  }
}

const getDirectorName = (directorIri) => {
  if (!directorIri) return null

  // Si c'est déjà un objet avec firstname et lastname
  if (typeof directorIri === 'object' && directorIri.firstname && directorIri.lastname) {
    return `${directorIri.firstname} ${directorIri.lastname}`
  }

  // Si c'est un IRI (string), extraire l'ID et chercher dans le map
  if (typeof directorIri === 'string') {
    const id = parseInt(directorIri.split('/').pop())
    const director = directors.value[id]
    if (director) {
      return `${director.firstname} ${director.lastname}`
    }
  }

  return null
}

const loadMovies = async () => {
  loading.value = true
  error.value = null

  try {
    const params = new URLSearchParams()

    if (filters.value.search) {
      params.append('name', filters.value.search)
    }

    if (filters.value.director) {
      params.append('director.name', filters.value.director)
    }

    if (filters.value.sort) {
      const [field, order] = filters.value.sort.split(':')
      params.append(`order[${field}]`, order)
    }

    // Récupérer tous les films (augmenter le nombre par page)
    params.append('itemsPerPage', '1000')

    const response = await api.get(`/api/movies?${params.toString()}`)

    // API Platform peut retourner soit "hydra:member" soit "member"
    let allMovies = response.data['hydra:member'] || response.data.member || []

    // Vérifier que c'est bien un tableau
    if (!Array.isArray(allMovies)) {
      console.error('Response data:', response.data)
      allMovies = []
    }

    // Filter client-side for draft=false and online=true
    movies.value = allMovies.filter(movie =>
      movie && !movie.draft && movie.online
    )
  } catch (err) {
    error.value = 'Erreur lors du chargement des films'
    console.error(err)
  } finally {
    loading.value = false
  }
}

onMounted(async () => {
  await loadDirectors()
  loadMovies()
})
</script>
