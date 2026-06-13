// ================================================================
// STORE: Inventory Transactions (Pinia)
// File: src/store/transactions.js
// ================================================================

import { defineStore } from 'pinia'
import api from '@/services/api'

export const useTransactionStore = defineStore('transactions', {
  state: () => ({
    transactions: [],
    pagination: null,
    summary: null,
    loading: false,
    error: null,
    dashboardStats: null,
  }),

  actions: {
    // ─── لیست رویدادها ───────────────────────────────────────────
    async fetchTransactions(params = {}) {
      this.loading = true
      this.error = null
      try {
        const { data } = await api.get('/inventory-transactions', { params })
        this.transactions = data.transactions.data
        this.pagination   = data.transactions
        this.summary      = data.summary
        return { success: true }
      } catch (e) {
        this.error = e.response?.data?.message || 'خطا در دریافت رویدادها'
        return { success: false }
      } finally {
        this.loading = false
      }
    },

    // ─── ثبت رویداد ──────────────────────────────────────────────
    async createTransaction(payload) {
      this.loading = true
      try {
        const { data } = await api.post('/inventory-transactions', payload)
        return { success: true, transaction: data.transaction, newBalance: data.new_balance }
      } catch (e) {
        return { success: false, errors: e.response?.data?.errors, message: e.response?.data?.message }
      } finally {
        this.loading = false
      }
    },

    // ─── حذف رویداد ──────────────────────────────────────────────
    async deleteTransaction(id) {
      try {
        await api.delete(`/inventory-transactions/${id}`)
        this.transactions = this.transactions.filter(t => t.id !== id)
        return { success: true }
      } catch (e) {
        return { success: false, message: e.response?.data?.message }
      }
    },

    // ─── کاردکس کالا ─────────────────────────────────────────────
    async fetchKardex(productId, params = {}) {
      this.loading = true
      try {
        const { data } = await api.get(`/products/${productId}/kardex`, { params })
        return { success: true, data }
      } catch (e) {
        return { success: false }
      } finally {
        this.loading = false
      }
    },

    // ─── آمار داشبورد ────────────────────────────────────────────
    async fetchDashboardStats() {
      try {
        const { data } = await api.get('/dashboard/stats')
        this.dashboardStats = data
        return { success: true, data }
      } catch (e) {
        return { success: false }
      }
    },
  },
})


// ================================================================
// CONSTANTS: transaction types
// File: src/constants/transactionTypes.js
// ================================================================
export const TRANSACTION_TYPES = [
  {
    value: 'receipt',
    label: 'ورود به انبار',
    icon: '📥',
    color: 'green',
    affect: '+',
    description: 'دریافت کالا از تأمین‌کننده یا خرید',
    needsParty: true,
    partyLabel: 'نام تأمین‌کننده',
  },
  {
    value: 'issue',
    label: 'خروج از انبار',
    icon: '📤',
    color: 'red',
    affect: '−',
    description: 'تحویل کالا به مشتری یا مصرف داخلی',
    needsParty: true,
    partyLabel: 'نام تحویل‌گیرنده / مشتری',
  },
  {
    value: 'return_in',
    label: 'مرجوعی از مشتری',
    icon: '↩️',
    color: 'blue',
    affect: '+',
    description: 'برگشت کالا از مشتری به انبار',
    needsParty: true,
    partyLabel: 'نام مشتری',
  },
  {
    value: 'return_out',
    label: 'مرجوعی به تأمین‌کننده',
    icon: '↪️',
    color: 'orange',
    affect: '−',
    description: 'ارسال کالا به تأمین‌کننده (برگشت خرید)',
    needsParty: true,
    partyLabel: 'نام تأمین‌کننده',
  },
  {
    value: 'transfer',
    label: 'انتقال داخلی',
    icon: '🔄',
    color: 'purple',
    affect: '±',
    description: 'جابجایی کالا بین قسمت‌ها یا مکان‌ها',
    needsParty: true,
    partyLabel: 'مبدأ / مقصد',
  },
  {
    value: 'adjustment',
    label: 'تعدیل موجودی',
    icon: '⚖️',
    color: 'yellow',
    affect: '±',
    description: 'اصلاح موجودی پس از انبارگردانی',
    needsParty: false,
    partyLabel: '',
  },
  {
    value: 'scrap',
    label: 'ضایعات',
    icon: '🗑️',
    color: 'red',
    affect: '−',
    description: 'خروج کالای فاسد، خراب یا منسوخ',
    needsParty: false,
    partyLabel: '',
  },
  {
    value: 'initial',
    label: 'موجودی اولیه',
    icon: '🔰',
    color: 'teal',
    affect: '+',
    description: 'ثبت موجودی اولیه هنگام راه‌اندازی سیستم',
    needsParty: false,
    partyLabel: '',
  },
]

export const getTypeInfo = (value) =>
  TRANSACTION_TYPES.find(t => t.value === value) || {}
