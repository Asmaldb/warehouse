<template>
  <div class="min-h-screen bg-black flex items-center justify-center p-4 pt-20">
    <div class="w-full max-w-md">
      <div class="text-center mb-8">
        <div class="w-16 h-16 rounded-2xl bg-primary-400/10 border border-primary-400/30 flex items-center justify-center mx-auto mb-4">
          <svg class="w-8 h-8 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
          </svg>
        </div>
        <h1 class="text-3xl font-bold text-white">ورود به سیستم</h1>
        <p class="text-gray-400 mt-2">به سیستم مدیریت انبار خوش آمدید</p>
      </div>

      <div class="card animate-slide-up">
        <form @submit.prevent="handleLogin" class="space-y-5">
          <div>
            <label class="block text-sm font-medium text-gray-400 mb-2">ایمیل</label>
            <input v-model="form.email" type="email" class="input-field" placeholder="email@example.com" required />
            <span v-if="errors.email" class="text-red-400 text-xs mt-1 block">{{ errors.email[0] }}</span>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-400 mb-2">رمز عبور</label>
            <input v-model="form.password" type="password" class="input-field" placeholder="••••••••" required />
            <span v-if="errors.password" class="text-red-400 text-xs mt-1 block">{{ errors.password[0] }}</span>
          </div>

          <div v-if="errors.general" class="bg-red-500/10 border border-red-500/30 rounded-lg p-3 text-red-400 text-sm">
            {{ errors.general }}
          </div>

          <button type="submit" class="btn-primary w-full py-3 text-base" :disabled="loading">
            <span v-if="loading" class="flex items-center justify-center gap-2">
              <svg class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
              </svg>
              در حال ورود...
            </span>
            <span v-else>ورود</span>
          </button>

          <p class="text-center text-gray-400 text-sm">
            حساب کاربری ندارید؟
            <RouterLink to="/register" class="text-primary-400 hover:text-primary-300 font-medium transition-colors">
              ثبت‌نام کنید
            </RouterLink>
          </p>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const authStore = useAuthStore()
const router    = useRouter()
const loading   = ref(false)
const errors    = reactive({})

const form = reactive({ email: '', password: '' })

async function handleLogin() {
  loading.value = true
  Object.keys(errors).forEach(k => delete errors[k])

  try {
    await authStore.login(form)
    router.push('/dashboard')
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