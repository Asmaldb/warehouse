<template>
  <div class="pt-24 pb-20 px-6">
    <div class="max-w-7xl mx-auto">

      Header
      <div class="mb-8">
        <h1 class="text-4xl font-black text-white mb-2">
          خوش آمدید، <span class="gradient-text">{{ authStore.currentUser?.name }}</span> 👋
        </h1>
        <p class="text-gray-400">وضعیت کلی انبار شما</p>
      </div>

      Stats
      <div class="grid grid-cols-2 md:grid-cols-4 gap-5 mb-8">
        <div class="card group hover:border-primary/40 transition-all" v-for="s in stats" :key="s.title">
          <div class="flex items-center justify-between mb-3">
            <div class="w-11 h-11 rounded-xl flex items-center justify-center text-xl"
                 :style="{ background: s.bg }">{{ s.icon }}</div>
            <span class="text-xs px-2 py-0.5 rounded-full"
                  :class="s.up ? 'bg-green-500/10 text-green-400' : 'bg-red-500/10 text-red-400'">
              {{ s.up ? '↑' : '↓' }} {{ s.pct }}%
            </span>
          </div>
          <div class="text-2xl font-black text-white mb-0.5">{{ s.value }}</div>
          <div class="text-gray-400 text-xs">{{ s.title }}</div>
        </div>
      </div>

      Quick Access Grid
      <div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-8">
        <RouterLink to="/transactions" class="card group hover:border-primary/40 transition-all flex items-center gap-4 cursor-pointer">
          <div class="w-12 h-12 bg-green-500/10 rounded-xl flex items-center justify-center text-2xl group-hover:bg-green-500/20 transition-all">📥</div>
          <div>
            <div class="text-white font-bold">ثبت رویداد</div>
            <div class="text-gray-400 text-sm">ورود / خروج / مرجوعی</div>
          </div>
        </RouterLink>
        <RouterLink to="/kardex" class="card group hover:border-amber-500/40 transition-all flex items-center gap-4 cursor-pointer">
          <div class="w-12 h-12 bg-amber-500/10 rounded-xl flex items-center justify-center text-2xl group-hover:bg-amber-500/20 transition-all">📊</div>
          <div>
            <div class="text-white font-bold">کاردکس کالا</div>
            <div class="text-gray-400 text-sm">تاریخچه کامل هر کالا</div>
          </div>
        </RouterLink>
        <RouterLink to="/products" class="card group hover:border-blue-500/40 transition-all flex items-center gap-4 cursor-pointer">
          <div class="w-12 h-12 bg-blue-500/10 rounded-xl flex items-center justify-center text-2xl group-hover:bg-blue-500/20 transition-all">📦</div>
          <div>
            <div class="text-white font-bold">مدیریت کالاها</div>
            <div class="text-gray-400 text-sm">افزودن و ویرایش کالا</div>
          </div>
        </RouterLink>
      </div>

      Low Stock Warning
      <div v-if="lowStock.length" class="card border-red-500/20 mb-6">
        <h2 class="text-white font-bold mb-4 flex items-center gap-2">
          <span class="text-red-400">⚠️</span> کالاهای کم‌موجود
        </h2>
        <div class="space-y-2">
          <div v-for="p in lowStock" :key="p.id"
               class="flex items-center justify-between bg-dark-700 p-3 rounded-xl">
            <div>
              <div class="text-white text-sm font-medium">{{ p.name }}</div>
              <div class="text-gray-500 text-xs">{{ p.code }}</div>
            </div>
            <div class="flex items-center gap-3">
              <span class="text-red-400 font-bold text-lg">{{ p.quantity }}</span>
              <RouterLink to="/transactions" class="text-xs bg-primary/10 text-primary px-3 py-1 rounded-lg hover:bg-primary/20">
                ثبت ورود
              </RouterLink>
            </div>
          </div>
        </div>
      </div>

      Recent transactions
      <div class="card">
        <div class="flex items-center justify-between mb-4">
          <h2 class="text-white font-bold">آخرین رویدادها</h2>
          <RouterLink to="/transactions" class="text-primary text-sm">مشاهده همه ←</RouterLink>
        </div>
        <div class="space-y-2">
          <div v-for="tx in recentTransactions" :key="tx.id"
               class="flex items-center justify-between p-3 bg-dark-700 rounded-xl">
            <div class="flex items-center gap-3">
              <span class="text-lg">{{ txIcon(tx.transaction_type) }}</span>
              <div>
                <div class="text-white text-sm">{{ tx.product?.name }}</div>
                <div class="text-gray-500 text-xs">{{ txLabel(tx.transaction_type) }}</div>
              </div>
            </div>
            <div class="text-right">
              <div class="font-bold text-sm"
                   :class="isIn(tx.transaction_type) ? 'text-green-400' : 'text-red-400'">
                {{ isIn(tx.transaction_type) ? '+' : '−' }}{{ tx.quantity }}
              </div>
              <div class="text-gray-600 text-xs">{{ formatDate(tx.created_at) }}</div>
            </div>
          </div>
          <div v-if="recentTransactions.length === 0" class="text-center py-4 text-gray-500 text-sm">
            هنوز رویدادی ثبت نشده
          </div>
        </div>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useAuthStore } from '@/store/auth'
import { useProductStore } from '@/store/products'
import { useTransactionStore, TRANSACTION_TYPES } from '@/store/transactions'

const authStore    = useAuthStore()
const productStore = useProductStore()
const txStore      = useTransactionStore()
const lowStock     = ref([])
const recentTransactions = ref([])

const txInfo  = (v) => TRANSACTION_TYPES.find(t => t.value === v) || {}
const txLabel = (v) => txInfo(v).label || v
const txIcon  = (v) => txInfo(v).icon || ''
const isIn    = (v) => ['receipt', 'return_in', 'initial'].includes(v)
const formatDate = (d) => new Date(d).toLocaleDateString('fa-IR')

const stats = computed(() => [
  { icon: '📦', title: 'کل کالاها',     value: productStore.pagination?.total || 0, bg: 'rgba(128,161,255,0.1)', up: true,  pct: 5 },
  { icon: '📥', title: 'ورودی ۳۰ روز', value: txStore.dashboardStats?.stats?.receipt?.total_qty || 0, bg: 'rgba(74,222,128,0.1)', up: true, pct: 8 },
  { icon: '📤', title: 'خروجی ۳۰ روز', value: txStore.dashboardStats?.stats?.issue?.total_qty || 0, bg: 'rgba(248,113,113,0.1)', up: false, pct: 3 },
  { icon: '💰', title: 'کالاهای کم‌موجود', value: lowStock.value.length, bg: 'rgba(251,191,36,0.1)', up: false, pct: 0 },
])

onMounted(async () => {
  await productStore.fetchProducts()
  await txStore.fetchTransactions({ per_page: 5 })
  const statsResult = await txStore.fetchDashboardStats()
  if (statsResult.success) {
    lowStock.value = statsResult.data.low_stock || []
  }
  recentTransactions.value = txStore.transactions
})
</script>