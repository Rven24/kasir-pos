<template>
  <Teleport to="body">
    <div
      v-if="isOpen"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm"
    >
    <div
      class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-2xl max-w-sm w-full overflow-hidden animate-in fade-in zoom-in-95 duration-200"
    >
      <!-- Header -->
      <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between bg-slate-50 dark:bg-slate-950/60">
        <div class="flex items-center gap-2.5">
          <div class="w-8 h-8 rounded-lg bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
            <QrCode class="w-4 h-4" />
          </div>
          <div>
            <h3 class="font-bold text-slate-800 dark:text-slate-100 text-sm">
              QRIS Dinamis Otomatis
            </h3>
            <p class="text-xs text-slate-500">Scan via GoPay, OVO, Dana, BCA, dll</p>
          </div>
        </div>

        <button
          @click="closeModal"
          class="p-1 rounded-lg text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors"
        >
          <X class="w-5 h-5" />
        </button>
      </div>

      <!-- Body / QR Display -->
      <div class="p-6 text-center flex flex-col items-center">
        <!-- Merchant Logo / Name Badge -->
        <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-slate-100 dark:bg-slate-800 text-xs font-semibold text-slate-700 dark:text-slate-300 mb-4">
          <Store class="w-3.5 h-3.5 text-emerald-500" />
          <span>{{ merchantName || 'KASIRKU MART & CAFE' }}</span>
        </div>

        <!-- QR Code Canvas Container -->
        <div class="p-3 bg-white rounded-2xl shadow-inner border border-slate-200 dark:border-slate-700 mb-4 flex flex-col items-center">
          <canvas ref="qrCanvas" class="w-48 h-48 block"></canvas>
          <div class="mt-2 text-[10px] text-slate-500 font-mono tracking-wider">
            NMID: ID102938475601
          </div>
        </div>

        <!-- Total Amount -->
        <div class="mb-4">
          <span class="text-xs text-slate-500">Total Tagihan QRIS</span>
          <div class="text-2xl font-extrabold text-emerald-600 dark:text-emerald-400 font-mono-num tracking-tight">
            {{ formatRupiah(totalAmount) }}
          </div>
        </div>

        <!-- Live Expiry Timer -->
        <div class="flex items-center gap-1.5 text-xs text-amber-600 dark:text-amber-400 font-medium bg-amber-50 dark:bg-amber-950/40 px-3 py-1.5 rounded-lg mb-4">
          <Clock class="w-3.5 h-3.5 animate-spin" />
          <span>Kedaluwarsa dalam: {{ formatTimer(timeLeft) }}</span>
        </div>

        <!-- Status Pulse -->
        <div class="flex items-center gap-2 text-xs text-slate-500">
          <span class="w-2 h-2 rounded-full bg-emerald-500 animate-ping"></span>
          <span>Menunggu konfirmasi scan pelanggan...</span>
        </div>
      </div>

      <!-- Footer Buttons -->
      <div class="p-4 border-t border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950/40 flex flex-col gap-2">
        <button
          @click="simulateSuccess"
          :disabled="isProcessing"
          class="w-full py-2.5 px-4 rounded-xl text-xs font-bold bg-emerald-600 hover:bg-emerald-500 active:scale-[0.99] text-white shadow-md shadow-emerald-600/20 transition-all flex items-center justify-center gap-2 disabled:opacity-50"
        >
          <CheckCircle2 class="w-4 h-4" />
          <span>{{ isProcessing ? 'Memverifikasi...' : 'Simulasi Pembayaran Berhasil' }}</span>
        </button>

        <button
          @click="closeModal"
          class="w-full py-2 px-4 rounded-xl text-xs font-medium border border-slate-300 dark:border-slate-700 hover:bg-slate-200 dark:hover:bg-slate-800 text-slate-600 dark:text-slate-400 transition-colors"
        >
          Batal (Ganti Metode Bayar)
        </button>
      </div>
    </div>
  </div>
  </Teleport>
</template>

<script setup>
import { ref, watch, onUnmounted, nextTick } from 'vue'
import QRCode from 'qrcode'
import { QrCode, Store, Clock, CheckCircle2, X } from 'lucide-vue-next'

const props = defineProps({
  isOpen: {
    type: Boolean,
    default: false,
  },
  totalAmount: {
    type: Number,
    required: true,
  },
  merchantName: {
    type: String,
    default: 'KASIRKU MART & CAFE',
  },
  invoiceNumber: {
    type: String,
    default: '',
  },
})

const emit = defineEmits(['close', 'paid'])

const qrCanvas = ref(null)
const timeLeft = ref(300) // 5 minutes (300s)
const isProcessing = ref(false)
let timerInterval = null

function formatRupiah(amount) {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0,
    maximumFractionDigits: 0,
  }).format(Number(amount || 0))
}

function formatTimer(seconds) {
  const m = Math.floor(seconds / 60)
  const s = seconds % 60
  return `${m.toString().padStart(2, '0')}:${s.toString().padStart(2, '0')}`
}

function generateQr() {
  nextTick(() => {
    if (!qrCanvas.value) return
    // Generate valid simulated standard QRIS payload string
    const payload = `00020101021226670014ID.LINKAJA.WWW01189360091800000000000215ID10293847560152045812530336054${props.totalAmount}5802ID5919${props.merchantName.substring(0, 19)}6007JAKARTA62190115${props.invoiceNumber || 'INV-20261111'}6304`
    QRCode.toCanvas(
      qrCanvas.value,
      payload,
      {
        width: 192,
        margin: 1,
        color: {
          dark: '#0f172a',
          light: '#ffffff',
        },
      },
      (error) => {
        if (error) console.error('QR Gen error:', error)
      }
    )
  })
}

function startTimer() {
  timeLeft.value = 300
  if (timerInterval) clearInterval(timerInterval)
  timerInterval = setInterval(() => {
    if (timeLeft.value > 0) {
      timeLeft.value--
    } else {
      clearInterval(timerInterval)
      closeModal()
    }
  }, 1000)
}

function simulateSuccess() {
  isProcessing.value = true
  setTimeout(() => {
    isProcessing.value = false
    emit('paid')
  }, 600)
}

function closeModal() {
  if (timerInterval) clearInterval(timerInterval)
  emit('close')
}

watch(
  () => props.isOpen,
  (val) => {
    if (val) {
      generateQr()
      startTimer()
    } else {
      if (timerInterval) clearInterval(timerInterval)
    }
  }
)

onUnmounted(() => {
  if (timerInterval) clearInterval(timerInterval)
})
</script>
