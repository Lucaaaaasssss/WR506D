<template>
    <div class=" flex items-center justify-center bg-white py-4 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-10">
            <div class="text-center">
                <h2 class="text-4xl font-black text-gray-900 tracking-tight">
                    Créer un compte
                </h2>
                <p class="mt-3 text-gray-500 text-sm">
                    Rejoignez-nous en remplissant les informations ci-dessous.
                </p>
            </div>

            <form class="space-y-6" @submit.prevent="handleSubmit">
                <transition name="fade">
                    <div v-if="error" class="bg-gray-50 border-l-4 border-gray-900 p-4">
                        <p class="text-sm text-gray-900 font-medium">{{ error }}</p>
                    </div>
                </transition>

                <transition name="fade">
                    <div v-if="success" class="bg-gray-900 border-l-4 border-white p-4">
                        <p class="text-sm text-white font-medium">
                            Inscription réussie ! Redirection en cours...
                        </p>
                    </div>
                </transition>

                <div class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="firstname" class="block text-xs font-bold text-gray-900 uppercase tracking-widest mb-1 ml-1">
                                Prénom
                            </label>
                            <input
                                id="firstname"
                                v-model="form.firstname"
                                name="firstname"
                                type="text"
                                class="appearance-none block w-full px-4 py-3 border border-gray-200 placeholder-gray-400 text-gray-900 focus:outline-none focus:ring-1 focus:ring-gray-900 focus:border-gray-900 transition-all sm:text-sm"
                                placeholder="Jean"
                            />
                        </div>
                        <div>
                            <label for="lastname" class="block text-xs font-bold text-gray-900 uppercase tracking-widest mb-1 ml-1">
                                Nom
                            </label>
                            <input
                                id="lastname"
                                v-model="form.lastname"
                                name="lastname"
                                type="text"
                                class="appearance-none block w-full px-4 py-3 border border-gray-200 placeholder-gray-400 text-gray-900 focus:outline-none focus:ring-1 focus:ring-gray-900 focus:border-gray-900 transition-all sm:text-sm"
                                placeholder="Dupont"
                            />
                        </div>
                    </div>

                    <div>
                        <label for="email-address" class="block text-xs font-bold text-gray-900 uppercase tracking-widest mb-1 ml-1">
                            Adresse email
                        </label>
                        <input
                            id="email-address"
                            v-model="form.email"
                            name="email"
                            type="email"
                            autocomplete="email"
                            required
                            class="appearance-none block w-full px-4 py-3 border border-gray-200 placeholder-gray-400 text-gray-900 focus:outline-none focus:ring-1 focus:ring-gray-900 focus:border-gray-900 transition-all sm:text-sm"
                            placeholder="votre@email.com"
                        />
                    </div>

                    <div>
                        <label for="password" class="block text-xs font-bold text-gray-900 uppercase tracking-widest mb-1 ml-1">
                            Mot de passe
                        </label>
                        <input
                            id="password"
                            v-model="form.password"
                            name="password"
                            type="password"
                            autocomplete="new-password"
                            required
                            class="appearance-none block w-full px-4 py-3 border border-gray-200 placeholder-gray-400 text-gray-900 focus:outline-none focus:ring-1 focus:ring-gray-900 focus:border-gray-900 transition-all sm:text-sm"
                            placeholder="••••••••"
                        />
                    </div>
                </div>

                <div class="pt-2">
                    <button
                        type="submit"
                        :disabled="loading"
                        class="group relative w-full flex justify-center py-3.5 px-4 border border-transparent text-sm font-bold text-white bg-gray-900 hover:bg-black focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-900 transition-all active:scale-[0.98] disabled:opacity-50 disabled:cursor-not-allowed"
                    >
            <span v-if="loading" class="flex items-center">
              <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              Création en cours...
            </span>
                        <span v-else class="uppercase tracking-widest">S'inscrire</span>
                    </button>
                </div>

                <div class="text-center pt-4">
                    <router-link
                        to="/login"
                        class="text-sm font-bold text-gray-500 hover:text-gray-900 transition-colors border-b border-transparent hover:border-gray-900 pb-1"
                    >
                        Déjà un compte ? Connectez-vous
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
    password: '',
    firstname: '',
    lastname: ''
})

const error = ref(null)
const success = ref(false)
const loading = ref(false)

const handleSubmit = async () => {
    error.value = null
    success.value = false
    loading.value = true

    try {
        const result = await authStore.register(form.value)
        if (result.success) {
            success.value = true
            form.value = { email: '', password: '', firstname: '', lastname: '' }
            setTimeout(() => {
                router.push('/login')
            }, 1500)
        } else {
            error.value = result.error || "Une erreur est survenue lors de l'inscription"
        }
    } catch (e) {
        error.value = "Erreur de connexion au serveur"
    } finally {
        loading.value = false
    }
}
</script>

<style scoped>
.fade-enter-active, .fade-leave-active {
    transition: opacity 0.3s ease;
}
.fade-enter-from, .fade-leave-to {
    opacity: 0;
}
</style>
