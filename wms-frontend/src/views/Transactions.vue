<!-- ================================================================ -->
<!-- VIEW: Transactions.vue                                           -->
<!-- File: src/views/Transactions.vue                                 -->
<!-- لیست کامل رویدادهای انبار با فیلتر و جستجو                     -->
<!-- ================================================================ -->
<template>
  <div class="pt-24 pb-20 px-6">
    <div class="max-w-7xl mx-auto">

      <!-- Header -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
        <div>
          <h1 class="text-4xl font-black gradient-text">رویدادهای انبار</h1>
          <p class="text-gray-400 mt-1">ثبت و مشاهده تمام تراکنش‌های ورود، خروج و سایر رویدادها</p>
        </div>
        <button @click="showModal = true" class="btn-primary flex items-center gap-2">
          <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
          </svg>
          ثبت رویداد جدید
        </button>
      </div>

      <!-- Summary Cards -->
      <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
        <div class="card border-green-500/20 hover:border-green-500/40 transition-all">
          <div class="text-2xl font-black text-green-400 mb-1">
            {{ store.summary?.total_in || 0 }}
          </div>
          <div class="text-xs text-gray-400">مجموع ورودی (عدد)</div>
        </div>
        <div class="card border-red-500/20 hover:border-red-500/40 transition-all">
          <div class="text-2xl font-black text-red-400 mb-1">
            {{ store.summary?.total_out || 0 }}
          </div>
          <div class="text-xs text-gray-400">مجموع خروجی (عدد)</div>
        </div>
        <div class="card border-primary/20 hover:border-primary/40 transition-all">
          <div class="text-2xl font-black text-primary mb-1">
            {{ store.summary?.total_count || 0 }}
          </div>
          <div class="text-xs text-gray-400">تعداد کل رویدادها</div>
        </div>
        <div class="card border-yellow-500/20 hover:border-yellow-500/40 transition-all">
          <div class="text-lg font-black text-yellow-400 mb-1">
            {{ formatPrice(store.summary?.total_value || 0) }}
          </div>
          <div class="text-xs text-gray-400">ارزش کل رویدادها</div>
        </div>
      </div>

      <!-- Filters Bar -->
      <div class="card mb-6">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
          <!-- جستجو -->
          <div class="relative">
            <svg class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-500"
                 fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/>
            </svg>
            <input v-model="filters.search" @input="debouncedLoad"
                   type="text" placeholder="جستجو..." class="input-dark pr-9 text-sm" />
          </div>
          <!-- فیلتر نوع رویداد -->
          <select v-model="filters.transaction_type" @change="loadData" class="input-dark text-sm">
            <option value="">همه رویدادها</option>
            <option v-for="t in typeOptions" :key="t.value" :value="t.value">
              {{ t.icon }} {{ t.label }}
            </option>
          </select>
          <!-- از تاریخ -->
          <input v-model="filters.from_date" @change="loadData"
                 type="date" class="input-dark text-sm" placeholder="از تاریخ" />
          <!-- تا تاریخ -->
          <input v-model="filters.to_date" @change="loadData"
                 type="date" class="input-dark text-sm" placeholder="تا تاریخ" />
        </div>
      </div>

      <!-- Table -->
      <div class="card overflow-hidden p-0">
        <div v-if="store.loading" class="text-center py-14">
          <svg class="w-9 h-9 animate-spin text-primary mx-auto" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
          </svg>
        </div>

        <div v-else-if="store.transactions.length === 0" class="text-center py-14">
          <div class="text-5xl mb-3">📋</div>
          <p class="text-gray-400">هنوز رویدادی ثبت نشده است.</p>
        </div>

        <div v-else class="overflow-x-auto">
          <table class="w-full">
            <thead>
              <tr class="bg-dark-700 border-b border-dark-500 text-right">
                <th class="px-5 py-3 text-gray-400 text-xs font-medium">تاریخ</th>
                <th class="px-5 py-3 text-gray-400 text-xs font-medium">نوع رویداد</th>
                <th class="px-5 py-3 text-gray-400 text-xs font-medium">کالا</th>
                <th class="px-5 py-3 text-gray-400 text-xs font-medium">مقدار</th>
                <th class="px-5 py-3 text-gray-400 text-xs font-medium">قیمت واحد</th>
                <th class="px-5 py-3 text-gray-400 text-xs font-medium">ارزش کل</th>
                <th class="px-5 py-3 text-gray-400 text-xs font-medium">مانده بعد</th>
                <th class="px-5 py-3 text-gray-400 text-xs font-medium">سند</th>
                <th class="px-5 py-3 text-gray-400 text-xs font-medium">عملیات</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="tx in store.transactions" :key="tx.id"
                  class="border-b border-dark-600 hover:bg-dark-700/40 transition-colors">
                <td class="px-5 py-3 text-gray-400 text-xs whitespace-nowrap">
                  {{ formatDate(tx.created_at) }}
                </td>
                <td class="px-5 py-3">
                  <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-medium"
                        :class="typeClass(tx.transaction_type)">
                    {{ typeIcon(tx.transaction_type) }} {{ typeLabel(tx.transaction_type) }}
                  </span>
                </td>
                <td class="px-5 py-3">
                  <div class="text-white text-sm font-medium">{{ tx.product?.name }}</div>
                  <div class="text-gray-500 text-xs font-mono">{{ tx.product?.code }}</div>
                </td>
                <td class="px-5 py-3">
                  <span :class="isIncrease(tx.transaction_type) ? 'text-green-400' : 'text-red-400'"
                        class="font-bold text-sm">
                    {{ isIncrease(tx.transaction_type) ? '+' : '−' }}{{ tx.quantity }}
                  </span>
                </td>
                <td class="px-5 py-3 text-gray-300 text-sm">{{ formatPrice(tx.unit_price) }}</td>
                <td class="px-5 py-3 text-white text-sm font-medium">{{ formatPrice(tx.total_value) }}</td>
                <td class="px-5 py-3 text-primary font-bold text-sm">{{ tx.balance_after }}</td>
                <td class="px-5 py-3">
                  <span v-if="tx.reference_number"
                        class="text-xs font-mono text-gray-400 bg-dark-600 px-2 py-1 rounded">
                    {{ tx.reference_number }}
                  </span>
                  <span v-else class="text-gray-600 text-xs">—</span>
                </td>
                <td class="px-5 py-3">
                  <button @click="confirmDelete(tx)"
                          class="p-1.5 bg-red-500/10 hover:bg-red-500/20 text-red-400 rounded-lg transition-all">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div v-if="store.pagination?.last_page > 1"
             class="px-5 py-4 border-t border-dark-600 flex items-center justify-between">
          <span class="text-gray-500 text-xs">
            نمایش {{ store.pagination.from }}–{{ store.pagination.to }} از {{ store.pagination.total }} رویداد
          </span>
          <div class="flex gap-1.5">
            <button v-for="p in pageRange" :key="p" @click="goPage(p)"
                    class="w-8 h-8 text-xs rounded-lg transition-all"
                    :class="p === store.pagination.current_page
                      ? 'bg-primary text-white' : 'bg-dark-700 text-gray-400 hover:text-white'">
              {{ p }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Modals -->
    <TransactionModal v-if="showModal"
                      :products="products"
                      @close="showModal = false"
                      @saved="onSaved" />

    <!-- Delete Confirm -->
    <div v-if="deletingTx"
         class="fixed inset-0 z-50 flex items-center justify-center px-6"
         style="background:rgba(0,0,0,0.75);backdrop-filter:blur(4px)">
      <div class="card max-w-sm w-full border-red-500/30 animate-fade-in text-center">
        <div class="text-4xl mb-3">⚠️</div>
        <h3 class="text-white font-bold mb-2">حذف رویداد</h3>
        <p class="text-gray-400 text-sm mb-6">
          این رویداد حذف و موجودی کالا به حالت قبل برمی‌گردد. آیا مطمئن هستید؟
        </p>
        <div class="flex gap-3">
          <button @click="deletingTx = null"
                  class="flex-1 border border-dark-500 text-gray-300 py-2.5 rounded-xl">انصراف</button>
          <button @click="doDelete"
                  class="flex-1 bg-red-500 hover:bg-red-600 text-white py-2.5 rounded-xl transition-all">
            بله، حذف کن
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { useProductStore } from '@/store/products'
import { useTransactionStore, TRANSACTION_TYPES } from '@/store/transactions'
import TransactionModal from '@/components/TransactionModal.vue'

const store        = useTransactionStore()
const productStore = useProductStore()
const products     = computed(() => productStore.products)

const showModal  = ref(false)
const deletingTx = ref(null)
const page       = ref(1)

const filters = reactive({
  search:           '',
  transaction_type: '',
  from_date:        '',
  to_date:          '',
})

const typeOptions = TRANSACTION_TYPES

let searchTimer = null
const debouncedLoad = () => {
  clearTimeout(searchTimer)
  searchTimer = setTimeout(() => { page.value = 1; loadData() }, 400)
}

const loadData = async () => {
  const params = { page: page.value, per_page: 15, ...filters }
  Object.keys(params).forEach(k => !params[k] && delete params[k])
  await store.fetchTransactions(params)
}

const pageRange = computed(() => {
  const t = store.pagination?.last_page || 1
  return Array.from({ length: Math.min(t, 7) }, (_, i) => i + 1)
})

const goPage = (p) => { page.value = p; loadData() }

const typeInfo = (value) => TRANSACTION_TYPES.find(t => t.value === value) || {}
const typeLabel = (v) => typeInfo(v).label || v
const typeIcon  = (v) => typeInfo(v).icon || '📋'
const isIncrease = (v) => ['receipt', 'return_in', 'initial'].includes(v)

const typeClass = (v) => {
  const map = {
    receipt:    'bg-green-500/10 text-green-400',
    issue:      'bg-red-500/10 text-red-400',
    return_in:  'bg-blue-500/10 text-blue-400',
    return_out: 'bg-orange-500/10 text-orange-400',
    transfer:   'bg-purple-500/10 text-purple-400',
    adjustment: 'bg-yellow-500/10 text-yellow-400',
    scrap:      'bg-red-700/10 text-red-600',
    initial:    'bg-teal-500/10 text-teal-400',
  }
  return map[v] || 'bg-gray-500/10 text-gray-400'
}

const formatPrice = (n) => new Intl.NumberFormat('fa-IR').format(Math.round(n || 0)) + ' ت'
const formatDate = (d) => new Date(d).toLocaleString('fa-IR', { dateStyle: 'short', timeStyle: 'short' })

const confirmDelete = (tx) => { deletingTx.value = tx }
const doDelete = async () => {
  if (!deletingTx.value) return
  await store.deleteTransaction(deletingTx.value.id)
  deletingTx.value = null
  loadData()
  productStore.fetchProducts()
}

const onSaved = () => {
  showModal.value = false
  loadData()
  productStore.fetchProducts()
}

onMounted(async () => {
  await productStore.fetchProducts({ per_page: 200 })
  await loadData()
})
</script>