import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '../services/api'

export const useAuthStore = defineStore('auth', () => {
  const user = ref(null)
  const token = ref(localStorage.getItem('token') || null)

  const isAuthenticated = computed(() => !!token.value)
  const userRole = computed(() => user.value?.role)

  const login = async (email, password) => {
    try {
      const response = await api.post('/auth/login', { email, password })
      token.value = response.data.token
      user.value = response.data.user
      localStorage.setItem('token', token.value)
      return { success: true }
    } catch (error) {
      return { success: false, error: error.response?.data?.error || 'Login failed' }
    }
  }

  const register = async (email, password, name, role) => {
    try {
      const response = await api.post('/auth/register', { email, password, name, role })
      token.value = response.data.token
      user.value = response.data.user
      localStorage.setItem('token', token.value)
      return { success: true }
    } catch (error) {
      return { success: false, error: error.response?.data?.error || 'Registration failed' }
    }
  }

  const logout = () => {
    token.value = null
    user.value = null
    localStorage.removeItem('token')
  }

  const fetchUser = async () => {
    if (!token.value) return
    try {
      const response = await api.get('/auth/me')
      user.value = response.data.user
    } catch (error) {
      logout()
    }
  }

  return {
    user,
    token,
    isAuthenticated,
    userRole,
    login,
    register,
    logout,
    fetchUser
  }
})

