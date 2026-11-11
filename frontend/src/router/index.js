import { createRouter, createWebHistory } from 'vue-router'
import LoginView from '@/views/LoginView.vue'
import CashierView from '@/views/CashierView.vue'
import InventoryView from '@/views/InventoryView.vue'
import DashboardView from '@/views/DashboardView.vue'
import SettingsView from '@/views/SettingsView.vue'

const routes = [
  {
    path: '/',
    redirect: '/pos',
  },
  {
    path: '/login',
    name: 'login',
    component: LoginView,
    meta: { guestOnly: true },
  },
  {
    path: '/pos',
    name: 'pos',
    component: CashierView,
    meta: { requiresAuth: true },
  },
  {
    path: '/inventory',
    name: 'inventory',
    component: InventoryView,
    meta: { requiresAuth: true, allowedRoles: ['owner', 'admin'] },
  },
  {
    path: '/analytics',
    name: 'analytics',
    component: DashboardView,
    meta: { requiresAuth: true, allowedRoles: ['owner', 'admin'] },
  },
  {
    path: '/settings',
    name: 'settings',
    component: SettingsView,
    meta: { requiresAuth: true, allowedRoles: ['owner', 'admin'] },
  },
  {
    path: '/:pathMatch(.*)*',
    redirect: '/pos',
  },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

router.beforeEach((to, from, next) => {
  const token = localStorage.getItem('pos_token')
  const userStr = localStorage.getItem('pos_user')
  const user = userStr ? JSON.parse(userStr) : null

  if (to.meta.requiresAuth && !token) {
    return next('/login')
  }

  if (to.meta.guestOnly && token) {
    return next('/pos')
  }

  if (to.meta.allowedRoles && user) {
    if (!to.meta.allowedRoles.includes(user.role)) {
      return next('/pos')
    }
  }

  next()
})

export default router
