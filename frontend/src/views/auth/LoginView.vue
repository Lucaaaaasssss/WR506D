<template>
    <div class=" flex items-center justify-center bg-white py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-10">
            <div class="text-center">
                <h2 class="text-4xl font-black text-gray-900 tracking-tight">
                    Connexion
                </h2>
                <p class="mt-3 text-gray-500 text-sm">
                    Bienvenue. Veuillez saisir vos identifiants.
                </p>
            </div>

            <form class="space-y-6" @submit.prevent="handleSubmit">
                <div v-if="error" class="bg-gray-50 border-l-4 border-gray-900 p-4 transition-all">
                    <p class="text-sm text-gray-900 font-medium">{{ error }}</p>
                </div>

                <div class="space-y-4">
                    <div class="relative">
                        <label for="email-address" class="block text-xs font-bold text-gray-900 uppercase tracking-widest mb-1 ml-1">
                            Adresse email
                        </label>
                        <input
                            id="email-address"
                            v-model="form.email"
                            name="email"
                            type="email"
                            required
                            class="appearance-none block w-full px-4 py-3 border border-gray-200 placeholder-gray-400 text-gray-900 focus:outline-none focus:ring-1 focus:ring-gray-900 focus:border-gray-900 transition-all sm:text-sm"
                            placeholder="votre@email.com"
                        />
                    </div>

                    <div class="relative">
                        <div class="flex justify-between items-center mb-1 ml-1">
                            <label for="password" class="block text-xs font-bold text-gray-900 uppercase tracking-widest">
                                Mot de passe
                            </label>
                        </div>
                        <input
                            id="password"
                            v-model="form.password"
                            name="password"
                            type="password"
                            required
                            class="appearance-none block w-full px-4 py-3 border border-gray-200 placeholder-gray-400 text-gray-900 focus:outline-none focus:ring-1 focus:ring-gray-900 focus:border-gray-900 transition-all sm:text-sm"
                            placeholder="••••••••"
                        />
                    </div>
                </div>

                <div>
                    <button
                        type="submit"
                        :disabled="loading"
                        class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-bold text-white bg-gray-900 hover:bg-black focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-900 transition-all active:scale-[0.98] disabled:opacity-50 disabled:cursor-not-allowed"
                    >
            <span v-if="loading" class="flex items-center">
              <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              Chargement...
            </span>
                        <span v-else uppercase tracking-widest>SE CONNECTER</span>
                    </button>
                </div>

                <div class="text-center pt-4">
                    <router-link
                        to="/register"
                        class="text-sm font-bold text-gray-500 hover:text-gray-900 transition-colors border-b border-transparent hover:border-gray-900 pb-1"
                    >
                        Créer un compte
                    </router-link>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../../stores/auth'

const router = useRouter()
const authStore = useAuthStore()

const form = ref({
    email: '',
    password: ''
})

const error = ref(null)
const loading = ref(false)

const handleSubmit = async () => {
    error.value = null
    loading.value = true

    try {
        const result = await authStore.login({
            email: form.value.email,
            password: form.value.password
        })

        if (result.success) {
            router.push('/')
        } else {
            error.value = result.error || "Identifiants invalides"
        }
    } catch (e) {
        error.value = "Une erreur technique est survenue"
    } finally {
        loading.value = false
    }
}
</script>
