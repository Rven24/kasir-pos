<template>
  <div class="p-4 sm:p-6 space-y-6 max-w-7xl mx-auto">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 dark:text-slate-100 tracking-tight flex items-center gap-2.5">
          <BarChart3 class="w-6 h-6 text-emerald-500" />
          Laporan & Analitik Penjualan
        </h1>
        <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
          Pantau omzet harian, performa produk terlaris, dan riwayat transaksi secara real-time.
        </p>
      </div>

      <div class="flex items-center gap-2">
        <button
          @click="loadSummary"
          class="px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-800 hover:bg-slate-100 dark:hover:bg-slate-800 text-xs font-bold text-slate-700 dark:text-slate-300 transition-colors flex items-center gap-1.5"
        >
          <RefreshCw class="w-4 h-4 text-emerald-500" :class="{ 'animate-spin': loadingSummary }" />
          <span>Refresh</span>
        </button>

        <button
          @click="exportCsv"
          class="px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 active:scale-95 text-white text-xs font-extrabold shadow-lg shadow-emerald-600/25 transition-all flex items-center gap-2"
        >
          <Download class="w-4 h-4" />
          <span>Ekspor CSV</span>
        </button>
      </div>
    </div>

    <!-- 4 Summary Stat Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
      <!-- Omzet Hari Ini -->
      <div class="p-5 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm relative overflow-hidden">
        <div class="flex justify-between items-start mb-2">
          <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Omzet Hari Ini</span>
          <div class="w-8 h-8 rounded-xl bg-emerald-500/10 text-emerald-500 flex items-center justify-center">
            <Coins class="w-4 h-4" />
          </div>
        </div>
        <div class="text-2xl font-extrabold text-slate-900 dark:text-slate-100 font-mono-num tracking-tight">
          {{ formatRupiah(summary.metrics?.today_revenue) }}
        </div>
        <div class="text-xs text-slate-400 mt-1 flex items-center gap-1">
          <span>Kemarin:</span>
          <span class="font-mono-num font-semibold text-slate-600 dark:text-slate-300">
            {{ formatRupiah(summary.metrics?.yesterday_revenue) }}
          </span>
        </div>
      </div>

      <!-- Transaksi Hari Ini -->
      <div class="p-5 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm relative overflow-hidden">
        <div class="flex justify-between items-start mb-2">
          <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Transaksi Hari Ini</span>
          <div class="w-8 h-8 rounded-xl bg-indigo-500/10 text-indigo-500 flex items-center justify-center">
            <CreditCard class="w-4 h-4" />
          </div>
        </div>
        <div class="text-2xl font-extrabold text-slate-900 dark:text-slate-100 font-mono-num tracking-tight">
          {{ summary.metrics?.today_transactions || 0 }}
          <span class="text-xs text-slate-400 font-normal">struk</span>
        </div>
        <div class="text-xs text-emerald-600 dark:text-emerald-400 mt-1 font-semibold flex items-center gap-1">
          <TrendingUp class="w-3.5 h-3.5" />
          <span>Transaksi Aktif Kasir</span>
        </div>
      </div>

      <!-- Total Omzet Bulan Ini -->
      <div class="p-5 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm relative overflow-hidden">
        <div class="flex justify-between items-start mb-2">
          <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Omzet Bulan Ini</span>
          <div class="w-8 h-8 rounded-xl bg-purple-500/10 text-purple-500 flex items-center justify-center">
            <TrendingUp class="w-4 h-4" />
          </div>
        </div>
        <div class="text-2xl font-extrabold text-slate-900 dark:text-slate-100 font-mono-num tracking-tight">
          {{ formatRupiah(summary.metrics?.month_revenue) }}
        </div>
        <div class="text-xs text-slate-400 mt-1">
          Total: <span class="font-mono-num font-semibold text-slate-600 dark:text-slate-300">{{ summary.metrics?.month_transactions || 0 }}</span> transaksi
        </div>
      </div>

      <!-- Katalog & Alert Stok -->
      <div class="p-5 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm relative overflow-hidden">
        <div class="flex justify-between items-start mb-2">
          <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Produk & Stok</span>
          <div class="w-8 h-8 rounded-xl bg-amber-500/10 text-amber-500 flex items-center justify-center">
            <Package class="w-4 h-4" />
          </div>
        </div>
        <div class="text-2xl font-extrabold text-slate-900 dark:text-slate-100 font-mono-num tracking-tight">
          {{ summary.metrics?.total_products || 0 }}
          <span class="text-xs text-slate-400 font-normal">SKU</span>
        </div>
        <div class="text-xs mt-1 font-semibold" :class="summary.metrics?.low_stock_count > 0 ? 'text-amber-500' : 'text-slate-400'">
          {{ summary.metrics?.low_stock_count > 0 ? `⚠️ ${summary.metrics?.low_stock_count} stok menipis` : '✅ Semua stok aman' }}
        </div>
      </div>
    </div>

    <!-- Charts & Analytics Section (2 Columns) -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- 7-Day Revenue Trend Chart (2 Cols) -->
      <div class="lg:col-span-2 p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm flex flex-col justify-between">
        <div>
          <div class="flex justify-between items-center mb-6">
            <div>
              <h3 class="font-bold text-slate-900 dark:text-slate-100 text-sm sm:text-base">
                Tren Omzet Penjualan (7 Hari Terakhir)
              </h3>
              <p class="text-xs text-slate-500">Statistik harian transaksi berhasil</p>
            </div>
            <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-100 dark:bg-emerald-950/70 text-emerald-700 dark:text-emerald-400">
              Live Data
            </span>
          </div>

          <!-- Bar Chart Container -->
          <div class="h-56 flex items-end justify-between gap-2 pt-6 pb-2 px-2 border-b border-slate-100 dark:border-slate-800">
            <div
              v-for="day in (summary.sales_trend || [])"
              :key="day.date"
              class="flex-1 flex flex-col items-center gap-2 group h-full justify-end"
            >
              <!-- Bar -->
              <div class="w-full max-w-[42px] bg-slate-100 dark:bg-slate-800 rounded-t-xl overflow-hidden relative flex flex-col justify-end transition-all group-hover:brightness-110">
                <div
                  class="w-full bg-gradient-to-t from-emerald-600 to-teal-400 rounded-t-xl transition-all duration-500"
                  :style="{ height: getBarHeight(day.revenue) }"
                ></div>
              </div>

              <!-- Day Label -->
              <span class="text-[10px] font-bold text-slate-500 dark:text-slate-400 truncate text-center w-full">
                {{ day.label }}
              </span>

              <!-- Tooltip on Hover -->
              <div class="absolute bottom-20 opacity-0 group-hover:opacity-100 transition-opacity bg-slate-900 text-white text-[10px] rounded-lg p-2 pointer-events-none shadow-xl z-20 whitespace-nowrap text-center">
                <div class="font-bold font-mono">{{ formatRupiah(day.revenue) }}</div>
                <div class="text-slate-400">{{ day.count }} transaksi</div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Payment Method Breakdown & Top Selling -->
      <div class="p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm space-y-6">
        <!-- Payment Methods -->
        <div>
          <h3 class="font-bold text-slate-900 dark:text-slate-100 text-sm mb-3">
            Metode Pembayaran Hari Ini
          </h3>
          <div class="space-y-2.5 text-xs">
            <div
              v-for="pm in (summary.payment_methods || [])"
              :key="pm.payment_method"
              class="p-3 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700/60 flex items-center justify-between"
            >
              <div class="flex items-center gap-2 font-bold uppercase text-slate-700 dark:text-slate-300">
                <Banknote v-if="pm.payment_method === 'cash'" class="w-4 h-4 text-emerald-500" />
                <QrCode v-else-if="pm.payment_method === 'qris'" class="w-4 h-4 text-indigo-500" />
                <ArrowRightLeft v-else class="w-4 h-4 text-purple-500" />
                <span>{{ pm.payment_method }}</span>
              </div>
              <div class="text-right">
                <div class="font-mono-num font-bold text-slate-900 dark:text-slate-100">
                  {{ formatRupiah(pm.total) }}
                </div>
                <div class="text-[10px] text-slate-400">{{ pm.count }} kali</div>
              </div>
            </div>
            <div v-if="!summary.payment_methods || summary.payment_methods.length === 0" class="text-center py-4 text-slate-400 text-xs">
              Belum ada transaksi hari ini
            </div>
          </div>
        </div>

        <!-- Top Selling Products -->
        <div>
          <h3 class="font-bold text-slate-900 dark:text-slate-100 text-sm mb-3">
            Top Produk Terlaris
          </h3>
          <div class="space-y-2 text-xs">
            <div
              v-for="(tp, idx) in (summary.top_products || [])"
              :key="idx"
              class="flex items-center justify-between p-2 rounded-xl bg-slate-50 dark:bg-slate-800/40"
            >
              <div class="flex items-center gap-2 min-w-0 flex-1">
                <span class="w-5 h-5 rounded-md bg-emerald-500/10 text-emerald-600 font-extrabold text-[10px] flex items-center justify-center shrink-0">
                  #{{ idx + 1 }}
                </span>
                <span class="font-bold text-slate-800 dark:text-slate-200 truncate">
                  {{ tp.product?.name }}
                </span>
              </div>
              <span class="font-mono-num font-bold text-emerald-600 dark:text-emerald-400 shrink-0 ml-2">
                {{ tp.total_qty_sold }} terjual
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Transaction History Table with Filters -->
    <div class="rounded-3xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-sm overflow-hidden p-6 space-y-4">
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
          <h3 class="font-extrabold text-slate-900 dark:text-slate-100 text-base">
            Riwayat Transaksi Penjualan
          </h3>
          <p class="text-xs text-slate-500">Daftar invoice, kasir, dan status pembayaran</p>
        </div>

        <!-- Date Filters -->
        <div class="flex items-center gap-1.5 flex-wrap">
          <button
            v-for="filter in ['today', 'yesterday', 'week', 'month', 'all']"
            :key="filter"
            type="button"
            @click="setFilter(filter)"
            class="px-3 py-1.5 rounded-xl text-xs font-bold transition-all"
            :class="selectedDateFilter === filter ? 'bg-emerald-600 text-white shadow-sm' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-200'"
          >
            {{ filterLabel(filter) }}
          </button>
        </div>
      </div>

      <!-- Transactions Table -->
      <div class="overflow-x-auto rounded-2xl border border-slate-100 dark:border-slate-800">
        <table class="w-full text-left border-collapse text-xs sm:text-sm">
          <thead>
            <tr class="border-b border-slate-200 dark:border-slate-800 bg-slate-50/80 dark:bg-slate-950/60 text-[11px] font-bold uppercase tracking-wider text-slate-500">
              <th class="py-3 px-4">No. Invoice</th>
              <th class="py-3 px-4">Waktu</th>
              <th class="py-3 px-4">Kasir / User</th>
              <th class="py-3 px-4">Pelanggan</th>
              <th class="py-3 px-4">Metode</th>
              <th class="py-3 px-4 text-right">Total</th>
              <th class="py-3 px-4 text-center">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
            <tr v-if="loadingTransactions">
              <td colspan="7" class="py-8 text-center text-slate-400">
                Memuat riwayat transaksi...
              </td>
            </tr>

            <tr v-else-if="transactions.length === 0">
              <td colspan="7" class="py-8 text-center text-slate-400">
                Tidak ada data transaksi pada rentang waktu ini.
              </td>
            </tr>

            <tr
              v-for="trx in transactions"
              :key="trx.id"
              class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition-colors"
            >
              <td class="py-3 px-4 font-mono-num font-bold text-slate-900 dark:text-slate-100">
                {{ trx.invoice_number }}
              </td>
              <td class="py-3 px-4 text-slate-500 text-xs">
                {{ formatDateTime(trx.created_at) }}
              </td>
              <td class="py-3 px-4 font-semibold text-slate-700 dark:text-slate-300">
                {{ trx.user?.name || 'Kasir' }}
              </td>
              <td class="py-3 px-4 text-slate-600 dark:text-slate-400">
                {{ trx.customer_name || 'Pelanggan Umum' }}
              </td>
              <td class="py-3 px-4">
                <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase"
                  :class="{
                    'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/70 dark:text-emerald-400': trx.payment_method === 'cash',
                    'bg-indigo-100 text-indigo-700 dark:bg-indigo-950/70 dark:text-indigo-400': trx.payment_method === 'qris',
                    'bg-purple-100 text-purple-700 dark:bg-purple-950/70 dark:text-purple-400': trx.payment_method === 'transfer',
                  }"
                >
                  {{ trx.payment_method }}
                </span>
              </td>
              <td class="py-3 px-4 text-right font-mono-num font-bold text-emerald-600 dark:text-emerald-400">
                {{ formatRupiah(trx.total_amount) }}
              </td>
              <td class="py-3 px-4 text-center">
                <button
                  @click="openReceiptDetail(trx)"
                  class="px-2.5 py-1 rounded-lg border border-slate-200 dark:border-slate-700 text-xs font-semibold hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 transition-colors inline-flex items-center gap-1"
                >
                  <Printer class="w-3.5 h-3.5 text-emerald-500" />
                  <span>Struk</span>
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Thermal Receipt Modal for Reprinting -->
    <ReceiptModal
      :is-open="showReceiptModal"
      :transaction="selectedReceiptTransaction"
      :cashier-name="selectedReceiptTransaction?.user?.name"
      :store-info="storeSettings"
      @close="showReceiptModal = false"
    />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '@/services/api'
import { useToastStore } from '@/stores/toast'
import ReceiptModal from '@/components/pos/ReceiptModal.vue'
import {
  BarChart3,
  RefreshCw,
  Download,
  Coins,
  CreditCard,
  TrendingUp,
  Package,
  Banknote,
  QrCode,
  ArrowRightLeft,
  Printer,
} from 'lucide-vue-next'

const toast = useToastStore()

const summary = ref({})
const transactions = ref([])
const storeSettings = ref({})
const loadingSummary = ref(false)
const loadingTransactions = ref(false)
const selectedDateFilter = ref('today')

const showReceiptModal = ref(false)
const selectedReceiptTransaction = ref(null)

function formatRupiah(amount) {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0,
    maximumFractionDigits: 0,
  }).format(Number(amount || 0))
}

function formatDateTime(dateStr) {
  if (!dateStr) return '-'
  const d = new Date(dateStr)
  return d.toLocaleDateString('id-ID', { day: '2-digit', month: 'short' }) + ' ' + d.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' })
}

function filterLabel(f) {
  const map = {
    today: 'Hari Ini',
    yesterday: 'Kemarin',
    week: 'Minggu Ini',
    month: 'Bulan Ini',
    all: 'Semua',
  }
  return map[f] || f
}

function getBarHeight(revenue) {
  const max = Math.max(...(summary.value.sales_trend?.map((d) => d.revenue) || [100000]), 100000)
  const pct = Math.max(12, Math.round((revenue / max) * 100))
  return `${pct}%`
}

async function loadSummary() {
  loadingSummary.value = true
  try {
    const res = await api.get('/dashboard/summary')
    if (res.data.success) {
      summary.value = res.data.data
    }
  } catch (err) {
    toast.error('Gagal mengambil ringkasan dashboard')
  } finally {
    loadingSummary.value = false
  }
}

async function loadTransactions() {
  loadingTransactions.value = true
  try {
    const params = selectedDateFilter.value === 'all' ? {} : { date_range: selectedDateFilter.value }
    const res = await api.get('/transactions', { params })
    if (res.data.success) {
      transactions.value = res.data.data.data || res.data.data
    }
  } catch (err) {
    toast.error('Gagal mengambil riwayat transaksi')
  } finally {
    loadingTransactions.value = false
  }
}

async function loadSettings() {
  try {
    const res = await api.get('/settings/public')
    if (res.data.success) {
      storeSettings.value = res.data.data
    }
  } catch (err) {
    console.warn(err)
  }
}

function setFilter(f) {
  selectedDateFilter.value = f
  loadTransactions()
}

function openReceiptDetail(trx) {
  selectedReceiptTransaction.value = trx
  showReceiptModal.value = true
}

function exportCsv() {
  if (transactions.value.length === 0) {
    toast.warning('Tidak ada data transaksi untuk diekspor.')
    return
  }

  const headers = ['No Invoice', 'Waktu', 'Kasir', 'Pelanggan', 'Metode', 'Total']
  const rows = transactions.value.map((t) => [
    t.invoice_number,
    t.created_at,
    t.user?.name || 'Kasir',
    t.customer_name || 'Pelanggan Umum',
    t.payment_method,
    t.total_amount,
  ])

  const csvContent =
    'data:text/csv;charset=utf-8,' +
    [headers.join(','), ...rows.map((e) => e.join(','))].join('\n')

  const encodedUri = encodeURI(csvContent)
  const link = document.createElement('a')
  link.setAttribute('href', encodedUri)
  link.setAttribute('download', `laporan-pos-${new Date().toISOString().slice(0, 10)}.csv`)
  document.body.appendChild(link)
  link.click()
  document.body.removeChild(link)
  toast.success('Laporan CSV berhasil diunduh!')
}

onMounted(() => {
  loadSummary()
  loadTransactions()
  loadSettings()
})
</script>
