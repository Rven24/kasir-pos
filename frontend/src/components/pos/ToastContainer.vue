<template>
  <div class="fixed bottom-5 right-5 z-50 flex flex-col gap-2 max-w-sm w-full pointer-events-none">
    <TransitionGroup
      enter-active-class="transform transition ease-out duration-300"
      enter-from-class="translate-y-4 opacity-0 scale-95"
      enter-to-class="translate-y-0 opacity-100 scale-100"
      leave-active-class="transition ease-in duration-200"
      leave-from-class="opacity-100 scale-100"
      leave-to-class="opacity-0 scale-95"
    >
      <div
        v-for="toast in toastStore.toasts"
        :key="toast.id"
        class="pointer-events-auto flex items-start gap-3 p-3.5 rounded-xl shadow-xl border text-sm font-medium backdrop-blur-md transition-all"
        :class="{
          'bg-emerald-950/90 border-emerald-500/30 text-emerald-100': toast.type === 'success',
          'bg-rose-950/90 border-rose-500/30 text-rose-100': toast.type === 'error',
          'bg-amber-950/90 border-amber-500/30 text-amber-100': toast.type === 'warning',
          'bg-slate-900/90 border-slate-700/50 text-slate-100': toast.type === 'info',
        }"
      >
        <!-- Icon -->
        <div class="mt-0.5 shrink-0">
          <CheckCircle2 v-if="toast.type === 'success'" class="w-5 h-5 text-emerald-400" />
          <AlertCircle v-else-if="toast.type === 'error'" class="w-5 h-5 text-rose-400" />
          <AlertTriangle v-else-if="toast.type === 'warning'" class="w-5 h-5 text-amber-400" />
          <Info v-else class="w-5 h-5 text-sky-400" />
        </div>

        <!-- Content -->
        <div class="flex-1 text-xs sm:text-sm leading-relaxed">
          {{ toast.message }}
        </div>

        <!-- Dismiss button -->
        <button
          @click="toastStore.remove(toast.id)"
          class="shrink-0 text-slate-400 hover:text-white transition-colors"
        >
          <X class="w-4 h-4" />
        </button>
      </div>
    </TransitionGroup>
  </div>
</template>

<script setup>
import { useToastStore } from '@/stores/toast'
import {
  CheckCircle2,
  AlertCircle,
  AlertTriangle,
  Info,
  X,
} from 'lucide-vue-next'

const toastStore = useToastStore()
</script>
