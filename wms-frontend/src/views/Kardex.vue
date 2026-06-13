
<!-- ================================================================ -->
<!-- VIEW: Kardex.vue                                                  -->
<!-- File: src/views/Kardex.vue                                       -->
<!-- کاردکس کامل یک کالا با مانده تجمعی                              -->
<!-- ================================================================ -->
<template>
  <div class="pt-24 pb-20 px-6">
    <div class="max-w-6xl mx-auto">

      <!-- Header -->
      <div class="flex items-center gap-4 mb-8">
        <RouterLink to="/transactions"
                    class="p-2 bg-dark-700 rounded-xl text-gray-400 hover:text-white transition-colors">
          <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
          </svg>
        </RouterLink>
        <div>
          <h1 class="text-4xl font-black gradient-text">کاردکس کالا</h1>
          <p v-if="kardex.product" class="text-gray-400 mt-1">
            {{ kardex.product.name }} ({{ kardex.product.code }})
          </p>
        </div>
      </div>

      <!-- Select Product -->
      <div class="card mb-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
          <div class="md:col-span-2">
            <label class="block text-gray-300 text-sm font-medium mb-2">انتخاب کالا</label>
            <select v-model="selectedProductId" @change="loadKardex" class="input-dark">
              <option value="">--- کالا را انتخاب کنید ---</option>
              <option v-for="p in products" :key="p.id" :value="p.id">
                {{ p.name }} ({{ p.code }}) — موجودی: {{ p.quantity }}
              </option>
            </select>
          </div>
          <div>
            <label class="block text-gray-300 text-sm font-medium mb-2">از تاریخ</label>
            <input v-model="fromDate" type="date" class="input-dark text-sm" @change="loadKardex" />
          </div>
          <div>
            <label class="block text-gray-300 text-sm font-medium mb-2">تا تاریخ</label>
            <input v-model="toDate" type="date" class="input-dark text-sm" @change="loadKardex" />
          </div>
        </div>
      </div>

      <!-- Loading -->
      <div v-if="loading" class="text-center py-16">
        <svg class="w-10 h-10 animate-spin text-primary mx-auto" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
        </svg>
      </div>

      <template v-else-if="kardex.rows">
        <!-- Summary Cards -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
          <div class="card text-center border-gray-500/20">
            <div class="text-xl font-bold text-gray-300 mb-1">{{ kardex.summary?.opening_balance }}</div>
            <div class="text-xs text-gray-500">مانده ابتدای دوره</div>
          </div>
          <div class="card text-center border-green-500/20">
            <div class="text-xl font-bold text-green-400 mb-1">+{{ kardex.summary?.total_in }}</div>
            <div class="text-xs text-gray-500">ورودی دوره</div>
          </div>
          <div class="card text-center border-red-500/20">
            <div class="text-xl font-bold text-red-400 mb-1">−{{ kardex.summary?.total_out }}</div>
            <div class="text-xs text-gray-500">خروجی دوره</div>
          </div>
          <div class="card text-center border-primary/20">
            <div class="text-xl font-bold text-primary mb-1">{{ kardex.summary?.closing_balance }}</div>
            <div class="text-xs text-gray-500">مانده پایان دوره</div>
          </div>
        </div>

        <!-- ارزش -->
        <div class="grid grid-cols-2 gap-4 mb-6">
          <div class="card border-green-500/10 flex justify-between items-center">
            <span class="text-gray-400 text-sm">ارزش ورودی دوره:</span>
            <span class="text-green-400 font-bold">{{ formatPrice(kardex.summary?.total_in_value) }}</span>
          </div>
          <div class="card border-red-500/10 flex justify-between items-center">
            <span class="text-gray-400 text-sm">ارزش خروجی دوره:</span>
            <span class="text-red-400 font-bold">{{ formatPrice(kardex.summary?.total_out_value) }}</span>
          </div>
        </div>

        <!-- Kardex Table -->
        <div class="card overflow-hidden p-0">
          <div class="px-5 py-4 border-b border-dark-600 flex items-center justify-between">
            <h2 class="text-white font-bold">جدول کاردکس</h2>
            <span class="text-xs text-gray-500">{{ kardex.rows.length }} ردیف</span>
          </div>
          <div class="overflow-x-auto">
            <table class="w-full">
              <thead>
                <tr class="bg-dark-700 border-b border-dark-500 text-right">
                  <th class="px-4 py-3 text-gray-400 text-xs">#</th>
                  <th class="px-4 py-3 text-gray-400 text-xs">تاریخ</th>
                  <th class="px-4 py-3 text-gray-400 text-xs">نوع رویداد</th>
                  <th class="px-4 py-3 text-gray-400 text-xs text-center">ورود</th>
                  <th class="px-4 py-3 text-gray-400 text-xs text-center">خروج</th>
                  <th class="px-4 py-3 text-gray-400 text-xs text-center">مانده</th>
                  <th class="px-4 py-3 text-gray-400 text-xs">قیمت واحد</th>
                  <th class="px-4 py-3 text-gray-400 text-xs">ارزش</th>
                  <th class="px-4 py-3 text-gray-400 text-xs">سند</th>
                  <th class="px-4 py-3 text-gray-400 text-xs">طرف</th>
                </tr>
              </thead>

              <!-- مانده اول دوره -->
              <tbody>
                <tr class="bg-dark-700/50 border-b border-dark-600">
                  <td class="px-4 py-3 text-gray-500 text-xs">—</td>
                  <td class="px-4 py-3 text-gray-500 text-xs">{{ fromDate || 'ابتدا' }}</td>
                  <td class="px-4 py-3">
                    <span class="text-xs bg-gray-500/10 text-gray-400 px-2 py-0.5 rounded">موجودی اول دوره</span>
                  </td>
                  <td class="px-4 py-3 text-center text-gray-500 text-xs">—</td>
                  <td class="px-4 py-3 text-center text-gray-500 text-xs">—</td>
                  <td class="px-4 py-3 text-center font-bold text-white">{{ kardex.summary?.opening_balance }}</td>
                  <td class="px-4 py-3 text-gray-500 text-xs">—</td>
                  <td class="px-4 py-3 text-gray-500 text-xs">—</td>
                  <td class="px-4 py-3 text-gray-500 text-xs">—</td>
                  <td class="px-4 py-3 text-gray-500 text-xs">—</td>
                </tr>

                <tr v-for="(row, i) in kardex.rows" :key="row.id"
                    class="border-b border-dark-600 hover:bg-dark-700/30 transition-colors">
                  <td class="px-4 py-3 text-gray-500 text-xs">{{ i + 1 }}</td>
                  <td class="px-4 py-3 text-gray-400 text-xs whitespace-nowrap">
                    {{ formatDate(row.created_at) }}
                  </td>
                  <td class="px-4 py-3">
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs"
                          :class="txClass(row.transaction_type)">
                      {{ txIcon(row.transaction_type) }} {{ txLabel(row.transaction_type) }}
                    </span>
                  </td>
                  <!-- ورود -->
                  <td class="px-4 py-3 text-center">
                    <span v-if="isIn(row.transaction_type)" class="text-green-400 font-bold text-sm">
                      {{ row.quantity }}
                    </span>
                    <span v-else class="text-gray-600 text-xs">—</span>
                  </td>
                  <!-- خروج -->
                  <td class="px-4 py-3 text-center">
                    <span v-if="isOut(row.transaction_type)" class="text-red-400 font-bold text-sm">
                      {{ row.quantity }}
                    </span>
                    <span v-else class="text-gray-600 text-xs">—</span>
                  </td>
                  <!-- مانده -->
                  <td class="px-4 py-3 text-center">
                    <span class="text-primary font-bold">{{ row.balance_after }}</span>
                  </td>
                  <td class="px-4 py-3 text-gray-300 text-xs">{{ formatPrice(row.unit_price) }}</td>
                  <td class="px-4 py-3 text-white text-xs font-medium">{{ formatPrice(row.total_value) }}</td>
                  <td class="px-4 py-3">
                    <span v-if="row.reference_number"
                          class="text-xs font-mono text-gray-400 bg-dark-600 px-1.5 py-0.5 rounded">
                      {{ row.reference_number }}
                    </span>
                  </td>
                  <td class="px-4 py-3 text-gray-400 text-xs">{{ row.party_name || '—' }}</td>
                </tr>

                <!-- مانده آخر دوره -->
                <tr class="bg-primary/5 border-t-2 border-primary/30">
                  <td class="px-4 py-3 text-gray-500 text-xs">—</td>
                  <td class="px-4 py-3 text-gray-400 text-xs">{{ toDate || 'اکنون' }}</td>
                  <td class="px-4 py-3">
                    <span class="text-xs bg-primary/10 text-primary px-2 py-0.5 rounded font-bold">
                      موجودی پایان دوره
                    </span>
                  </td>
                  <td class="px-4 py-3 text-center text-green-400 text-xs font-bold">{{ kardex.summary?.total_in }}</td>
                  <td class="px-4 py-3 text-center text-red-400 text-xs font-bold">{{ kardex.summary?.total_out }}</td>
                  <td class="px-4 py-3 text-center text-primary font-black text-lg">{{ kardex.summary?.closing_balance }}</td>
                  <td class="px-4 py-3 text-green-400 text-xs">{{ formatPrice(kardex.summary?.total_in_value) }}</td>
                  <td class="px-4 py-3 text-red-400 text-xs">{{ formatPrice(kardex.summary?.total_out_value) }}</td>
                  <td colspan="2"></td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </template>

      <!-- No selection -->
      <div v-else-if="!selectedProductId" class="text-center py-16">
        <div class="text-5xl mb-4">📊</div>
        <h3 class="text-white text-xl font-bold mb-2">کاردکس کالا</h3>
        <p class="text-gray-400">یک کالا را از لیست بالا انتخاب کنید تا کاردکس آن نمایش داده شود.</p>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useProductStore } from '@/store/products'
import { useTransactionStore, TRANSACTION_TYPES } from '@/store/transactions'

const productStore   = useProductStore()
const txStore        = useTransactionStore()
const products       = computed(() => productStore.products)
const selectedProductId = ref('')
const fromDate       = ref('')
const toDate         = ref('')
const loading        = ref(false)
const kardex         = ref({})

const txInfo  = (v) => TRANSACTION_TYPES.find(t => t.value === v) || {}
const txLabel = (v) => txInfo(v).label || v
const txIcon  = (v) => txInfo(v).icon || ''
const isIn    = (v) => ['receipt', 'return_in', 'initial'].includes(v)
const isOut   = (v) => ['issue', 'return_out', 'scrap'].includes(v)

const txClass = (v) => ({
  receipt:    'bg-green-500/10 text-green-400',
  issue:      'bg-red-500/10 text-red-400',
  return_in:  'bg-blue-500/10 text-blue-400',
  return_out: 'bg-orange-500/10 text-orange-400',
  transfer:   'bg-purple-500/10 text-purple-400',
  adjustment: 'bg-yellow-500/10 text-yellow-400',
  scrap:      'bg-red-700/10 text-red-600',
  initial:    'bg-teal-500/10 text-teal-400',
})[v] || 'bg-gray-500/10 text-gray-400'

const formatPrice = (n) => new Intl.NumberFormat('fa-IR').format(Math.round(n || 0)) + ' ت'
const formatDate  = (d) => new Date(d).toLocaleString('fa-IR', { dateStyle: 'short', timeStyle: 'short' })

const loadKardex = async () => {
  if (!selectedProductId.value) return
  loading.value = true
  const params = {}
  if (fromDate.value) params.from_date = fromDate.value
  if (toDate.value)   params.to_date   = toDate.value
  const result = await txStore.fetchKardex(selectedProductId.value, params)
  if (result.success) kardex.value = result.data
  loading.value = false
}

onMounted(async () => {
  await productStore.fetchProducts({ per_page: 200 })
})
</script>
