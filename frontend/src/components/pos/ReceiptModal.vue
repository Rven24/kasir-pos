<template>
  <Teleport to="body">
    <div
      v-if="isOpen"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/75 backdrop-blur-sm"
      @keydown.esc="closeModal"
    >
    <div
      class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-2xl max-w-lg w-full max-h-[92vh] flex flex-col overflow-hidden animate-in fade-in zoom-in-95 duration-200"
    >
      <!-- Modal Header (Screen Only) -->
      <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between bg-slate-50 dark:bg-slate-950/60">
        <div class="flex items-center gap-2.5">
          <div class="w-8 h-8 rounded-lg bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
            <Printer class="w-4 h-4" />
          </div>
          <div>
            <h3 class="font-bold text-slate-800 dark:text-slate-100 text-sm sm:text-base">
              Struk Pembayaran Thermal
            </h3>
            <p class="text-xs text-slate-500">Live preview struk transaksi kasir</p>
          </div>
        </div>

        <div class="flex items-center gap-2">
          <!-- Paper Size Toggle -->
          <div class="flex bg-slate-200 dark:bg-slate-800 p-0.5 rounded-lg text-xs font-semibold">
            <button
              @click="paperSize = '58mm'"
              class="px-2 py-1 rounded transition-colors"
              :class="paperSize === '58mm' ? 'bg-white dark:bg-slate-700 shadow-sm text-emerald-600 dark:text-emerald-400' : 'text-slate-500'"
            >
              58mm
            </button>
            <button
              @click="paperSize = '80mm'"
              class="px-2 py-1 rounded transition-colors"
              :class="paperSize === '80mm' ? 'bg-white dark:bg-slate-700 shadow-sm text-emerald-600 dark:text-emerald-400' : 'text-slate-500'"
            >
              80mm
            </button>
          </div>

          <button
            @click="closeModal"
            class="p-1.5 rounded-lg text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors"
          >
            <X class="w-5 h-5" />
          </button>
        </div>
      </div>

      <!-- Live Receipt Container -->
      <div class="flex-1 overflow-y-auto p-4 sm:p-6 bg-slate-100 dark:bg-slate-950 flex justify-center">
        <!-- Receipt Paper Area -->
        <div
          id="thermal-receipt-print"
          class="bg-white text-slate-900 shadow-md font-mono-num transition-all select-none border border-dashed border-slate-300"
          :class="paperSize === '58mm' ? 'w-[58mm] max-w-[240px] p-3 text-[11px]' : 'w-[80mm] max-w-[320px] p-4 text-[12px]'"
          style="font-family: 'JetBrains Mono', 'Courier New', Courier, monospace;"
        >
          <!-- Store Header -->
          <div class="text-center pb-3 border-b border-dashed border-slate-400">
            <div class="font-bold text-sm tracking-wide uppercase">{{ storeInfo.store_name || 'KASIRKU MART & CAFE' }}</div>
            <div class="text-[10px] text-slate-600 leading-tight mt-0.5">{{ storeInfo.store_address || 'Jl. Jend. Sudirman No. 45, Jakarta' }}</div>
            <div class="text-[10px] text-slate-600 leading-tight">Telp: {{ storeInfo.store_phone || '0812-3456-7890' }}</div>
          </div>

          <!-- Transaction Meta -->
          <div class="py-2 border-b border-dashed border-slate-400 text-[10px] space-y-0.5">
            <div class="flex justify-between">
              <span>No. Inv</span>
              <span class="font-semibold">{{ transaction?.invoice_number || 'INV-20261111-XXXX' }}</span>
            </div>
            <div class="flex justify-between">
              <span>Waktu</span>
              <span>{{ formatDateTime(transaction?.created_at) }}</span>
            </div>
            <div class="flex justify-between">
              <span>Kasir</span>
              <span>{{ transaction?.user?.name || cashierName || 'Kasir 01' }}</span>
            </div>
            <div class="flex justify-between">
              <span>Pelanggan</span>
              <span>{{ transaction?.customer_name || 'Pelanggan Umum' }}</span>
            </div>
          </div>

          <!-- Items Table -->
          <div class="py-2 border-b border-dashed border-slate-400">
            <div class="text-[10px] font-semibold flex justify-between mb-1 pb-1 border-b border-slate-200">
              <span>ITEM</span>
              <span>TOTAL</span>
            </div>
            <div
              v-for="(item, idx) in (transaction?.details || [])"
              :key="idx"
              class="mb-1.5 last:mb-0 leading-tight"
            >
              <div class="font-medium truncate">{{ item.product?.name || item.name }}</div>
              <div class="flex justify-between text-slate-600 text-[10px]">
                <span>{{ item.quantity }} x {{ formatRupiah(item.unit_price) }}</span>
                <span class="font-semibold text-slate-900">{{ formatRupiah(item.subtotal) }}</span>
              </div>
            </div>
          </div>

          <!-- Financial Calculation -->
          <div class="py-2 border-b border-dashed border-slate-400 text-[11px] space-y-1">
            <div class="flex justify-between text-[10px] text-slate-600">
              <span>Subtotal</span>
              <span>{{ formatRupiah(calculatedSubtotal) }}</span>
            </div>
            <div v-if="Number(transaction?.discount) > 0" class="flex justify-between text-[10px] text-emerald-600">
              <span>Diskon</span>
              <span>-{{ formatRupiah(transaction?.discount) }}</span>
            </div>
            <div class="flex justify-between text-[10px] text-slate-600">
              <span>PPN (11%)</span>
              <span>{{ formatRupiah(transaction?.tax) }}</span>
            </div>
            <div class="flex justify-between font-bold text-xs pt-1 border-t border-slate-200">
              <span>TOTAL</span>
              <span>{{ formatRupiah(transaction?.total_amount) }}</span>
            </div>
            <div class="flex justify-between text-[10px] pt-1">
              <span class="uppercase">Bayar ({{ transaction?.payment_method || 'TUNAI' }})</span>
              <span>{{ formatRupiah(transaction?.pay_amount) }}</span>
            </div>
            <div class="flex justify-between text-[10px] font-semibold text-slate-800">
              <span>Kembalian</span>
              <span>{{ formatRupiah(transaction?.change_amount) }}</span>
            </div>
          </div>

          <!-- Footer & Barcode Simulation -->
          <div class="pt-3 text-center text-[10px] text-slate-600 leading-tight">
            <div class="whitespace-pre-line">{{ storeInfo.receipt_footer || 'Terima kasih atas kunjungan Anda!\nBarang yang sudah dibeli tidak dapat ditukar.' }}</div>
            
            <!-- Simulated Barcode line -->
            <div class="mt-3 flex flex-col items-center justify-center">
              <div class="h-8 w-4/5 bg-slate-800 flex items-center justify-center text-white text-[9px] tracking-widest font-mono">
                |||| | |||||| || | |||| ||
              </div>
              <span class="text-[9px] mt-0.5 tracking-wider">{{ transaction?.invoice_number }}</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Modal Footer (Action Buttons) -->
      <div class="p-4 border-t border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 flex flex-wrap items-center justify-between gap-3">
        <button
          @click="closeModal"
          class="px-4 py-2 rounded-xl text-xs font-semibold border border-slate-300 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 transition-colors flex items-center gap-1.5"
        >
          <RotateCcw class="w-3.5 h-3.5" />
          Transaksi Baru (ESC)
        </button>

        <div class="flex items-center gap-2">
          <button
            @click="handlePrint"
            class="px-5 py-2.5 rounded-xl text-xs font-bold bg-emerald-600 hover:bg-emerald-500 active:scale-95 text-white shadow-lg shadow-emerald-600/20 transition-all flex items-center gap-2"
          >
            <Printer class="w-4 h-4" />
            Cetak Struk Thermal
          </button>
        </div>
      </div>
    </div>
  </div>
  </Teleport>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Printer, X, RotateCcw } from 'lucide-vue-next'

const props = defineProps({
  isOpen: {
    type: Boolean,
    default: false,
  },
  transaction: {
    type: Object,
    default: null,
  },
  cashierName: {
    type: String,
    default: '',
  },
  storeInfo: {
    type: Object,
    default: () => ({}),
  },
})

const emit = defineEmits(['close', 'printed'])

const paperSize = ref('58mm')

const calculatedSubtotal = computed(() => {
  if (!props.transaction?.details) return 0
  return props.transaction.details.reduce((sum, item) => sum + Number(item.subtotal || 0), 0)
})

function formatRupiah(amount) {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0,
    maximumFractionDigits: 0,
  }).format(Number(amount || 0))
}

function formatDateTime(dateStr) {
  if (!dateStr) return new Date().toLocaleString('id-ID')
  const date = new Date(dateStr)
  return date.toLocaleDateString('id-ID', {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
  }) + ' ' + date.toLocaleTimeString('id-ID', {
    hour: '2-digit',
    minute: '2-digit',
  })
}

function handlePrint() {
  window.print()
  emit('printed')
}

function closeModal() {
  emit('close')
}
</script>
