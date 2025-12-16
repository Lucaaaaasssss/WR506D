<template>
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white shadow-lg rounded-lg overflow-hidden">
            <div class="bg-gradient-to-r from-blue-500 to-blue-600 h-32"></div>

            <div class="px-6 py-8">
                <div class="flex items-center justify-center -mt-20 mb-6">
                    <div class="bg-white rounded-full p-2 shadow-lg">
                        <div class="bg-blue-500 rounded-full w-24 h-24 flex items-center justify-center">
                            <svg class="w-16 h-16 text-white" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="text-center mb-8">
                    <h1 class="text-3xl font-bold text-gray-900 mb-2">Mon Profil</h1>
                    <p class="text-gray-600">Informations de votre compte</p>
                </div>

                <div class="space-y-6">
                    <div class="border-b border-gray-200 pb-4" v-if="authStore.user?.firstname || authStore.user?.lastname">
                        <label class="block text-sm font-medium text-gray-500 mb-1">Nom complet</label>
                        <p class="text-lg text-gray-900">
                            {{ authStore.user?.firstname }} {{ authStore.user?.lastname }}
                        </p>
                    </div>

                    <div class="border-b border-gray-200 pb-4">
                        <label class="block text-sm font-medium text-gray-500 mb-1">Email</label>
                        <p class="text-lg text-gray-900">{{ authStore.user?.email }}</p>
                    </div>

                    <div class="border-b border-gray-200 pb-4">
                        <label class="block text-sm font-medium text-gray-500 mb-1">Rôle</label>
                        <p class="text-lg text-gray-900">
                            <span v-if="authStore.isAdmin" class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-purple-100 text-purple-800">
                                Administrateur
                            </span>
                            <span v-else class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                Utilisateur
                            </span>
                        </p>
                    </div>

                    <div class="border-b border-gray-200 pb-4">
                        <label class="block text-sm font-medium text-gray-500 mb-1">Statut</label>
                        <p class="text-lg">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                <span class="w-2 h-2 bg-green-500 rounded-full mr-2"></span>
                                Connecté
                            </span>
                        </p>
                    </div>
                </div>

                <div class="mt-8 flex justify-center">
                    <button
                        @click="handleLogout"
                        class="px-6 py-3 text-sm font-medium rounded-md text-white bg-red-600 transition duration-150 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2"
                    >
                        Se déconnecter
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { useAuthStore } from '../stores/auth'
import { useRouter } from 'vue-router'

const authStore = useAuthStore()
const router = useRouter()

const handleLogout = () => {
    authStore.logout()
    router.push('/')
}
</script>
