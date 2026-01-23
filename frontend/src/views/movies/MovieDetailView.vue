<template>
  <div class="max-w-7xl mx-auto">
    <div v-if="loading" class="text-center py-8">
      <p class="text-gray-600">Chargement...</p>
    </div>

    <div v-else-if="error" class="bg-red-50 p-4 rounded-md">
      <p class="text-red-800">{{ error }}</p>
    </div>

    <div v-else-if="movie">
      <!-- Contenu principal -->
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 mb-12">
        <!-- Affiche -->
        <div class="lg:col-span-4">
          <div class="sticky top-6">
            <div class="aspect-[2/3] bg-gray-200 overflow-hidden shadow-2xl">
              <img
                :src="movie.poster?.contentUrl || `https://picsum.photos/seed/${movie.id}/400/600`"
                :alt="movie.name"
                class="w-full h-full object-cover"
              />
            </div>
          </div>
        </div>

        <!-- Informations -->
        <div class="lg:col-span-8 space-y-8">
          <!-- Header avec titre et actions admin -->
          <div class="flex justify-between items-start flex-wrap gap-4">
            <div>
              <h1 class="text-4xl font-black text-gray-900 mb-2">{{ movie.name }}</h1>
              <div class="flex items-center gap-3 text-sm text-gray-500">
                <span v-if="movie.releaseData">{{ new Date(movie.releaseData).getFullYear() }}</span>
                <span v-if="movie.duration">{{ movie.duration }} min</span>
                <span v-if="director">{{ director.firstname }} {{ director.lastname }}</span>
              </div>
            </div>
            <div v-if="authStore.isAdmin" class="flex gap-2">
              <router-link
                :to="{ name: 'movie-edit', params: { id: movie.id } }"
                class="px-4 py-2 border border-gray-900 text-sm font-medium text-gray-900 hover:bg-gray-900 hover:text-white transition-colors"
              >
                Modifier
              </router-link>
              <button
                @click="deleteMovie"
                class="px-4 py-2 border border-red-600 text-sm font-medium text-red-600 hover:bg-red-600 hover:text-white transition-colors"
              >
                Supprimer
              </button>
            </div>
          </div>
          <!-- Catégories -->
          <div v-if="movie.categories && movie.categories.length > 0" class="flex flex-wrap gap-2">
            <span
              v-for="category in movie.categories"
              :key="category.id"
              class="px-3 py-1 bg-gray-900 text-white text-sm font-medium uppercase tracking-wide"
            >
              {{ category.name }}
            </span>
          </div>

          <!-- Description -->
          <div v-if="movie.description" class="prose max-w-none">
            <p class="text-lg text-gray-700 leading-relaxed">{{ movie.description }}</p>
          </div>

          <!-- Métadonnées -->
          <div class="grid grid-cols-2 md:grid-cols-3 gap-6 py-6 border-t border-b border-gray-200">
            <div v-if="movie.budget && movie.budget > 0">
              <div class="text-xs text-gray-500 uppercase tracking-wider mb-1">Budget</div>
              <div class="text-lg font-bold text-gray-900">{{ formatBudget(movie.budget) }}</div>
            </div>
            <div v-if="movie.nbEntries && movie.nbEntries > 0">
              <div class="text-xs text-gray-500 uppercase tracking-wider mb-1">Entrées</div>
              <div class="text-lg font-bold text-gray-900">{{ movie.nbEntries.toLocaleString('fr-FR') }}</div>
            </div>
            <div v-if="movie.releaseData">
              <div class="text-xs text-gray-500 uppercase tracking-wider mb-1">Sortie</div>
              <div class="text-lg font-bold text-gray-900">{{ new Date(movie.releaseData).toLocaleDateString('fr-FR') }}</div>
            </div>
          </div>

          <!-- Lien externe -->
          <div v-if="movie.url">
            <a
              :href="movie.url"
              target="_blank"
              class="inline-flex items-center gap-2 px-6 py-3 bg-gray-900 text-white font-medium hover:bg-black transition-colors"
            >
              Plus d'informations
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
              </svg>
            </a>
          </div>
        </div>
      </div>

      <!-- Comments Section -->
      <div class="border-t border-gray-200 pt-12">
        <h2 class="text-3xl font-black text-gray-900 mb-8">Commentaires <span class="text-gray-400">({{ comments.length }})</span></h2>

        <!-- Add Comment Form -->
        <div v-if="authStore.isAuthenticated" class="mb-10">
          <form @submit.prevent="submitComment" class="space-y-3">
            <textarea
              v-model="newComment"
              rows="4"
              class="w-full px-4 py-3 border border-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-900 focus:border-transparent text-gray-900 placeholder-gray-400"
              placeholder="Partagez votre avis sur ce film..."
              required
            ></textarea>
            <div class="flex justify-end">
              <button
                type="submit"
                :disabled="submittingComment"
                class="px-6 py-2.5 bg-gray-900 text-white font-medium hover:bg-black disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
              >
                {{ submittingComment ? 'Envoi en cours...' : 'Publier le commentaire' }}
              </button>
            </div>
          </form>
        </div>
        <div v-else class="mb-10 p-6 border border-gray-300 text-center">
          <p class="text-gray-600">
            <router-link to="/login" class="font-bold text-gray-900 hover:underline">
              Connectez-vous
            </router-link>
            pour laisser un commentaire.
          </p>
        </div>

        <!-- Comments List -->
        <div v-if="loadingComments" class="text-center py-8">
          <p class="text-gray-500">Chargement des commentaires...</p>
        </div>

        <div v-else-if="comments.length === 0" class="text-center py-12">
          <p class="text-gray-500">Aucun commentaire pour le moment. Soyez le premier à donner votre avis !</p>
        </div>

        <div v-else class="space-y-6">
          <div
            v-for="comment in comments"
            :key="comment.id"
            class="pb-6 border-b border-gray-200 last:border-b-0"
          >
            <div class="flex justify-between items-start mb-3">
              <div>
                <p class="font-bold text-gray-900 text-lg">
                  {{ getAuthorName(comment.author) }}
                </p>
                <p class="text-sm text-gray-500">
                  {{ formatCommentDate(comment.createdAt) }}
                </p>
              </div>
              <button
                v-if="authStore.isAdmin"
                @click="deleteComment(comment.id)"
                class="text-sm text-red-600 hover:text-red-800 font-medium"
              >
                Supprimer
              </button>
            </div>
            <p class="text-gray-700 leading-relaxed">{{ comment.content }}</p>
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
    const movieIri = encodeURIComponent(`/api/movies/${route.params.id}`)
    const response = await api.get(`/api/comments?movie=${movieIri}&order[createdAt]=desc`)
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
