
import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/store/auth'

const routes = [
  { path: '/',             name: 'home',         component: () => import('@/views/Home.vue') },
  { path: '/about',        name: 'about',        component: () => import('@/views/About.vue') },
  { path: '/contact',      name: 'contact',      component: () => import('@/views/Contact.vue') },
  {
    path: '/login',        name: 'login',
    component: () => import('@/views/Login.vue'),
    meta: { guestOnly: true },
  },
  {
    path: '/register',     name: 'register',
    component: () => import('@/views/Register.vue'),
    meta: { guestOnly: true },
  },
  {
    path: '/dashboard',    name: 'dashboard',
    component: () => import('@/views/Dashboard.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/products',     name: 'products',
    component: () => import('@/views/Products.vue'),
    meta: { requiresAuth: true },
  },
  // ─── NEW ────────────────────────────────────────────────────────
  {
    path: '/transactions', name: 'transactions',
    component: () => import('@/views/Transactions.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/kardex',       name: 'kardex',
    component: () => import('@/views/Kardex.vue'),
    meta: { requiresAuth: true },
  },
  // ───────────────────────────────────────────────────────────────
  { path: '/:pathMatch(.*)*', redirect: '/' },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
  scrollBehavior: () => ({ top: 0 }),
})

router.beforeEach((to, from, next) => {
  const authStore = useAuthStore()
  if (to.meta.requiresAuth && !authStore.isAuthenticated) return next('/login')
  if (to.meta.guestOnly  && authStore.isAuthenticated)    return next('/dashboard')
  next()
})

export default router
