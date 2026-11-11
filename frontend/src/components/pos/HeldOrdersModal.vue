<template>
  <Teleport to="body">
    <div
      v-if="isOpen"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/75 backdrop-blur-sm"
      @keydown.esc="closeModal"
    >
    <div
      class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl shadow-2xl max-w-md w-full overflow-hidden animate-in fade-in zoom-in-95 duration-200 flex flex-col max-h-[85vh]"
    >
      <!-- Header -->
      <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between bg-slate-50 dark:bg-slate-950/60">
        <div class="flex items-center gap-2.5">
          <div class="w-8 h-8 rounded-xl bg-amber-500/10 text-amber-500 flex items-center justify-center">
            <PauseCircle class="w-4 h-4" />
          </div>
          <div>
            <h3 class="font-bold text-slate-800 dark:text-slate-100 text-sm">
              Transaksi Digantung (Multi-Tab)
            </h3>
            <p class="text-xs text-slate-500">Daftar antrean belanja yang disimpan sementara</p>
          </div>
        </div>

        <button
          @click="closeModal"
          class="p-1.5 rounded-lg text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors"
        >
          <X class="w-5 h-5" />
        </button>
      </div>

      <!-- List -->
      <div class="p-6 overflow-y-auto space-y-3 flex-1">
        <div
          v-if="cartStore.heldTransactions.length === 0"
          class="text-center py-10 text-slate-400"
        >
          <Clock class="w-10 h-10 mx-auto text-slate-300 dark:text-slate-700 mb-2" />
          <p class="text-sm font-semibold">Tidak ada transaksi yang sedang digantung</p>
          <p class="text-xs text-slate-500 mt-0.5">Tekan tombol 'Gantung' di kasir untuk menyimpan order sementara.</p>
        </div>

        <div
          v-for="order in cartStore.heldTransactions"
          :key="order.id"
          class="p-4 rounded-2xl border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/40 hover:border-amber-400/50 transition-all flex items-center justify-between gap-3"
        >
          <div class="min-w-0 flex-1">
            <div class="flex items-center gap-2">
              <span class="font-bold text-slate-900 dark:text-slate-100 text-sm truncate">
                {{ order.label }}
              </span>
              <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-amber-100 dark:bg-amber-950/60 text-amber-700 dark:text-amber-400">
                {{ order.items.length }} item
              </span>
            </div>
            <div class="text-xs text-slate-500 mt-0.5 flex items-center gap-2">
              <span>{{ formatTime(order.heldAt) }}</span>
              <span>•</span>
              <span class="font-mono-num font-bold text-emerald-600 dark:text-emerald-400">
                {{ formatRupiah(order.totalAmount) }}
              </span>
            </div>
          </div>

          <div class="flex items-center gap-1.5 shrink-0">
            <button
              @click="handleRestore(order.id)"
              class="px-3 py-1.5 rounded-xl bg-amber-500 hover:bg-amber-400 text-white font-bold text-xs shadow-sm transition-all flex items-center gap-1"
            >
              <Play class="w-3.5 h-3.5 fill-current" />
              <span>Muat</span>
            </button>
            <button
              @click="cartStore.removeHeldOrder(order.id)"
              class="p-1.5 rounded-xl text-slate-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/40 transition-colors"
              title="Hapus Transaksi Gantung"
            >
              <Trash2 class="w-4 h-4" />
            </button>
          </div>
        </div>
      </div>

      <!-- Footer -->
      <div class="p-4 border-t border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950/40 flex justify-end">
        <button
          @click="closeModal"
          class="px-4 py-2 rounded-xl text-xs font-semibold border border-slate-300 dark:border-slate-700 hover:bg-slate-200 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 transition-colors"
        >
          Tutup
        </button>
      </div>
    </div>
  </div>
  </Teleport>
</template>

<script setup>
import { useCartStore } from '@/stores/cart'
import { PauseCircle, Play, Trash2, Clock, X } from 'lucide-vue-next'

defineProps({
  isOpen: {
    type: Boolean,
    default: false,
  },
})

const emit = defineEmits(['close'])

const cartStore = useCartStore()

function formatRupiah(amount) {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0,
    maximumFractionDigits: 0,
  }).format(Number(amount || 0))
}

function formatTime(isoStr) {
  if (!isoStr) return ''
  const d = new Date(isoStr)
  return d.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' })
}

function handleRestore(id) {
  cartStore.restoreOrder(id)
  emit('close')
}

function closeModal() {
  emit('close')
}
</script>
