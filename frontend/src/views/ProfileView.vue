<template>
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white shadow-lg rounded-lg overflow-hidden">
            <div class="bg-gradient-to-r from-gray-800 to-gray-900 h-32"></div>

            <div class="px-6 py-8">
                <div class="flex items-center justify-center -mt-20 mb-6">
                    <div class="bg-white rounded-full p-2 shadow-lg">
                        <div class="bg-gray-900 rounded-full w-24 h-24 flex items-center justify-center">
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

                    <!-- Section 2FA -->
                    <div class="border-b border-gray-200 pb-4">
                        <div class="flex items-center justify-between mb-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Authentification à deux facteurs (2FA)</label>
                                <p class="text-sm text-gray-500 mt-1">Ajoutez une couche de sécurité supplémentaire à votre compte</p>
                            </div>
                            <div>
                                <span v-if="authStore.user?.twoFactorEnabled" class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    Activé
                                </span>
                                <span v-else class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                    Désactivé
                                </span>
                            </div>
                        </div>

                        <!-- Activation du 2FA -->
                        <div v-if="!authStore.user?.twoFactorEnabled && !twoFASetup.qrCode">
                            <button
                                @click="setupTwoFA"
                                :disabled="twoFASetup.loading"
                                class="w-full px-4 py-2 text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                {{ twoFASetup.loading ? 'Chargement...' : 'Activer le 2FA' }}
                            </button>
                        </div>

                        <!-- Configuration du 2FA (affichage QR code) -->
                        <div v-if="twoFASetup.qrCode" class="mt-4 bg-gray-50 rounded-lg p-4">
                            <h3 class="text-lg font-medium text-gray-900 mb-3">Configuration du 2FA</h3>

                            <div class="space-y-4">
                                <div class="text-center">
                                    <p class="text-sm text-gray-600 mb-3">Scannez ce QR code avec Google Authenticator, Authy ou une autre application 2FA</p>
                                    <img :src="twoFASetup.qrCode" alt="QR Code" class="mx-auto border border-gray-300 rounded-lg" />
                                </div>

                                <div class="bg-white rounded-lg p-3 border border-gray-200">
                                    <p class="text-xs font-medium text-gray-700 mb-1">Clé secrète (si vous ne pouvez pas scanner) :</p>
                                    <p class="text-xs font-mono text-gray-900 break-all">{{ twoFASetup.secret }}</p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Entrez le code à 6 chiffres généré</label>
                                    <input
                                        v-model="twoFASetup.verificationCode"
                                        type="text"
                                        maxlength="6"
                                        placeholder="123456"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    />
                                </div>

                                <div v-if="twoFASetup.error" class="bg-red-50 border border-red-200 rounded-md p-3">
                                    <p class="text-sm text-red-700">{{ twoFASetup.error }}</p>
                                </div>

                                <div class="flex gap-2">
                                    <button
                                        @click="enableTwoFA"
                                        :disabled="twoFASetup.loading || twoFASetup.verificationCode.length !== 6"
                                        class="flex-1 px-4 py-2 text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 disabled:opacity-50 disabled:cursor-not-allowed"
                                    >
                                        {{ twoFASetup.loading ? 'Vérification...' : 'Activer' }}
                                    </button>
                                    <button
                                        @click="cancelTwoFASetup"
                                        :disabled="twoFASetup.loading"
                                        class="px-4 py-2 text-sm font-medium rounded-md text-gray-700 bg-gray-200 hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500 disabled:opacity-50 disabled:cursor-not-allowed"
                                    >
                                        Annuler
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Codes de secours -->
                        <div v-if="twoFASetup.backupCodes.length > 0" class="mt-4 bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                            <h3 class="text-lg font-medium text-yellow-900 mb-2">Codes de secours</h3>
                            <p class="text-sm text-yellow-700 mb-3">⚠️ Sauvegardez ces codes dans un endroit sûr. Ils ne seront plus affichés.</p>
                            <div class="grid grid-cols-2 gap-2 bg-white rounded-lg p-3">
                                <div v-for="(code, index) in twoFASetup.backupCodes" :key="index" class="text-sm font-mono text-gray-900">
                                    {{ index + 1 }}. {{ code }}
                                </div>
                            </div>
                            <button
                                @click="twoFASetup.backupCodes = []"
                                class="mt-3 w-full px-4 py-2 text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700"
                            >
                                J'ai sauvegardé mes codes
                            </button>
                        </div>

                        <!-- Désactivation du 2FA -->
                        <div v-if="authStore.user?.twoFactorEnabled && !twoFADisable.show && twoFASetup.backupCodes.length === 0">
                            <button
                                @click="twoFADisable.show = true"
                                class="w-full px-4 py-2 text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500"
                            >
                                Désactiver le 2FA
                            </button>
                        </div>

                        <!-- Formulaire de désactivation -->
                        <div v-if="twoFADisable.show" class="mt-4 bg-gray-50 rounded-lg p-4">
                            <h3 class="text-lg font-medium text-gray-900 mb-3">Désactiver le 2FA</h3>

                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Entrez votre code 2FA pour confirmer</label>
                                    <input
                                        v-model="twoFADisable.code"
                                        type="text"
                                        maxlength="8"
                                        placeholder="Code 2FA ou code de secours"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500"
                                    />
                                </div>

                                <div v-if="twoFADisable.error" class="bg-red-50 border border-red-200 rounded-md p-3">
                                    <p class="text-sm text-red-700">{{ twoFADisable.error }}</p>
                                </div>

                                <div class="flex gap-2">
                                    <button
                                        @click="disableTwoFA"
                                        :disabled="twoFADisable.loading || !twoFADisable.code"
                                        class="flex-1 px-4 py-2 text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 disabled:opacity-50 disabled:cursor-not-allowed"
                                    >
                                        {{ twoFADisable.loading ? 'Désactivation...' : 'Désactiver' }}
                                    </button>
                                    <button
                                        @click="twoFADisable.show = false; twoFADisable.code = ''; twoFADisable.error = ''"
                                        :disabled="twoFADisable.loading"
                                        class="px-4 py-2 text-sm font-medium rounded-md text-gray-700 bg-gray-200 hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500 disabled:opacity-50 disabled:cursor-not-allowed"
                                    >
                                        Annuler
                                    </button>
                                </div>
                            </div>
                        </div>
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
import { reactive } from 'vue'
import { useAuthStore } from '../stores/auth'
import { useRouter } from 'vue-router'
import api from '../services/api'

const authStore = useAuthStore()
const router = useRouter()

const twoFASetup = reactive({
    loading: false,
    qrCode: '',
    secret: '',
    verificationCode: '',
    backupCodes: [],
    error: ''
})

const twoFADisable = reactive({
    show: false,
    code: '',
    loading: false,
    error: ''
})

const setupTwoFA = async () => {
    twoFASetup.loading = true
    twoFASetup.error = ''

    try {
        const response = await api.post('/api/2fa/setup')
        twoFASetup.qrCode = response.data.qr_code
        twoFASetup.secret = response.data.secret
    } catch (error) {
        twoFASetup.error = error.response?.data?.error || 'Erreur lors de la configuration du 2FA'
    } finally {
        twoFASetup.loading = false
    }
}

const enableTwoFA = async () => {
    twoFASetup.loading = true
    twoFASetup.error = ''

    try {
        const response = await api.post('/api/2fa/enable', {
            code: twoFASetup.verificationCode
        })

        twoFASetup.backupCodes = response.data.backup_codes
        twoFASetup.qrCode = ''
        twoFASetup.secret = ''
        twoFASetup.verificationCode = ''

        // Rafraîchir les infos utilisateur
        await authStore.refreshUser()
    } catch (error) {
        twoFASetup.error = error.response?.data?.error || 'Code invalide'
    } finally {
        twoFASetup.loading = false
    }
}

const cancelTwoFASetup = () => {
    twoFASetup.qrCode = ''
    twoFASetup.secret = ''
    twoFASetup.verificationCode = ''
    twoFASetup.error = ''
}

const disableTwoFA = async () => {
    twoFADisable.loading = true
    twoFADisable.error = ''

    try {
        await api.post('/api/2fa/disable', {
            code: twoFADisable.code
        })

        twoFADisable.show = false
        twoFADisable.code = ''

        // Rafraîchir les infos utilisateur
        await authStore.refreshUser()
    } catch (error) {
        twoFADisable.error = error.response?.data?.error || 'Erreur lors de la désactivation'
    } finally {
        twoFADisable.loading = false
    }
}

const handleLogout = () => {
    authStore.logout()
    router.push('/')
}
</script>
