<template>
    <div id="app" class="min-h-screen bg-white font-sans flex flex-col">
        <nav class="shadow-md bg-white border-b border-gray-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex h-16 items-center justify-between relative">
                    <div class="flex items-center">
                        <router-link to="/" class="text-3xl font-logo text-gray-900">
                            Bomboclaat
                        </router-link>
                    </div>

                    <div class="hidden sm:flex justify-center absolute left-1/2 transform -translate-x-1/2">
                        <div class="flex space-x-6 lg:space-x-8">
                            <router-link
                                to="/"
                                class="inline-flex items-center px-1 pt-1 text-sm font-bold text-gray-700 border-b-2 border-transparent hover:border-gray-900 hover:text-gray-900 transition duration-200"
                            >
                                Accueil
                            </router-link>
                            <router-link
                                to="/movies"
                                class="inline-flex items-center px-1 pt-1 text-sm font-bold text-gray-700 border-b-2 border-transparent hover:border-gray-900 hover:text-gray-900 transition duration-200"
                            >
                                Films
                            </router-link>
                            <router-link
                                to="/directors"
                                class="inline-flex items-center px-1 pt-1 text-sm font-bold text-gray-700 border-b-2 border-transparent hover:border-gray-900 hover:text-gray-900 transition duration-200"
                            >
                                Réalisateurs
                            </router-link>
                            <router-link
                                v-if="authStore.isAdmin"
                                to="/movies/create"
                                class="inline-flex items-center px-1 pt-1 text-sm font-bold text-gray-700 border-b-2 border-transparent hover:border-gray-900 hover:text-gray-900 transition duration-200"
                            >
                                Nouveau Film
                            </router-link>
                            <router-link
                                v-if="authStore.isAdmin"
                                to="/admin"
                                class="inline-flex items-center px-1 pt-1 text-sm font-bold text-gray-700 border-b-2 border-transparent hover:border-gray-900 hover:text-gray-900 transition duration-200"
                            >
                                Administration
                            </router-link>
                        </div>
                    </div>

                    <div class="flex items-center space-x-3">
                        <router-link
                            :to="authStore.isAuthenticated ? '/profile' : '/login'"
                            class="p-2 rounded-full transition duration-150 text-gray-700 hover:bg-gray-100"
                            :title="authStore.isAuthenticated ? 'Mon profil' : 'Se connecter'"
                        >
                            <svg class="w-7 h-7" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                            </svg>
                        </router-link>

                        <button
                            v-if="authStore.isAuthenticated"
                            @click="handleLogout"
                            class="hidden md:inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-gray-900 hover:bg-gray-800 transition-colors duration-200"
                        >
                            Déconnexion
                        </button>

                        <button
                            @click="isMobileMenuOpen = !isMobileMenuOpen"
                            class="sm:hidden p-2 rounded-full text-gray-700 hover:bg-gray-100 transition duration-150 focus:outline-none"
                            aria-label="Toggle menu"
                        >
                            <svg v-if="isMobileMenuOpen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                            <svg v-else class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <transition name="slide">
                <div v-if="isMobileMenuOpen" class="sm:hidden border-t border-gray-200 py-2 bg-white shadow-lg">
                    <router-link
                        to="/"
                        @click="isMobileMenuOpen = false"
                        class="block px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 hover:text-gray-900 transition duration-150"
                    >
                        Accueil
                    </router-link>
                    <router-link
                        to="/movies"
                        @click="isMobileMenuOpen = false"
                        class="block px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 hover:text-gray-900 transition duration-150"
                    >
                        Films
                    </router-link>
                    <router-link
                        to="/directors"
                        @click="isMobileMenuOpen = false"
                        class="block px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 hover:text-gray-900 transition duration-150"
                    >
                        Réalisateurs
                    </router-link>

                    <template v-if="authStore.isAdmin">
                        <hr class="my-1 border-gray-100">
                        <router-link
                            to="/movies/create"
                            @click="isMobileMenuOpen = false"
                            class="block px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 hover:text-gray-900 transition duration-150"
                        >
                            Nouveau Film
                        </router-link>
                        <router-link
                            to="/admin"
                            @click="isMobileMenuOpen = false"
                            class="block px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 hover:text-gray-900 transition duration-150"
                        >
                            Administration
                        </router-link>
                    </template>

                    <hr v-if="authStore.isAuthenticated" class="my-1 border-gray-100">
                    <button
                        v-if="authStore.isAuthenticated"
                        @click="handleLogoutAndClose"
                        class="w-full text-left px-4 py-2 text-sm font-medium text-red-600 hover:bg-red-50 transition duration-150"
                    >
                        Déconnexion
                    </button>

                </div>
            </transition>
        </nav>

        <main class="px-6 sm:px-8 lg:px-12 py-8 flex-grow">
            <router-view />
        </main>

        <footer class="bg-white border-t border-gray-200 mt-auto">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
                <div class="text-center">
                    <div class="mb-6">
                        <h3 class="text-3xl font-logo tracking-tight text-gray-900 mb-2">
                            Bomboclaat
                        </h3>
                        <p class="font-bold text-gray-400">
                            © 2025 Lucas Lebecq. Tous droits réservés.
                        </p>
                    </div>
                </div>
            </div>
        </footer>
    </div>
</template>

<script setup>
import { ref } from 'vue'
import { useAuthStore } from './stores/auth'
import { useRouter } from 'vue-router'

const authStore = useAuthStore()
const router = useRouter()

// État pour le menu mobile
const isMobileMenuOpen = ref(false)

const handleLogout = () => {
    authStore.logout()
    router.push('/')
}

// Fonction pour déconnecter et fermer le menu mobile
const handleLogoutAndClose = () => {
    handleLogout()
    isMobileMenuOpen.value = false
}
</script>

<style>
/* CSS d'animation pour une transition plus fluide du menu mobile */
.slide-enter-active, .slide-leave-active {
    transition: all 0.3s ease-out;
    max-height: 300px; /* Doit être suffisant pour contenir le menu */
    overflow: hidden;
}
.slide-enter-from, .slide-leave-to {
    opacity: 0;
    max-height: 0;
}
</style>
