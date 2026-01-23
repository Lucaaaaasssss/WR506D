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

                <div v-if="!showTwoFAInput" class="space-y-4">
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

                <!-- 2FA Input -->
                <div v-else class="space-y-4">
                    <div class="bg-blue-50 border-l-4 border-blue-500 p-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-blue-700">
                                    Authentification à deux facteurs activée. Entrez le code à 6 chiffres de votre application d'authentification.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="relative">
                        <label for="twofa-code" class="block text-xs font-bold text-gray-900 uppercase tracking-widest mb-1 ml-1">
                            Code 2FA
                        </label>
                        <input
                            id="twofa-code"
                            v-model="form.twoFACode"
                            name="twoFACode"
                            type="text"
                            maxlength="8"
                            required
                            autocomplete="off"
                            class="appearance-none block w-full px-4 py-3 border border-gray-200 placeholder-gray-400 text-gray-900 focus:outline-none focus:ring-1 focus:ring-gray-900 focus:border-gray-900 transition-all sm:text-sm text-center text-2xl tracking-widest font-mono"
                            placeholder="000000"
                        />
                        <p class="text-xs text-gray-500 mt-1 ml-1">Vous pouvez également utiliser un code de secours</p>
                    </div>

                    <button
                        type="button"
                        @click="cancelTwoFA"
                        class="w-full text-center text-sm text-gray-600 hover:text-gray-900 transition-colors"
                    >
                        ← Retour
                    </button>
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
import api from '../../services/api'

const router = useRouter()
const authStore = useAuthStore()

const form = ref({
    email: '',
    password: '',
    twoFACode: ''
})

const error = ref(null)
const loading = ref(false)
const showTwoFAInput = ref(false)

const handleSubmit = async () => {
    error.value = null
    loading.value = true

    try {
        // Si on est sur l'écran 2FA, vérifier le code
        if (showTwoFAInput.value) {
            await verifyTwoFACode()
            return
        }

        // Sinon, faire le login normal
        const result = await authStore.login({
            email: form.value.email,
            password: form.value.password
        })

        if (result.success) {
            // Vérifier si le 2FA est activé
            if (authStore.user?.twoFactorEnabled) {
                showTwoFAInput.value = true
                loading.value = false
                // Garder le token temporairement mais déconnecter visuellement
                return
            }

            router.push('/')
        } else {
            error.value = result.error || "Identifiants invalides"
        }
    } catch (e) {
        error.value = "Une erreur technique est survenue"
    } finally {
        if (!showTwoFAInput.value) {
            loading.value = false
        }
    }
}

const verifyTwoFACode = async () => {
    try {
        const response = await api.post('/api/2fa/verify', {
            email: form.value.email,
            code: form.value.twoFACode
        })

        if (response.data.message === 'Code valide') {
            // Code valide, rediriger
            router.push('/')
        }
    } catch (e) {
        error.value = e.response?.data?.error || "Code invalide"
    } finally {
        loading.value = false
    }
}

const cancelTwoFA = () => {
    showTwoFAInput.value = false
    form.value.twoFACode = ''
    error.value = null
    authStore.logout()
}
</script>
