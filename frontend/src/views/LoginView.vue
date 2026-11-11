<template>
  <div class="min-h-screen flex items-center justify-center p-4 bg-gradient-to-br from-slate-100 via-slate-50 to-emerald-50/30 dark:from-slate-950 dark:via-slate-900 dark:to-emerald-950/20">
    <div class="max-w-md w-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl shadow-2xl p-6 sm:p-8 animate-in fade-in zoom-in-95 duration-200">
      <!-- Brand Logo & Header -->
      <div class="text-center mb-8">
        <div class="inline-flex w-14 h-14 rounded-2xl bg-gradient-to-tr from-emerald-600 to-teal-400 text-white items-center justify-center shadow-lg shadow-emerald-500/25 mb-4">
          <Zap class="w-8 h-8 fill-current" />
        </div>
        <h1 class="text-2xl font-extrabold text-slate-900 dark:text-slate-100 tracking-tight">
          KasirKu<span class="text-emerald-500">.POS</span>
        </h1>
        <p class="text-xs sm:text-sm text-slate-500 mt-1">
          Smart & Fast Point-of-Sale System for Modern Retail & F&B
        </p>
      </div>

      <!-- Login Form -->
      <form @submit.prevent="handleLogin" class="space-y-4">
        <div>
          <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
            Email Kasir / Pengguna
          </label>
          <div class="relative">
            <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400">
              <Mail class="w-4 h-4" />
            </span>
            <input
              v-model="email"
              type="email"
              required
              placeholder="nama@kasirku.com"
              class="w-full pl-10 pr-4 py-3 rounded-2xl bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 text-sm text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/50 focus:border-emerald-500 transition-all"
            />
          </div>
        </div>

        <div>
          <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
            Kata Sandi
          </label>
          <div class="relative">
            <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400">
              <Lock class="w-4 h-4" />
            </span>
            <input
              v-model="password"
              :type="showPassword ? 'text' : 'password'"
              required
              placeholder="••••••••"
              class="w-full pl-10 pr-11 py-3 rounded-2xl bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 text-sm text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/50 focus:border-emerald-500 transition-all"
            />
            <button
              type="button"
              @click="showPassword = !showPassword"
              class="absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200"
            >
              <EyeOff v-if="showPassword" class="w-4 h-4" />
              <Eye v-else class="w-4 h-4" />
            </button>
          </div>
        </div>

        <button
          type="submit"
          :disabled="authStore.loading"
          class="w-full py-3.5 px-4 rounded-2xl bg-emerald-600 hover:bg-emerald-500 active:scale-[0.99] text-white font-extrabold text-sm shadow-xl shadow-emerald-600/25 transition-all flex items-center justify-center gap-2 disabled:opacity-50 mt-2"
        >
          <LogIn class="w-4 h-4" />
          <span>{{ authStore.loading ? 'Memverifikasi...' : 'Masuk ke Terminal Kasir' }}</span>
        </button>
      </form>

      <!-- Quick Demo Account Pills -->
      <div class="mt-8 pt-6 border-t border-slate-200 dark:border-slate-800">
        <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400 text-center mb-3">
          Akun Demo Siap Pakai (Klik untuk Isi):
        </p>
        <div class="grid grid-cols-3 gap-2">
          <button
            type="button"
            @click="fillDemo('kasir')"
            class="py-2 px-2.5 rounded-xl border border-slate-200 dark:border-slate-700/80 hover:bg-emerald-50 dark:hover:bg-emerald-950/40 hover:border-emerald-500/40 text-xs font-semibold text-slate-700 dark:text-slate-300 transition-all text-center"
          >
            <div class="font-bold text-emerald-600 dark:text-emerald-400">Kasir</div>
            <div class="text-[10px] text-slate-400">Shift 1</div>
          </button>

          <button
            type="button"
            @click="fillDemo('admin')"
            class="py-2 px-2.5 rounded-xl border border-slate-200 dark:border-slate-700/80 hover:bg-indigo-50 dark:hover:bg-indigo-950/40 hover:border-indigo-500/40 text-xs font-semibold text-slate-700 dark:text-slate-300 transition-all text-center"
          >
            <div class="font-bold text-indigo-600 dark:text-indigo-400">Admin</div>
            <div class="text-[10px] text-slate-400">Stok & Edit</div>
          </button>

          <button
            type="button"
            @click="fillDemo('owner')"
            class="py-2 px-2.5 rounded-xl border border-slate-200 dark:border-slate-700/80 hover:bg-purple-50 dark:hover:bg-purple-950/40 hover:border-purple-500/40 text-xs font-semibold text-slate-700 dark:text-slate-300 transition-all text-center"
          >
            <div class="font-bold text-purple-600 dark:text-purple-400">Owner</div>
            <div class="text-[10px] text-slate-400">Semua Akses</div>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { Zap, Mail, Lock, Eye, EyeOff, LogIn } from 'lucide-vue-next'

const router = useRouter()
const authStore = useAuthStore()

const email = ref('kasir@kasirku.com')
const password = ref('password')
const showPassword = ref(false)

async function handleLogin() {
  const res = await authStore.login(email.value, password.value)
  if (res.success) {
    router.push('/pos')
  }
}

function fillDemo(role) {
  if (role === 'owner') {
    email.value = 'owner@kasirku.com'
  } else if (role === 'admin') {
    email.value = 'admin@kasirku.com'
  } else {
    email.value = 'kasir@kasirku.com'
  }
  password.value = 'password'
}
</script>
