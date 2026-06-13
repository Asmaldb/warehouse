
<template>
  <nav class="fixed top-0 left-0 right-0 z-50 bg-black/80 backdrop-blur-md border-b border-gray-800">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex items-center justify-between h-16">
        <!-- لوگو -->
        <RouterLink to="/" class="flex items-center gap-2 group">
          <div class="w-8 h-8 rounded-lg bg-primary-400 flex items-center justify-center group-hover:scale-110 transition-transform">
            <svg class="w-5 h-5 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
            </svg>
          </div>
          <span class="font-bold text-lg text-white">انبار<span class="text-primary-400">داری</span></span>
        </RouterLink>

        <!-- منوی دسکتاپ -->
        <div class="hidden md:flex items-center gap-8">
          <RouterLink v-for="link in navLinks" :key="link.name"
            :to="link.path"
            class="text-gray-400 hover:text-white transition-colors text-sm font-medium"
            active-class="text-primary-400">
            {{ link.label }}
          </RouterLink>
        </div>

        <!-- دکمه‌های احراز هویت -->
        <div class="flex items-center gap-3">
          <template v-if="!authStore.isAuthenticated">
            <RouterLink to="/login" class="btn-secondary text-sm px-4 py-2">ورود</RouterLink>
            <RouterLink to="/register" class="btn-primary text-sm px-4 py-2">ثبت‌نام</RouterLink>
          </template>
          <template v-else>
            <RouterLink to="/dashboard" class="text-gray-400 hover:text-white text-sm transition-colors">
              {{ authStore.user?.name }}
            </RouterLink>
            <button @click="handleLogout" class="btn-secondary text-sm px-4 py-2">خروج</button>
          </template>
        </div>
      </div>
    </div>
  </nav>
</template>

<script setup>
import { useAuthStore } from '@/stores/auth'
import { useRouter } from 'vue-router'

const authStore = useAuthStore()
const router    = useRouter()

const navLinks = [
  { to: '/',             label: 'خانه' },
  { to: '/about',        label: 'درباره ما' },
  { to: '/contact',      label: 'تماس' },
  { to: '/products',     label: 'کالاها',      auth: true },
  { to: '/transactions', label: 'رویدادها',    auth: true },   // NEW
  { to: '/kardex',       label: 'کاردکس',      auth: true },   // NEW
]

async function handleLogout() {
  await authStore.logout()
  router.push('/')
}
</script>