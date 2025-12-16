<template>
    <div>
        <div class="sm:flex sm:items-center mb-8">
            <div class="sm:flex-auto">
                <h1 class="text-3xl font-bold text-gray-900">Réalisateurs</h1>
                <p class="mt-2 text-sm text-gray-700">
                    Liste de tous les réalisateurs de films
                </p>
            </div>
        </div>

        <div v-if="loading" class="flex justify-center items-center py-12">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
        </div>

        <div v-else-if="error" class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded">
            {{ error }}
        </div>

        <div v-else class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
            <div
                v-for="director in directors"
                :key="director.id"
                class="bg-white overflow-hidden shadow-lg rounded-lg hover:shadow-xl transition duration-300 cursor-pointer"
                @click="goToDirector(director.id)"
            >
                <div class="px-6 py-8">
                    <div class="flex items-center justify-center mb-4">
                        <div class="bg-blue-100 rounded-full p-4">
                            <svg class="w-12 h-12 text-blue-600" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                            </svg>
                        </div>
                    </div>

                    <div class="text-center">
                        <h3 class="text-xl font-semibold text-gray-900 mb-2">
                            {{ director.firstname }} {{ director.lastname }}
                        </h3>

                        <div class="space-y-1 text-sm text-gray-600">
                            <p v-if="director.dob">
                                <span class="font-medium">Né(e) :</span> {{ formatDate(director.dob) }}
                            </p>
                            <p v-if="director.dod">
                                <span class="font-medium">Décédé(e) :</span> {{ formatDate(director.dod) }}
                            </p>
                            <p v-else-if="director.dob" class="text-green-600 font-medium">
                                {{ calculateAge(director.dob) }} ans
                            </p>
                        </div>

                        <div v-if="director.movies && director.movies.length > 0" class="mt-4 pt-4 border-t border-gray-200">
                            <p class="text-sm text-gray-700">
                                <span class="font-semibold">{{ director.movies.length }}</span>
                                {{ director.movies.length > 1 ? 'films' : 'film' }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div v-if="!loading && directors.length === 0" class="text-center py-12">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">Aucun réalisateur</h3>
            <p class="mt-1 text-sm text-gray-500">Aucun réalisateur n'a été trouvé dans la base de données.</p>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'

const router = useRouter()
const directors = ref([])
const loading = ref(true)
const error = ref(null)

const fetchDirectors = async () => {
    try {
        loading.value = true
        error.value = null
        const response = await axios.get('http://localhost:8000/api/directors')
        directors.value = response.data['hydra:member'] || response.data.member || []
    } catch (err) {
        error.value = 'Erreur lors du chargement des réalisateurs'
        console.error(err)
    } finally {
        loading.value = false
    }
}

const formatDate = (dateString) => {
    const date = new Date(dateString)
    return date.toLocaleDateString('fr-FR', { year: 'numeric', month: 'long', day: 'numeric' })
}

const calculateAge = (dobString) => {
    const dob = new Date(dobString)
    const today = new Date()
    let age = today.getFullYear() - dob.getFullYear()
    const monthDiff = today.getMonth() - dob.getMonth()
    if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < dob.getDate())) {
        age--
    }
    return age
}

const goToDirector = (id) => {
    // Pour l'instant, on pourrait rediriger vers une page de détail du réalisateur
    // router.push(`/directors/${id}`)
    console.log('Director ID:', id)
}

onMounted(() => {
    fetchDirectors()
})
</script>
