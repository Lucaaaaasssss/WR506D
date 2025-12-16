<template>
  <div>
    <div v-if="loading" class="text-center py-8">
      <p class="text-gray-600">Chargement...</p>
    </div>

    <div v-else-if="error" class="bg-red-50 p-4 rounded-md">
      <p class="text-red-800">{{ error }}</p>
    </div>

    <div v-else-if="movie" class="space-y-6">
      <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="grid grid-cols-1 lg:grid-cols-3">
          <div class="lg:col-span-1">
            <div class="h-96 bg-gray-200 flex items-center justify-center">
              <img
                v-if="movie.poster?.contentUrl"
                :src="movie.poster.contentUrl"
                :alt="movie.name"
                class="w-full h-full object-cover"
              />
              <span v-else class="text-gray-400">Pas d'image</span>
            </div>
          </div>
          <div class="lg:col-span-2 p-6">
            <div class="flex justify-between items-start mb-4 flex-wrap gap-2">
              <h1 class="text-3xl font-bold text-gray-900">{{ movie.name }}</h1>
              <div v-if="authStore.isAdmin" class="flex gap-2 flex-shrink-0">
                <router-link
                  :to="{ name: 'movie-edit', params: { id: movie.id } }"
                  class="inline-flex items-center px-3 py-2 border border-gray-300 text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
                >
                  Modifier
                </router-link>
                <button
                  @click="deleteMovie"
                  class="inline-flex items-center px-3 py-2 border border-red-300 text-sm leading-4 font-medium rounded-md text-red-700 bg-white hover:bg-red-50"
                >
                  Supprimer
                </button>
              </div>
            </div>

            <div class="space-y-3 text-gray-700">
              <p v-if="director">
                <span class="font-semibold">Réalisateur:</span> {{ director.firstname }} {{ director.lastname }}
              </p>
              <p v-if="movie.duration">
                <span class="font-semibold">Durée:</span> {{ movie.duration }} minutes
              </p>
              <p v-if="movie.releaseData">
                <span class="font-semibold">Date de sortie:</span>
                {{ new Date(movie.releaseData).toLocaleDateString('fr-FR') }}
              </p>
              <p v-if="movie.budget && movie.budget > 0">
                <span class="font-semibold">Budget:</span> {{ formatBudget(movie.budget) }}
              </p>
              <p v-if="movie.nbEntries && movie.nbEntries > 0">
                <span class="font-semibold">Entrées:</span> {{ movie.nbEntries.toLocaleString('fr-FR') }}
              </p>
              <div v-if="movie.categories && movie.categories.length > 0">
                <span class="font-semibold">Catégories:</span>
                <span
                  v-for="category in movie.categories"
                  :key="category.id"
                  class="inline-block bg-indigo-100 text-indigo-800 text-xs px-2 py-1 rounded ml-2"
                >
                  {{ category.name }}
                </span>
              </div>
            </div>

            <div v-if="movie.description" class="mt-4">
              <h3 class="font-semibold text-gray-900 mb-2">Description</h3>
              <p class="text-gray-700">{{ movie.description }}</p>
            </div>

            <div v-if="movie.url" class="mt-4">
              <a
                :href="movie.url"
                target="_blank"
                class="text-indigo-600 hover:text-indigo-800"
              >
                Voir plus d'informations
              </a>
            </div>
          </div>
        </div>
      </div>

      <!-- Comments Section -->
      <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-2xl font-bold text-gray-900 mb-4">Commentaires</h2>

        <!-- Add Comment Form -->
        <div v-if="authStore.isAuthenticated" class="mb-6">
          <form @submit.prevent="submitComment">
            <textarea
              v-model="newComment"
              rows="3"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
              placeholder="Ajouter un commentaire..."
              required
            ></textarea>
            <div class="mt-2">
              <button
                type="submit"
                :disabled="submittingComment"
                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50"
              >
                {{ submittingComment ? 'Envoi...' : 'Publier' }}
              </button>
            </div>
          </form>
        </div>
        <div v-else class="mb-6 p-4 bg-gray-50 rounded-md">
          <p class="text-gray-600">
            <router-link to="/login" class="text-indigo-600 hover:text-indigo-800">
              Connectez-vous
            </router-link>
            pour laisser un commentaire.
          </p>
        </div>

        <!-- Comments List -->
        <div v-if="loadingComments" class="text-center py-4">
          <p class="text-gray-600">Chargement des commentaires...</p>
        </div>

        <div v-else-if="comments.length === 0" class="text-center py-4">
          <p class="text-gray-600">Aucun commentaire pour le moment.</p>
        </div>

        <div v-else class="space-y-4">
          <div
            v-for="comment in comments"
            :key="comment.id"
            class="border-b border-gray-200 pb-4 last:border-b-0"
          >
            <div class="flex justify-between items-start">
              <div>
                <p class="font-semibold text-gray-900">
                  {{ getAuthorName(comment.author) }}
                </p>
                <p class="text-sm text-gray-500">
                  {{ formatCommentDate(comment.createdAt) }}
                </p>
              </div>
              <button
                v-if="authStore.isAdmin"
                @click="deleteComment(comment.id)"
                class="text-sm text-red-600 hover:text-red-800"
              >
                Supprimer
              </button>
            </div>
            <p class="mt-2 text-gray-700">{{ comment.content }}</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '../../stores/auth'
import api from '../../services/api'

const route = useRoute()
const router = useRouter()
const authStore = useAuthStore()

const movie = ref(null)
const director = ref(null)
const comments = ref([])
const newComment = ref('')
const loading = ref(false)
const loadingComments = ref(false)
const submittingComment = ref(false)
const error = ref(null)

const loadMovie = async () => {
  loading.value = true
  error.value = null

  try {
    const response = await api.get(`/api/movies/${route.params.id}`)
    movie.value = response.data

    // Charger le réalisateur si c'est un IRI (string)
    if (movie.value.director && typeof movie.value.director === 'string') {
      const directorResponse = await api.get(movie.value.director)
      director.value = directorResponse.data
    } else if (movie.value.director && typeof movie.value.director === 'object') {
      director.value = movie.value.director
    }
  } catch (err) {
    error.value = 'Erreur lors du chargement du film'
    console.error(err)
  } finally {
    loading.value = false
  }
}

const loadComments = async () => {
  loadingComments.value = true

  try {
    const response = await api.get(`/api/comments?movie=/api/movies/${route.params.id}&order[createdAt]=desc`)
    const commentsData = response.data['hydra:member'] || response.data.member || response.data

    console.log('Commentaires chargés:', commentsData)

    // Vérifier que c'est bien un tableau
    if (Array.isArray(commentsData)) {
      comments.value = commentsData
    } else {
      console.error('Les commentaires ne sont pas un tableau:', commentsData)
      comments.value = []
    }
  } catch (err) {
    console.error('Erreur lors du chargement des commentaires', err)
    comments.value = []
  } finally {
    loadingComments.value = false
  }
}

const submitComment = async () => {
  if (!newComment.value.trim()) return

  submittingComment.value = true

  try {
    await api.post('/api/comments', {
      content: newComment.value,
      movie: `/api/movies/${route.params.id}`,
      author: `/api/users/${authStore.user.id}`
    }, {
      headers: {
        'Content-Type': 'application/ld+json'
      }
    })

    newComment.value = ''
    await loadComments()
  } catch (err) {
    alert('Erreur lors de la publication du commentaire')
    console.error(err)
  } finally {
    submittingComment.value = false
  }
}

const deleteComment = async (commentId) => {
  if (!confirm('Êtes-vous sûr de vouloir supprimer ce commentaire ?')) return

  try {
    await api.delete(`/api/comments/${commentId}`)
    await loadComments()
  } catch (err) {
    alert('Erreur lors de la suppression du commentaire')
    console.error(err)
  }
}

const deleteMovie = async () => {
  if (!confirm('Êtes-vous sûr de vouloir supprimer ce film ?')) return

  try {
    await api.delete(`/api/movies/${route.params.id}`)
    router.push('/movies')
  } catch (err) {
    alert('Erreur lors de la suppression du film')
    console.error(err)
  }
}

const formatBudget = (budget) => {
  if (!budget || budget <= 0) return '0 €'

  try {
    return new Intl.NumberFormat('fr-FR', {
      style: 'currency',
      currency: 'EUR'
    }).format(budget)
  } catch (err) {
    return `${budget} €`
  }
}

const getAuthorName = (author) => {
  if (!author) return 'Utilisateur'

  if (author.firstname && author.lastname) {
    return `${author.firstname} ${author.lastname}`
  }

  if (author.firstname) return author.firstname
  if (author.lastname) return author.lastname

  return author.email || 'Utilisateur'
}

const formatCommentDate = (dateString) => {
  if (!dateString) return 'Date inconnue'

  try {
    const date = new Date(dateString)
    if (isNaN(date.getTime())) return 'Date invalide'

    return date.toLocaleDateString('fr-FR', { year: 'numeric', month: 'long', day: 'numeric' })
  } catch (err) {
    console.error('Erreur de formatage de date:', err)
    return 'Date invalide'
  }
}

onMounted(() => {
  loadMovie()
  loadComments()
})
</script>
