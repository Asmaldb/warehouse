<template>
  <Teleport to="body">
    <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center p-4">
      <!-- پس‌زمینه تیره -->
      <div class="absolute inset-0 bg-black/70 backdrop-blur-sm" @click="$emit('close')"></div>

      <!-- محتوای مدال -->
      <div class="relative w-full max-w-lg card animate-slide-up">
        <div class="flex items-center justify-between mb-6">
          <h2 class="text-xl font-bold text-white">
            {{ product ? 'ویرایش کالا' : 'افزودن کالا جدید' }}
          </h2>
          <button @click="$emit('close')" class="text-gray-400 hover:text-white transition-colors">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
        </div>

        <form @submit.prevent="handleSubmit" class="space-y-4">
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-400 mb-1">نام کالا *</label>
              <input v-model="form.name" type="text" class="input-field" placeholder="نام کالا" required />
              <span v-if="errors.name" class="text-red-400 text-xs mt-1">{{ errors.name[0] }}</span>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-400 mb-1">کد کالا *</label>
              <input v-model="form.code" type="text" class="input-field" placeholder="کد یکتا" required />
              <span v-if="errors.code" class="text-red-400 text-xs mt-1">{{ errors.code[0] }}</span>
            </div>
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-400 mb-1">تعداد *</label>
              <input v-model.number="form.quantity" type="number" min="0" class="input-field" placeholder="0" required />
              <span v-if="errors.quantity" class="text-red-400 text-xs mt-1">{{ errors.quantity[0] }}</span>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-400 mb-1">قیمت (تومان) *</label>
              <input v-model.number="form.price" type="number" min="0" step="0.01" class="input-field" placeholder="0" required />
              <span v-if="errors.price" class="text-red-400 text-xs mt-1">{{ errors.price[0] }}</span>
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-400 mb-1">توضیحات</label>
            <textarea v-model="form.description" rows="3" class="input-field resize-none" placeholder="توضیحات کالا..."></textarea>
          </div>

          <div v-if="errors.general" class="bg-red-500/10 border border-red-500/30 rounded-lg p-3 text-red-400 text-sm">
            {{ errors.general }}
          </div>

          <div class="flex gap-3 pt-2">
            <button type="submit" class="btn-primary flex-1" :disabled="loading">
              <span v-if="loading" class="flex items-center justify-center gap-2">
                <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                </svg>
                در حال ذخیره...
              </span>
              <span v-else>{{ product ? 'ذخیره تغییرات' : 'افزودن کالا' }}</span>
            </button>
            <button type="button" @click="$emit('close')" class="btn-secondary flex-1">انصراف</button>
          </div>
        </form>
      </div>
    </div>
  </Teleport>
</template>

<script setup>
import { reactive, watch, ref } from 'vue'
import { productsApi } from '@/api/products'

const props = defineProps({
  show:    Boolean,
  product: { type: Object, default: null },
})

const emit = defineEmits(['close', 'saved'])

const loading = ref(false)
const errors  = reactive({})

const form = reactive({
  name: '', code: '', quantity: 0, price: 0, description: '',
})

watch(() => props.product, (val) => {
  if (val) {
    Object.assign(form, { ...val })
  } else {
    Object.assign(form, { name: '', code: '', quantity: 0, price: 0, description: '' })
  }
}, { immediate: true })

async function handleSubmit() {
  loading.value = true
  Object.keys(errors).forEach(k => delete errors[k])

  try {
    if (props.product) {
      await productsApi.update(props.product.id, form)
    } else {
      await productsApi.create(form)
    }
    emit('saved')
    emit('close')
  } catch (err) {
    if (err.response?.data?.errors) {
      Object.assign(errors, err.response.data.errors)
    } else {
      errors.general = err.response?.data?.message || 'خطایی رخ داد'
    }
  } finally {
    loading.value = false
  }
}
</script>