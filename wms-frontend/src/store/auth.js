import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { authApi } from '@/api/auth'

export const useAuthStore = defineStore('auth', () => {
  const user  = ref(JSON.parse(localStorage.getItem('auth_user') || 'null'))
  const token = ref(localStorage.getItem('auth_token') || null)

  const isAuthenticated = computed(() => !!token.value)

  async function register(data) {
    const response = await authApi.register(data)
    setAuth(response.data)
    return response
  }

  async function login(data) {
    const response = await authApi.login(data)
    setAuth(response.data)
    return response
  }

  async function logout() {
    try {
      await authApi.logout()
    } finally {
      clearAuth()
    }
  }

  function setAuth(data) {
    user.value  = data.user
    token.value = data.token
    localStorage.setItem('auth_user',  JSON.stringify(data.user))
    localStorage.setItem('auth_token', data.token)
  }

  function clearAuth() {
    user.value  = null
    token.value = null
    localStorage.removeItem('auth_user')
    localStorage.removeItem('auth_token')
  }

  return { user, token, isAuthenticated, register, login, logout }
})