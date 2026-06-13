<template>
  <div class="min-h-screen bg-black pt-16">
    <div class="max-w-7xl mx-auto px-4 py-10">
      <!-- هدر صفحه -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
        <div>
          <h1 class="text-2xl font-bold text-white">مدیریت کالاها</h1>
          <p class="text-gray-400 text-sm mt-1">{{ totalItems }} کالا در انبار</p>
        </div>
        <button @click="openModal(null)" class="btn-primary flex items-center gap-2">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
          </svg>
          افزودن کالا
        </button>
      </div>

      <!-- فیلترها -->
      <div class="flex flex-col sm:flex-row gap-3 mb-6">
        <div class="relative flex-1">
          <svg class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/>
          </svg>
          <input v-model="searchQuery" @input="debouncedSearch" type="text"
            class="input-field pr-10"
            placeholder="جستجوی نام، کد یا توضیحات..." />
        </div>

        <select v-model="sortBy" @change="fetchProducts" class="input-field sm:w-48">
          <option value="created_at">تاریخ ثبت</option>
          <option value="name">نام کالا</option>
          <option value="price">قیمت</option>
          <option value="quantity">تعداد</option>
        </select>

        <button @click="toggleSortOrder" class="btn-secondary flex items-center gap-2 sm:w-auto">
          <svg class="w-4 h-4" :class="{ 'rotate-180': sortOrder === 'asc' }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4h13M3 8h9m-9 4h6m4 0l4-4m0 0l4 4m-4-4v12"/>
          </svg>
          {{ sortOrder === 'desc' ? 'نزولی' : 'صعودی' }}
        </button>
      </div>

      <!-- جدول -->
      <div class="card p-0 overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead class="border-b border-gray-800">
              <tr>
                <th class="text-right text-gray-400 text-xs font-medium py-4 px-6">نام کالا</th>
                <th class="text-right text-gray-400 text-xs font-medium py-4 px-4">کد</th>
                <th class="text-right text-gray-400 text-xs font-medium py-4 px-4">تعداد</th>
                <th class="text-right text-gray-400 text-xs font-medium py-4 px-4">قیمت</th>
                <th class="text-right text-gray-400 text-xs font-medium py-4 px-4">تاریخ ثبت</th>
                <th class="text-right text-gray-400 text-xs font-medium py-4 px-6">عملیات</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="loading">
                <td colspan="6" class="text-center py-16 text-gray-500">
                  <svg class="animate-spin w-8 h-8 mx-auto mb-3 text-primary-400" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                  </svg>
                  در حال بارگذاری...
                </td>
              </tr>

              <tr v-else-if="products.length === 0">
                <td colspan="6" class="text-center py-16 text-gray-500">
                  <svg class="w-12 h-12 mx-auto mb-3 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                  </svg>
                  کالایی یافت نشد
                </td>
              </tr>

              <tr v-else v-for="product in products" :key="product.id"
                class="border-b border-gray-800/50 hover:bg-gray-800/30 transition-colors">
                <td class="py-4 px-6">
                  <div class="font-medium text-white">{{ product.name }}</div>
                  <div v-if="product.description" class="text-gray-500 text-xs mt-0.5 truncate max-w-48">{{ product.description }}</div>
                </td>
                <td class="py-4 px-4">
                  <span class="text-xs font-mono bg-gray-800 text-gray-300 px-2 py-1 rounded">{{ product.code }}</span>
                </td>
                <td class="py-4 px-4">
                  <span :class="product.quantity < 10 ? 'text-red-400' : 'text-white'">
                    {{ product.quantity }}
                  </span>
                </td>
                <td class="py-4 px-4 text-gray-300">{{ Number(product.price).toLocaleString('fa-IR') }} ت</td>
                <td class="py-4 px-4 text-gray-500 text-sm">{{ formatDate(product.created_at) }}</td>
                <td class="py-4 px-6">
                  <div class="flex items-center gap-2">
                    <button @click="openModal(product)"
                      class="p-2 text-gray-400 hover:text-primary-400 hover:bg-primary-400/10 rounded-lg transition-all">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                      </svg>
                    </button>
                    <button @click="deleteProduct(product)"
                      class="p-2 text-gray-400 hover:text-red-400 hover:bg-red-400/10 rounded-lg transition-all">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                      </svg>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- صفحه‌بندی -->
        <div v-if="pagination && pagination.last_page > 1"
          class="flex items-center justify-between px-6 py-4 border-t border-gray-800">
          <span class="text-gray-500 text-sm">صفحه {{ pagination.current_page }} از {{ pagination.last_page }}</span>
          <div class="flex gap-2">
            <button :disabled="pagination.current_page === 1"
              @click="changePage(pagination.current_page - 1)"
              class="btn-secondary px-3 py-1.5 text-sm disabled:opacity-40 disabled:cursor-not-allowed">
              قبلی
            </button>
            <button :disabled="pagination.current_page === pagination.last_page"
              @click="changePage(pagination.current_page + 1)"
              class="btn-secondary px-3 py-1.5 text-sm disabled:opacity-40 disabled:cursor-not-allowed">
              بعدی
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- مدال افزودن/ویرایش -->
  <ProductModal
    :show="showModal"
    :product="editingProduct"
    @close="showModal = false"
    @saved="fetchProducts" />
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { productsApi } from '@/api/products'
import ProductModal from '@/components/ProductModal.vue'

const products       = ref([])
const loading        = ref(true)
const searchQuery    = ref('')
const sortBy         = ref('created_at')
const sortOrder      = ref('desc')
const pagination     = ref(null)
const totalItems     = ref(0)
const currentPage    = ref(1)
const showModal      = ref(false)
const editingProduct = ref(null)
let   debounceTimer  = null

async function fetchProducts() {
  loading.value = true
  try {
    const res = await productsApi.getAll({
      search:     searchQuery.value || undefined,
      sort_by:    sortBy.value,
      sort_order: sortOrder.value,
      page:       currentPage.value,
    })
    products.value   = res.data.data
    pagination.value = res.data
    totalItems.value = res.data.total
  } finally {
    loading.value = false
  }
}

function debouncedSearch() {
  clearTimeout(debounceTimer)
  debounceTimer = setTimeout(() => {
    currentPage.value = 1
    fetchProducts()
  }, 400)
}

function toggleSortOrder() {
  sortOrder.value = sortOrder.value === 'desc' ? 'asc' : 'desc'
  fetchProducts()
}

function changePage(page) {
  currentPage.value = page
  fetchProducts()
}

function openModal(product) {
  editingProduct.value = product
  showModal.value      = true
}

async function deleteProduct(product) {
  if (!confirm(`آیا از حذف "${product.name}" اطمینان دارید؟`)) return
  await productsApi.delete(product.id)
  fetchProducts()
}

function formatDate(dateStr) {
  return new Date(dateStr).toLocaleDateString('fa-IR')
}

onMounted(fetchProducts)
</script>