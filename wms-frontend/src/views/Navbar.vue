<template>
  <nav class="fixed top-0 left-0 right-0 z-50 bg-black/80 backdrop-blur-md border-b border-gray-800 transition-all duration-300">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex items-center justify-between h-16">

        <!-- لوگو -->
        <RouterLink to="/" class="flex items-center gap-2 group">
          <img src="@/assets/logo.png" alt="SKY Warehouse" class="h-10 w-10 object-contain group-hover:scale-105 transition-transform" />
          <span class="font-bold text-lg text-white hidden sm:block">
            SKY <span class="text-primary-400">Warehouse</span>
          </span>
        </RouterLink>

        <!-- منوی دسکتاپ -->
        <div class="hidden md:flex items-center gap-6">
          <RouterLink
            v-for="link in visibleLinks"
            :key="link.path"
            :to="link.path"
            class="text-gray-400 hover:text-white transition-colors text-sm font-medium"
            active-class="text-primary-400">
            {{ link.label }}
          </RouterLink>
        </div>

        <!-- دکمه‌های احراز هویت -->
        <div class="flex items-center gap-3">
          <template v-if="!authStore.isAuthenticated">
            <RouterLink to="/login"
              class="text-sm px-4 py-2 border border-gray-700 rounded-lg text-gray-300 hover:border-primary-400 hover:text-white transition-all">
              ورود
            </RouterLink>
            <RouterLink to="/register"
              class="text-sm px-4 py-2 bg-primary-400 hover:bg-primary-500 text-black font-semibold rounded-lg transition-all">
              ثبت‌نام
            </RouterLink>
          </template>
          <template v-else>
            <span class="text-gray-400 text-sm hidden sm:block">{{ authStore.user?.name }}</span>
            <button @click="handleLogout"
              class="text-sm px-4 py-2 border border-gray-700 rounded-lg text-gray-300 hover:border-red-500 hover:text-red-400 transition-all">
              خروج
            </button>
          </template>

          <!-- موبایل منو -->
          <button class="md:hidden text-gray-400 hover:text-white" @click="mobileOpen = !mobileOpen">
            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path v-if="!mobileOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
              <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
        </div>
      </div>

      <!-- موبایل منو -->
      <Transition name="slide">
        <div v-if="mobileOpen" class="md:hidden py-3 border-t border-gray-800 space-y-1">
          <RouterLink
            v-for="link in visibleLinks"
            :key="link.path"
            :to="link.path"
            @click="mobileOpen = false"
            class="block px-3 py-2 text-gray-400 hover:text-white hover:bg-gray-800 rounded-lg transition-all text-sm"
            active-class="text-primary-400 bg-gray-800">
            {{ link.label }}
          </RouterLink>
        </div>
      </Transition>
    </div>
  </nav>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/store/auth'   

const authStore = useAuthStore()
const router    = useRouter()
const mobileOpen = ref(false)

const allLinks = [
  { path: '/',             label: 'خانه',     auth: false },
  { path: '/about',        label: 'درباره ما', auth: false },
  { path: '/contact',      label: 'تماس',     auth: false },
  { path: '/products',     label: 'کالاها',   auth: true  },
  { path: '/transactions', label: 'رویدادها', auth: true  },
  { path: '/kardex',       label: 'کاردکس',   auth: true  },
]

// لینک‌هایی که باید نمایش داده شوند
const visibleLinks = computed(() =>
  allLinks.filter(l => !l.auth || authStore.isAuthenticated)
)

async function handleLogout() {
  await authStore.logout()
  mobileOpen.value = false
  router.push('/')
}
</script>

<style scoped>
.slide-enter-active, .slide-leave-active { transition: all 0.25s ease; }
.slide-enter-from, .slide-leave-to { opacity: 0; transform: translateY(-8px); }
</style>
