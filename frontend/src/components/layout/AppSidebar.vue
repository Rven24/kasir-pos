<template>
  <div>
    <!-- Mobile Backdrop -->
    <div
      v-if="isOpen"
      class="fixed inset-0 z-40 bg-black/60 backdrop-blur-xs lg:hidden"
      @click="$emit('close')"
    ></div>

    <!-- Sidebar Container -->
    <aside
      class="fixed top-16 bottom-0 left-0 z-40 w-64 bg-white dark:bg-slate-900 border-r border-slate-200 dark:border-slate-800 flex flex-col transition-transform duration-300 ease-in-out lg:translate-x-0"
      :class="isOpen ? 'translate-x-0' : '-translate-x-full'"
    >
      <!-- Navigation Links -->
      <nav class="flex-1 p-4 space-y-1.5 overflow-y-auto">
        <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 px-3 py-1">
          Menu Utama
        </div>

        <!-- POS Terminal -->
        <router-link
          to="/pos"
          @click="$emit('close')"
          class="flex items-center gap-3 px-3.5 py-3 rounded-2xl text-xs sm:text-sm font-bold transition-all group"
          :class="$route.path === '/pos' ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-600/25' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-slate-100'"
        >
          <ShoppingCart class="w-5 h-5 transition-transform group-hover:scale-110" />
          <span>Kasir / Terminal POS</span>
        </router-link>

        <!-- Inventory (Admin & Owner) -->
        <router-link
          v-if="authStore.canManageProducts"
          to="/inventory"
          @click="$emit('close')"
          class="flex items-center gap-3 px-3.5 py-3 rounded-2xl text-xs sm:text-sm font-bold transition-all group"
          :class="$route.path === '/inventory' ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-600/25' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-slate-100'"
        >
          <Package class="w-5 h-5 transition-transform group-hover:scale-110" />
          <span>Inventaris & Produk</span>
        </router-link>

        <!-- Analytics & Reports (Admin & Owner) -->
        <router-link
          v-if="authStore.canViewReports"
          to="/analytics"
          @click="$emit('close')"
          class="flex items-center gap-3 px-3.5 py-3 rounded-2xl text-xs sm:text-sm font-bold transition-all group"
          :class="$route.path === '/analytics' ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-600/25' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-slate-100'"
        >
          <BarChart3 class="w-5 h-5 transition-transform group-hover:scale-110" />
          <span>Laporan & Analitik</span>
        </router-link>

        <!-- Settings & Users (Owner / Admin) -->
        <router-link
          v-if="authStore.canManageUsers || authStore.isAdmin"
          to="/settings"
          @click="$emit('close')"
          class="flex items-center gap-3 px-3.5 py-3 rounded-2xl text-xs sm:text-sm font-bold transition-all group"
          :class="$route.path === '/settings' ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-600/25' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-slate-100'"
        >
          <Sliders class="w-5 h-5 transition-transform group-hover:scale-110" />
          <span>Pengaturan Toko</span>
        </router-link>
      </nav>

      <!-- Bottom Card: Fast POS Focus Mode Info -->
      <div class="p-4 border-t border-slate-200 dark:border-slate-800">
        <div class="p-3.5 rounded-2xl bg-gradient-to-br from-emerald-500/10 via-emerald-500/5 to-transparent border border-emerald-500/20 text-xs text-slate-600 dark:text-slate-300">
          <div class="font-bold text-emerald-600 dark:text-emerald-400 flex items-center gap-1.5 mb-1">
            <Sparkles class="w-4 h-4" />
            Mode Kasir Cepat
          </div>
          <p class="text-[11px] leading-relaxed text-slate-500 dark:text-slate-400">
            Tekan <kbd class="px-1 py-0.5 rounded bg-slate-200 dark:bg-slate-700 font-mono text-[10px]">F2</kbd> untuk Checkout dan <kbd class="px-1 py-0.5 rounded bg-slate-200 dark:bg-slate-700 font-mono text-[10px]">F4</kbd> untuk Bayar instan.
          </p>
        </div>
      </div>
    </aside>
  </div>
</template>

<script setup>
import { useAuthStore } from '@/stores/auth'
import {
  ShoppingCart,
  Package,
  BarChart3,
  Sliders,
  Sparkles,
} from 'lucide-vue-next'

defineProps({
  isOpen: {
    type: Boolean,
    default: false,
  },
})

defineEmits(['close'])

const authStore = useAuthStore()
</script>
