import { defineStore } from 'pinia'
import api from '@/api/axios'

export const useProductStore = defineStore('products', {
  state: () => ({
    products: [],
    pagination: null,
    loading: false,
    error: null,
  }),

  actions: {
    async fetchProducts(params = {}) {
      this.loading = true
      this.error = null
      try {
        const { data } = await api.get('/products', { params })
        // API پاسخ paginate برمی‌گرداند
        this.products   = data.data ?? data
        this.pagination = data
        return { success: true }
      } catch (e) {
        this.error = e.response?.data?.message || 'خطا در دریافت کالاها'
        return { success: false }
      } finally {
        this.loading = false
      }
    },

    async createProduct(payload) {
      this.loading = true
      try {
        const { data } = await api.post('/products', payload)
        return { success: true, product: data.product }
      } catch (e) {
        return { success: false, errors: e.response?.data?.errors }
      } finally {
        this.loading = false
      }
    },

    async updateProduct(id, payload) {
      this.loading = true
      try {
        const { data } = await api.put(`/products/${id}`, payload)
        const idx = this.products.findIndex(p => p.id === id)
        if (idx !== -1) this.products[idx] = data.product
        return { success: true, product: data.product }
      } catch (e) {
        return { success: false, errors: e.response?.data?.errors }
      } finally {
        this.loading = false
      }
    },

    async deleteProduct(id) {
      try {
        await api.delete(`/products/${id}`)
        this.products = this.products.filter(p => p.id !== id)
        return { success: true }
      } catch (e) {
        return { success: false }
      }
    },
  },
})
