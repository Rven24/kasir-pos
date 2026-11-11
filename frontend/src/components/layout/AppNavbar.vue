<template>
  <header class="h-16 border-b border-slate-200 dark:border-slate-800 bg-white/90 dark:bg-slate-900/90 backdrop-blur-md px-4 sm:px-6 flex items-center justify-between z-30 sticky top-0 transition-colors">
    <!-- Left: Brand / Title -->
    <div class="flex items-center gap-3">
      <button
        @click="$emit('toggle-sidebar')"
        class="p-2 rounded-xl text-slate-500 hover:text-slate-800 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors lg:hidden"
      >
        <Menu class="w-5 h-5" />
      </button>

      <div class="flex items-center gap-2.5">
        <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-emerald-600 to-teal-400 text-white flex items-center justify-center shadow-md shadow-emerald-500/20">
          <Zap class="w-5 h-5 fill-current" />
        </div>
        <div>
          <div class="flex items-center gap-2">
            <span class="font-extrabold text-base tracking-tight text-slate-900 dark:text-slate-100">
              KasirKu<span class="text-emerald-500">.POS</span>
            </span>
            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-700 dark:bg-emerald-950/70 dark:text-emerald-400">
              <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
              Live POS
            </span>
          </div>
          <p class="text-[11px] text-slate-400 hidden sm:block">Smart & Fast Point-of-Sale System</p>
        </div>
      </div>
    </div>

    <!-- Right: Controls & User Profile -->
    <div class="flex items-center gap-2 sm:gap-3">
      <!-- Keyboard Shortcuts Tooltip Trigger -->
      <button
        @click="showShortcuts = true"
        class="hidden sm:flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-slate-200 dark:border-slate-800 text-xs font-semibold text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors cursor-pointer"
        title="Daftar Pintasan Keyboard"
      >
        <Keyboard class="w-4 h-4 text-emerald-500" />
        <span>Pintasan</span>
      </button>

      <!-- Fullscreen Toggle -->
      <button
        @click="toggleFullscreen"
        class="p-2 rounded-xl border border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors cursor-pointer"
        :title="isFullscreen ? 'Keluar Fullscreen' : 'Mode Layar Penuh'"
      >
        <Minimize v-if="isFullscreen" class="w-4 h-4 text-emerald-500" />
        <Maximize v-else class="w-4 h-4" />
      </button>

      <!-- Dark / Light Mode Toggle -->
      <button
        @click="toggleDarkMode"
        class="p-2 rounded-xl border border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors cursor-pointer"
        :title="isDark ? 'Beralih ke Mode Terang' : 'Beralih ke Mode Gelap'"
      >
        <Sun v-if="isDark" class="w-4 h-4 text-amber-400" />
        <Moon v-else class="w-4 h-4 text-indigo-500" />
      </button>

      <!-- User Profile Badge -->
      <div class="flex items-center gap-2 pl-2 border-l border-slate-200 dark:border-slate-800">
        <div class="w-8 h-8 rounded-full bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-200 flex items-center justify-center font-bold text-xs uppercase">
          {{ userInitials }}
        </div>
        <div class="hidden md:block text-left">
          <div class="text-xs font-bold text-slate-800 dark:text-slate-200 truncate max-w-[130px]">
            {{ authStore.user?.name || 'Kasir' }}
          </div>
          <div class="text-[10px] uppercase font-bold text-emerald-600 dark:text-emerald-400">
            {{ authStore.role }}
          </div>
        </div>

        <!-- Logout Button -->
        <button
          @click="handleLogout"
          class="p-2 rounded-xl text-slate-400 hover:text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-950/40 transition-colors cursor-pointer"
          title="Keluar / Ganti Akun"
        >
          <LogOut class="w-4 h-4" />
        </button>
      </div>
    </div>

    <!-- Quick Shortcuts Modal (Teleported directly to <body> so it escapes header stacking context) -->
    <Teleport to="body">
      <div
        v-if="showShortcuts"
        class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm"
        @click.self="showShortcuts = false"
      >
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 max-w-sm w-full shadow-2xl animate-in zoom-in-95 duration-150">
          <div class="flex justify-between items-center mb-4">
            <h4 class="font-bold text-sm text-slate-900 dark:text-slate-100 flex items-center gap-2">
              <Keyboard class="w-4 h-4 text-emerald-500" />
              Pintasan Keyboard (Quick Keys)
            </h4>
            <button
              @click="showShortcuts = false"
              class="p-1 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 rounded-lg"
            >
              <X class="w-4 h-4" />
            </button>
          </div>

          <div class="space-y-2.5 text-xs">
            <div class="flex justify-between items-center p-2 rounded-xl bg-slate-50 dark:bg-slate-800/60">
              <span class="text-slate-600 dark:text-slate-300">Buka Menu Checkout</span>
              <kbd class="px-2 py-1 rounded bg-white dark:bg-slate-700 border border-slate-300 dark:border-slate-600 font-mono font-bold text-emerald-600">F2</kbd>
            </div>
            <div class="flex justify-between items-center p-2 rounded-xl bg-slate-50 dark:bg-slate-800/60">
              <span class="text-slate-600 dark:text-slate-300">Konfirmasi Bayar / Lanjut</span>
              <kbd class="px-2 py-1 rounded bg-white dark:bg-slate-700 border border-slate-300 dark:border-slate-600 font-mono font-bold text-emerald-600">F4</kbd>
            </div>
            <div class="flex justify-between items-center p-2 rounded-xl bg-slate-50 dark:bg-slate-800/60">
              <span class="text-slate-600 dark:text-slate-300">Simpan / Gantung Transaksi</span>
              <kbd class="px-2 py-1 rounded bg-white dark:bg-slate-700 border border-slate-300 dark:border-slate-600 font-mono font-bold text-emerald-600">F8</kbd>
            </div>
            <div class="flex justify-between items-center p-2 rounded-xl bg-slate-50 dark:bg-slate-800/60">
              <span class="text-slate-600 dark:text-slate-300">Batal / Bersihkan Keranjang</span>
              <kbd class="px-2 py-1 rounded bg-white dark:bg-slate-700 border border-slate-300 dark:border-slate-600 font-mono font-bold text-rose-500">ESC</kbd>
            </div>
            <div class="flex justify-between items-center p-2 rounded-xl bg-slate-50 dark:bg-slate-800/60">
              <span class="text-slate-600 dark:text-slate-300">Cari Produk / Scan Barcode</span>
              <kbd class="px-2 py-1 rounded bg-white dark:bg-slate-700 border border-slate-300 dark:border-slate-600 font-mono font-bold text-emerald-600">/</kbd>
            </div>
          </div>

          <button
            @click="showShortcuts = false"
            class="w-full mt-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-xs font-bold text-white shadow-md shadow-emerald-600/20 transition-all cursor-pointer"
          >
            Mengerti
          </button>
        </div>
      </div>
    </Teleport>
  </header>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import {
  Zap,
  Menu,
  Keyboard,
  Maximize,
  Minimize,
  Sun,
  Moon,
  LogOut,
  X,
} from 'lucide-vue-next'

defineEmits(['toggle-sidebar'])

const router = useRouter()
const authStore = useAuthStore()

const isDark = ref(false)
const isFullscreen = ref(false)
const showShortcuts = ref(false)

const userInitials = computed(() => {
  const name = authStore.user?.name || 'K'
  return name.slice(0, 2).toUpperCase()
})

function syncThemeClass(dark) {
  if (dark) {
    document.documentElement.classList.add('dark')
    localStorage.setItem('theme', 'dark')
  } else {
    document.documentElement.classList.remove('dark')
    localStorage.setItem('theme', 'light')
  }
}

function toggleDarkMode() {
  isDark.value = !isDark.value
  syncThemeClass(isDark.value)
}

function toggleFullscreen() {
  if (!document.fullscreenElement) {
    document.documentElement.requestFullscreen().then(() => {
      isFullscreen.value = true
    }).catch(err => console.warn(err))
  } else {
    if (document.exitFullscreen) {
      document.exitFullscreen().then(() => {
        isFullscreen.value = false
      })
    }
  }
}

async function handleLogout() {
  await authStore.logout()
  router.push('/login')
}

onMounted(() => {
  const savedTheme = localStorage.getItem('theme')
  if (savedTheme === 'dark' || (!savedTheme && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
    isDark.value = true
    syncThemeClass(true)
  } else {
    isDark.value = false
    syncThemeClass(false)
  }

  document.addEventListener('fullscreenchange', () => {
    isFullscreen.value = !!document.fullscreenElement
  })
})
</script>
