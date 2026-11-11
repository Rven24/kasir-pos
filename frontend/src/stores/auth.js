import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '@/services/api'
import { useToastStore } from './toast'

export const useAuthStore = defineStore('auth', () => {
  const token = ref(localStorage.getItem('pos_token') || null)
  const user = ref(JSON.parse(localStorage.getItem('pos_user') || 'null'))
  const loading = ref(false)

  const isAuthenticated = computed(() => !!token.value && !!user.value)
  const role = computed(() => user.value?.role || 'kasir')
  const isOwner = computed(() => role.value === 'owner')
  const isAdmin = computed(() => role.value === 'admin' || role.value === 'owner')
  const isKasir = computed(() => role.value === 'kasir')

  // Permission helpers
  const canManageProducts = computed(() => role.value === 'owner' || role.value === 'admin')
  const canManageUsers = computed(() => role.value === 'owner')
  const canViewReports = computed(() => role.value === 'owner' || role.value === 'admin')

  async function login(email, password) {
    loading.value = true
    const toast = useToastStore()
    try {
      const response = await api.post('/auth/login', { email, password })
      if (response.data.success) {
        token.value = response.data.data.token
        user.value = response.data.data.user
        localStorage.setItem('pos_token', token.value)
        localStorage.setItem('pos_user', JSON.stringify(user.value))
        toast.success(`Selamat datang kembali, ${user.value.name}!`)
        return { success: true }
      }
    } catch (err) {
      const message = err.response?.data?.message || 'Login gagal, periksa email & password.'
      toast.error(message)
      return { success: false, message }
    } finally {
      loading.value = false
    }
  }

  async function logout() {
    try {
      if (token.value) {
        await api.post('/auth/logout')
      }
    } catch (err) {
      console.warn('Logout error:', err)
    } finally {
      token.value = null
      user.value = null
      localStorage.removeItem('pos_token')
      localStorage.removeItem('pos_user')
      const toast = useToastStore()
      toast.info('Anda telah keluar dari sistem kasir.')
    }
  }

  async function fetchMe() {
    if (!token.value) return null
    try {
      const response = await api.get('/auth/me')
      if (response.data.success) {
        user.value = response.data.data
        localStorage.setItem('pos_user', JSON.stringify(user.value))
      }
    } catch (err) {
      console.warn('Failed to fetch user:', err)
    }
  }

  return {
    token,
    user,
    loading,
    isAuthenticated,
    role,
    isOwner,
    isAdmin,
    isKasir,
    canManageProducts,
    canManageUsers,
    canViewReports,
    login,
    logout,
    fetchMe,
  }
})
