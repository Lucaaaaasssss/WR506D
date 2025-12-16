<template>
    <div class="min-h-screen bg-white">
        <div class="mx-auto px-4 sm:px-6 lg:px-8">

            <header class="pt-32 pb-24 text-center">
                <h1 class="text-6xl sm:text-7xl lg:text-8xl font-logo tracking-tight text-gray-900 mb-6">
                    Bomboclaat
                </h1>
                <p class="text-xl text-gray-500 mb-16 max-w-2xl mx-auto font-light">
                    Découvrez, partagez et débattez sur le cinéma
                </p>

                <div class="flex justify-center gap-4">
                    <router-link
                        to="/movies"
                        class="px-8 py-3 text-sm font-medium text-white bg-gray-900 hover:bg-gray-800 transition-colors duration-200"
                    >
                        Explorer les films
                    </router-link>

                    <router-link
                        v-if="!authStore.isAuthenticated"
                        to="/register"
                        class="px-8 py-3 text-sm font-medium text-gray-900 border border-gray-300 hover:border-gray-900 transition-colors duration-200"
                    >
                        S'inscrire
                    </router-link>
                </div>
            </header>

            <section class="py-24 border-t border-gray-200">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-16">
                    <div class="text-center">
                        <div class="mb-4">
                            <svg class="w-8 h-8 mx-auto text-gray-900" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 4v16M17 4v16M3 8h4m10 0h4M3 12h18M3 16h4m10 0h4M4 20h16a1 1 0 001-1V5a1 1 0 00-1-1H4a1 1 0 00-1 1v14a1 1 0 001 1z"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">Catalogue</h3>
                        <p class="text-sm text-gray-500 font-light leading-relaxed">
                            Films et réalisateurs
                        </p>
                    </div>

                    <div class="text-center">
                        <div class="mb-4">
                            <svg class="w-8 h-8 mx-auto text-gray-900" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">Communauté</h3>
                        <p class="text-sm text-gray-500 font-light leading-relaxed">
                            Critiques et discussions
                        </p>
                    </div>

                    <div class="text-center">
                        <div class="mb-4">
                            <svg class="w-8 h-8 mx-auto text-gray-900" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">Recherche</h3>
                        <p class="text-sm text-gray-500 font-light leading-relaxed">
                            Trouvez rapidement
                        </p>
                    </div>
                </div>
            </section>

        </div>
    </div>
</template>

<script setup>
import { useAuthStore } from '../stores/auth'

const authStore = useAuthStore()
</script>
