<template>
  <div>
    <h1 class="text-3xl font-bold text-gray-900 mb-6">Administration</h1>

    <div class="bg-white rounded-lg shadow-md p-6">
      <h2 class="text-2xl font-bold text-gray-900 mb-4">Gestion des utilisateurs</h2>

      <div v-if="loading" class="text-center py-8">
        <p class="text-gray-600">Chargement...</p>
      </div>

      <div v-else-if="error" class="bg-red-50 p-4 rounded-md">
        <p class="text-red-800">{{ error }}</p>
      </div>

      <div v-else>
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th
                  class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                >
                  ID
                </th>
                <th
                  class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                >
                  Email
                </th>
                <th
                  class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                >
                  Nom
                </th>
                <th
                  class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                >
                  Rôles
                </th>
                <th
                  class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                >
                  Actions
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="user in users" :key="user.id">
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {{ user.id }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {{ user.email }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {{ user.firstname }} {{ user.lastname }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  <span
                    v-for="role in user.roles"
                    :key="role"
                    class="inline-block bg-gray-100 text-gray-900 text-xs px-2 py-1 rounded mr-1"
                  >
                    {{ role }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                  <button
                    v-if="user.roles && !user.roles.includes('ROLE_ADMIN')"
                    @click="promoteToAdmin(user.id)"
                    class="text-gray-900 hover:text-black mr-3"
                  >
                    Promouvoir Admin
                  </button>
                  <button
                    v-if="user.roles && user.roles.includes('ROLE_ADMIN') && authStore.user.id !== user.id"
                    @click="demoteFromAdmin(user.id)"
                    class="text-orange-600 hover:text-orange-900 mr-3"
                  >
                    Rétrograder
                  </button>
                  <button
                    v-if="authStore.user.id !== user.id"
                    @click="deleteUser(user.id)"
                    class="text-red-600 hover:text-red-900"
                  >
                    Supprimer
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
      <div class="bg-white rounded-lg shadow-md p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-2">Total Utilisateurs</h3>
        <p class="text-3xl font-bold text-gray-900">{{ Array.isArray(users) ? users.length : 0 }}</p>
      </div>
      <div class="bg-white rounded-lg shadow-md p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-2">Administrateurs</h3>
        <p class="text-3xl font-bold text-gray-900">
          {{ Array.isArray(users) ? users.filter(u => u.roles && u.roles.includes('ROLE_ADMIN')).length : 0 }}
        </p>
      </div>
      <div class="bg-white rounded-lg shadow-md p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-2">Utilisateurs simples</h3>
        <p class="text-3xl font-bold text-gray-900">
          {{ Array.isArray(users) ? users.filter(u => u.roles && !u.roles.includes('ROLE_ADMIN')).length : 0 }}
        </p>
      </div>
    </div>

    <!-- Content Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
      <div class="bg-white rounded-lg shadow-md p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Films</h3>
        <div class="space-y-2">
          <p class="text-sm text-gray-600">
            Total: <span class="font-bold">{{ stats.moviesTotal }}</span>
          </p>
          <p class="text-sm text-gray-600">
            En ligne: <span class="font-bold">{{ stats.moviesOnline }}</span>
          </p>
          <p class="text-sm text-gray-600">
            Brouillons: <span class="font-bold">{{ stats.moviesDraft }}</span>
          </p>
        </div>
      </div>
      <div class="bg-white rounded-lg shadow-md p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Commentaires</h3>
        <div class="space-y-2">
          <p class="text-sm text-gray-600">
            Total: <span class="font-bold">{{ stats.commentsTotal }}</span>
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useAuthStore } from '../../stores/auth'
import api from '../../services/api'

const authStore = useAuthStore()

const users = ref([])
const stats = ref({
  moviesTotal: 0,
  moviesOnline: 0,
  moviesDraft: 0,
  commentsTotal: 0
})
const loading = ref(false)
const error = ref(null)

const loadUsers = async () => {
  loading.value = true
  error.value = null

  try {
    const response = await api.get('/api/users')
    const usersData = response.data['hydra:member'] || response.data.member || response.data

    // Vérifier que c'est bien un tableau
    users.value = Array.isArray(usersData) ? usersData : []

    console.log('Users loaded:', users.value)
  } catch (err) {
    error.value = 'Erreur lors du chargement des utilisateurs'
    console.error(err)
  } finally {
    loading.value = false
  }
}

const loadStats = async () => {
  try {
    const [moviesResponse, commentsResponse] = await Promise.all([
      api.get('/api/movies?pagination=false'),
      api.get('/api/comments?pagination=false')
    ])

    console.log('Movies response:', moviesResponse.data)
    console.log('Comments response:', commentsResponse.data)

    const movies = moviesResponse.data['hydra:member'] || moviesResponse.data.member || moviesResponse.data
    const comments = commentsResponse.data['hydra:member'] || commentsResponse.data.member || commentsResponse.data

    console.log('Movies array:', movies)
    console.log('Comments array:', comments)

    // Vérifier que c'est un tableau
    const moviesArray = Array.isArray(movies) ? movies : []
    const commentsArray = Array.isArray(comments) ? comments : []

    stats.value = {
      moviesTotal: moviesArray.length,
      moviesOnline: moviesArray.filter(m => m.online && !m.draft).length,
      moviesDraft: moviesArray.filter(m => m.draft).length,
      commentsTotal: commentsArray.length
    }

    console.log('Stats:', stats.value)
  } catch (err) {
    console.error('Erreur lors du chargement des statistiques', err)
  }
}

const promoteToAdmin = async (userId) => {
  if (!confirm('Êtes-vous sûr de vouloir promouvoir cet utilisateur en administrateur ?')) return

  try {
    await api.patch(`/api/users/${userId}`, {
      roles: ['ROLE_USER', 'ROLE_ADMIN']
    }, {
      headers: {
        'Content-Type': 'application/merge-patch+json'
      }
    })
    await loadUsers()
  } catch (err) {
    alert('Erreur lors de la promotion de l\'utilisateur')
    console.error(err)
  }
}

const demoteFromAdmin = async (userId) => {
  if (!confirm('Êtes-vous sûr de vouloir rétrograder cet administrateur ?')) return

  try {
    await api.patch(`/api/users/${userId}`, {
      roles: ['ROLE_USER']
    }, {
      headers: {
        'Content-Type': 'application/merge-patch+json'
      }
    })
    await loadUsers()
  } catch (err) {
    alert('Erreur lors de la rétrogradation de l\'utilisateur')
    console.error(err)
  }
}

const deleteUser = async (userId) => {
  if (!confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?')) return

  try {
    await api.delete(`/api/users/${userId}`)
    await loadUsers()
  } catch (err) {
    alert('Erreur lors de la suppression de l\'utilisateur')
    console.error(err)
  }
}

onMounted(() => {
  loadUsers()
  loadStats()
})
</script>
