<template>
  <Teleport to="body">
    <div
      v-if="isOpen"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm"
      @keydown.esc="closeModal"
    >
    <div
      class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl shadow-2xl max-w-lg w-full overflow-hidden animate-in fade-in zoom-in-95 duration-200 flex flex-col max-h-[95vh]"
    >
      <!-- Header -->
      <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between bg-slate-50/75 dark:bg-slate-950/40">
        <div>
          <h3 class="font-extrabold text-slate-900 dark:text-slate-100 text-lg flex items-center gap-2">
            <CreditCard class="w-5 h-5 text-emerald-500" />
            Pembayaran Transaksi
          </h3>
          <p class="text-xs text-slate-500">Pilih metode & selesaikan transaksi kasir</p>
        </div>

        <button
          @click="closeModal"
          class="p-1.5 rounded-xl text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors"
        >
          <X class="w-5 h-5" />
        </button>
      </div>

      <!-- Scrollable Content -->
      <div class="p-6 overflow-y-auto space-y-5 flex-1">
        <!-- Total Tagihan Box -->
        <div class="p-4 rounded-2xl bg-gradient-to-br from-emerald-500/10 via-emerald-500/5 to-transparent border border-emerald-500/20 text-center">
          <span class="text-xs font-semibold uppercase tracking-wider text-emerald-600 dark:text-emerald-400">
            Total Tagihan Belanja
          </span>
          <div class="text-3xl sm:text-4xl font-extrabold text-slate-900 dark:text-slate-100 font-mono-num mt-1">
            {{ formatRupiah(totalAmount) }}
          </div>
          <div class="flex items-center justify-center gap-3 mt-2 text-xs text-slate-500">
            <span>Subtotal: {{ formatRupiah(subtotal) }}</span>
            <span v-if="discount > 0" class="text-emerald-600 font-medium">Diskon: -{{ formatRupiah(discount) }}</span>
            <span>PPN (11%): {{ formatRupiah(tax) }}</span>
          </div>
        </div>

        <!-- Payment Method Tabs -->
        <div>
          <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">
            Metode Pembayaran
          </label>
          <div class="grid grid-cols-3 gap-2">
            <button
              type="button"
              @click="selectPaymentMethod('cash')"
              class="py-3 px-3 rounded-2xl border text-xs sm:text-sm font-bold flex flex-col items-center gap-1.5 transition-all"
              :class="paymentMethod === 'cash' ? 'bg-emerald-600 border-emerald-500 text-white shadow-lg shadow-emerald-600/25 scale-[1.02]' : 'bg-slate-50 dark:bg-slate-800/60 border-slate-200 dark:border-slate-700/80 text-slate-700 dark:text-slate-300 hover:border-slate-300'"
            >
              <Banknote class="w-5 h-5" />
              <span>Tunai</span>
            </button>

            <button
              type="button"
              @click="selectPaymentMethod('qris')"
              class="py-3 px-3 rounded-2xl border text-xs sm:text-sm font-bold flex flex-col items-center gap-1.5 transition-all"
              :class="paymentMethod === 'qris' ? 'bg-emerald-600 border-emerald-500 text-white shadow-lg shadow-emerald-600/25 scale-[1.02]' : 'bg-slate-50 dark:bg-slate-800/60 border-slate-200 dark:border-slate-700/80 text-slate-700 dark:text-slate-300 hover:border-slate-300'"
            >
              <QrCode class="w-5 h-5" />
              <span>QRIS</span>
            </button>

            <button
              type="button"
              @click="selectPaymentMethod('transfer')"
              class="py-3 px-3 rounded-2xl border text-xs sm:text-sm font-bold flex flex-col items-center gap-1.5 transition-all"
              :class="paymentMethod === 'transfer' ? 'bg-emerald-600 border-emerald-500 text-white shadow-lg shadow-emerald-600/25 scale-[1.02]' : 'bg-slate-50 dark:bg-slate-800/60 border-slate-200 dark:border-slate-700/80 text-slate-700 dark:text-slate-300 hover:border-slate-300'"
            >
              <ArrowRightLeft class="w-5 h-5" />
              <span>Transfer</span>
            </button>
          </div>
        </div>

        <!-- Cash Details & Quick Suggestions (When Cash Selected) -->
        <div v-if="paymentMethod === 'cash'" class="space-y-4">
          <div>
            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">
              Uang Diterima (Rp)
            </label>
            <div class="relative">
              <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 font-bold text-base">
                Rp
              </span>
              <input
                ref="payInputRef"
                v-model="cashInput"
                type="number"
                step="500"
                placeholder="0"
                class="w-full pl-12 pr-4 py-3.5 rounded-2xl bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-slate-100 font-mono-num text-xl font-bold focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                @keydown.enter.prevent="handleConfirmPayment"
              />
            </div>
          </div>

          <!-- Quick Suggestion Buttons -->
          <div>
            <div class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider mb-1.5">
              Uang Pas & Pecahan Cepat:
            </div>
            <div class="flex flex-wrap gap-1.5">
              <button
                type="button"
                @click="setCashAmount(totalAmount)"
                class="px-3 py-1.5 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-emerald-100 dark:hover:bg-emerald-950/50 hover:text-emerald-600 text-xs font-bold text-slate-700 dark:text-slate-300 transition-colors"
              >
                Uang Pas
              </button>
              <button
                v-for="amt in suggestedCashAmounts"
                :key="amt"
                type="button"
                @click="setCashAmount(amt)"
                class="px-3 py-1.5 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-xs font-semibold text-slate-700 dark:text-slate-300 font-mono-num transition-colors"
              >
                {{ formatRupiah(amt) }}
              </button>
            </div>
          </div>

          <!-- Kembalian Display Box -->
          <div
            class="p-4 rounded-2xl border transition-all"
            :class="changeAmount >= 0 ? 'bg-slate-50 dark:bg-slate-800/40 border-slate-200 dark:border-slate-700' : 'bg-rose-50 dark:bg-rose-950/30 border-rose-200 dark:border-rose-800'"
          >
            <div class="flex justify-between items-center">
              <span class="text-xs font-bold uppercase tracking-wider" :class="changeAmount >= 0 ? 'text-slate-600 dark:text-slate-400' : 'text-rose-600 dark:text-rose-400'">
                {{ changeAmount >= 0 ? 'Kembalian' : 'Uang Kurang' }}
              </span>
              <div
                class="text-2xl font-extrabold font-mono-num"
                :class="changeAmount >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400'"
              >
                {{ formatRupiah(Math.abs(changeAmount)) }}
              </div>
            </div>
          </div>
        </div>

        <!-- Non-Cash Note (QRIS / Transfer) -->
        <div v-else class="p-4 rounded-2xl bg-indigo-50 dark:bg-indigo-950/40 border border-indigo-200 dark:border-indigo-800/60 text-xs text-indigo-700 dark:text-indigo-300 space-y-1">
          <div class="font-bold flex items-center gap-1.5">
            <Info class="w-4 h-4 text-indigo-500" />
            <span>Pembayaran Non-Tunai</span>
          </div>
          <p class="leading-relaxed">
            Jumlah bayar akan disesuaikan otomatis senilai total tagihan (Rp {{ formatRupiah(totalAmount) }}). Klik 'Lanjutkan' untuk membuka QR code QRIS atau verifikasi transfer.
          </p>
        </div>

        <!-- Customer Name & Notes (Optional) -->
        <div class="pt-2 border-t border-slate-200 dark:border-slate-800 space-y-3">
          <div>
            <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">
              Nama Pelanggan (Opsional)
            </label>
            <input
              v-model="customerName"
              type="text"
              placeholder="Contoh: Meja 4 / Kak Rina"
              class="w-full px-3.5 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-xs text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-1 focus:ring-emerald-500"
            />
          </div>
        </div>
      </div>

      <!-- Footer Buttons -->
      <div class="p-4 sm:p-6 border-t border-slate-200 dark:border-slate-800 bg-slate-50/75 dark:bg-slate-950/40 flex items-center justify-between gap-3">
        <button
          type="button"
          @click="closeModal"
          class="px-4 py-3 rounded-2xl text-xs font-bold border border-slate-300 dark:border-slate-700 hover:bg-slate-200 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 transition-colors"
        >
          Batal (ESC)
        </button>

        <button
          type="button"
          :disabled="isSubmitting || (paymentMethod === 'cash' && changeAmount < 0)"
          @click="handleConfirmPayment"
          class="flex-1 py-3 px-5 rounded-2xl text-sm font-extrabold bg-emerald-600 hover:bg-emerald-500 active:scale-[0.99] text-white shadow-xl shadow-emerald-600/30 transition-all flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed"
        >
          <CheckCircle2 class="w-5 h-5" />
          <span>{{ isSubmitting ? 'Memproses Transaksi...' : 'Bayar Sekarang (F4)' }}</span>
        </button>
      </div>
    </div>
  </div>
  </Teleport>
</template>

<script setup>
import { ref, computed, watch, nextTick } from 'vue'
import {
  CreditCard,
  Banknote,
  QrCode,
  ArrowRightLeft,
  CheckCircle2,
  X,
  Info,
} from 'lucide-vue-next'

const props = defineProps({
  isOpen: {
    type: Boolean,
    default: false,
  },
  subtotal: {
    type: Number,
    required: true,
  },
  discount: {
    type: Number,
    default: 0,
  },
  tax: {
    type: Number,
    default: 0,
  },
  totalAmount: {
    type: Number,
    required: true,
  },
  initialCustomerName: {
    type: String,
    default: '',
  },
})

const emit = defineEmits(['close', 'process-payment', 'open-qris'])

const paymentMethod = ref('cash')
const cashInput = ref('')
const customerName = ref('')
const isSubmitting = ref(false)
const payInputRef = ref(null)

const parsedPayAmount = computed(() => {
  if (paymentMethod.value !== 'cash') {
    return props.totalAmount
  }
  return Number(cashInput.value) || 0
})

const changeAmount = computed(() => {
  return parsedPayAmount.value - props.totalAmount
})

// Suggest popular Indonesian banknote denominations higher than total
const suggestedCashAmounts = computed(() => {
  const tot = props.totalAmount
  const denominations = [10000, 20000, 50000, 100000, 200000, 500000]
  const roundedNext50k = Math.ceil(tot / 50000) * 50000
  const roundedNext100k = Math.ceil(tot / 100000) * 100000

  const list = new Set()
  if (roundedNext50k > tot) list.add(roundedNext50k)
  if (roundedNext100k > tot) list.add(roundedNext100k)

  for (const d of denominations) {
    if (d > tot && list.size < 4) {
      list.add(d)
    }
  }

  return Array.from(list).sort((a, b) => a - b)
})

function formatRupiah(amount) {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0,
    maximumFractionDigits: 0,
  }).format(Number(amount || 0))
}

function selectPaymentMethod(method) {
  paymentMethod.value = method
  if (method === 'cash') {
    nextTick(() => {
      payInputRef.value?.focus()
    })
  }
}

function setCashAmount(amt) {
  cashInput.value = amt.toString()
}

function handleConfirmPayment() {
  if (paymentMethod.value === 'qris') {
    emit('open-qris', {
      customerName: customerName.value,
      payAmount: props.totalAmount,
    })
    return
  }

  if (paymentMethod.value === 'cash' && changeAmount.value < 0) {
    return
  }

  emit('process-payment', {
    payment_method: paymentMethod.value,
    pay_amount: parsedPayAmount.value,
    customer_name: customerName.value,
  })
}

function closeModal() {
  emit('close')
}

watch(
  () => props.isOpen,
  (val) => {
    if (val) {
      cashInput.value = ''
      customerName.value = props.initialCustomerName || ''
      paymentMethod.value = 'cash'
      isSubmitting.value = false
      nextTick(() => {
        payInputRef.value?.focus()
      })
    }
  }
)
</script>
