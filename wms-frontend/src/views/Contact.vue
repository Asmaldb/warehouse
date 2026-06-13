<template>
  <div class="min-h-screen bg-black pt-16">
    <div class="max-w-2xl mx-auto px-4 py-20">
      <div class="text-center mb-12">
        <h1 class="text-4xl font-bold text-white mb-4">تماس با ما</h1>
        <p class="text-gray-400">سوال یا پیشنهادی دارید؟ با ما در میان بگذارید</p>
      </div>

      <div class="card animate-slide-up">
        <form @submit.prevent="handleSubmit" class="space-y-5" v-if="!success">
          <div>
            <label class="block text-sm font-medium text-gray-400 mb-2">نام *</label>
            <input v-model="form.name" type="text" class="input-field" placeholder="نام شما" required />
            <span v-if="errors.name" class="text-red-400 text-xs mt-1 block">{{ errors.name[0] }}</span>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-400 mb-2">ایمیل *</label>
            <input v-model="form.email" type="email" class="input-field" placeholder="email@example.com" required />
            <span v-if="errors.email" class="text-red-400 text-xs mt-1 block">{{ errors.email[0] }}</span>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-400 mb-2">پیام *</label>
            <textarea v-model="form.message" rows="5" class="input-field resize-none" placeholder="پیام خود را بنویسید..." required></textarea>
            <span v-if="errors.message" class="text-red-400 text-xs mt-1 block">{{ errors.message[0] }}</span>
          </div>

          <div v-if="errors.general" class="bg-red-500/10 border border-red-500/30 rounded-lg p-3 text-red-400 text-sm">
            {{ errors.general }}
          </div>

          <button type="submit" class="btn-primary w-full py-3" :disabled="loading">
            <span v-if="loading">در حال ارسال...</span>
            <span v-else>ارسال پیام</span>
          </button>
        </form>

        <div v-else class="text-center py-8">
          <div class="w-16 h-16 rounded-full bg-green-500/10 flex items-center justify-center mx-auto mb-4">
            <svg class="w-8 h-8 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
          </div>
          <h3 class="text-white font-bold text-xl mb-2">پیام ارسال شد!</h3>
          <p class="text-gray-400">به زودی با شما تماس خواهیم گرفت.</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref } from 'vue'
import { contactApi } from '@/api/contact'

const loading = ref(false)
const success = ref(false)
const errors  = reactive({})
const form    = reactive({ name: '', email: '', message: '' })

async function handleSubmit() {
  loading.value = true
  Object.keys(errors).forEach(k => delete errors[k])

  try {
    await contactApi.send(form)
    success.value = true
  } catch (err) {
    if (err.response?.data?.errors) {
      Object.assign(errors, err.response.data.errors)
    } else {
      errors.general = 'خطایی رخ داد. لطفاً دوباره تلاش کنید.'
    }
  } finally {
    loading.value = false
  }
}
</script>