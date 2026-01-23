import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '../services/api'

export const useAuthStore = defineStore('auth', () => {
  const token = ref(localStorage.getItem('token') || null)
  const user = ref(JSON.parse(localStorage.getItem('user') || 'null'))

  const isAuthenticated = computed(() => !!token.value)
  const isAdmin = computed(() => user.value?.roles?.includes('ROLE_ADMIN') || false)

  async function login(credentials) {
    try {
      const response = await api.post('/auth', credentials)
      token.value = response.data.token
      localStorage.setItem('token', response.data.token)

      // Fetch user info
      const userResponse = await api.get('/api/me')
      user.value = userResponse.data
      localStorage.setItem('user', JSON.stringify(userResponse.data))

      return { success: true }
    } catch (error) {
      return {
        success: false,
        error: error.response?.data?.message || 'Login failed'
      }
    }
  }

  async function register(userData) {
    try {
      await api.post('/register', userData)
      return { success: true }
    } catch (error) {
      return {
        success: false,
        error: error.response?.data?.error || 'Registration failed'
      }
    }
  }

  function logout() {
    token.value = null
    user.value = null
    localStorage.removeItem('token')
    localStorage.removeItem('user')
  }

  async function refreshUser() {
    try {
      const userResponse = await api.get('/api/me')
      user.value = userResponse.data
      localStorage.setItem('user', JSON.stringify(userResponse.data))
    } catch (error) {
      console.error('Failed to refresh user', error)
    }
  }

  return {
    token,
    user,
    isAuthenticated,
    isAdmin,
    login,
    register,
    logout,
    refreshUser
  }
})
