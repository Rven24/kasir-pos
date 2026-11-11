<template>
  <div class="h-[calc(100vh-4rem)] flex flex-col lg:flex-row overflow-hidden bg-slate-100 dark:bg-slate-950">
    <!-- LEFT PANEL: Catalog, Filter, Search, Barcode (Split-Screen Left) -->
    <div class="flex-1 flex flex-col min-w-0 border-r border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50 overflow-hidden">
      <!-- Top Search & Barcode Scan Toolbar -->
      <div class="p-4 border-b border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 flex flex-col sm:flex-row items-center gap-3">
        <!-- Search Input -->
        <div class="relative flex-1 w-full">
          <Search class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none" />
          <input
            ref="searchInputRef"
            v-model="searchQuery"
            type="text"
            placeholder="Cari produk (Nama / SKU) atau tekan '/'..."
            class="w-full pl-10 pr-4 py-2.5 rounded-2xl bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 text-xs sm:text-sm text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/50 focus:border-emerald-500 transition-all"
          />
        </div>

        <!-- Barcode / SKU Hardware Scan Input -->
        <div class="w-full sm:w-72">
          <BarcodeScannerInput
            ref="barcodeScannerRef"
            :products="products"
            @scanned-product="handleScannedProduct"
          />
        </div>
      </div>

      <!-- Category Filter Pills -->
      <div class="px-4 py-3 bg-white/70 dark:bg-slate-900/70 border-b border-slate-200 dark:border-slate-800 flex items-center gap-2 overflow-x-auto no-scrollbar">
        <button
          type="button"
          @click="selectedCategoryId = 'all'"
          class="px-3.5 py-1.5 rounded-xl text-xs font-bold whitespace-nowrap transition-all"
          :class="selectedCategoryId === 'all' ? 'bg-emerald-600 text-white shadow-md shadow-emerald-600/20' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-slate-700'"
        >
          Semua ({{ products.length }})
        </button>

        <button
          v-for="cat in categories"
          :key="cat.id"
          type="button"
          @click="selectedCategoryId = cat.id"
          class="px-3.5 py-1.5 rounded-xl text-xs font-bold whitespace-nowrap transition-all"
          :class="selectedCategoryId === cat.id ? 'bg-emerald-600 text-white shadow-md shadow-emerald-600/20' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-slate-700'"
        >
          {{ cat.name }} ({{ cat.products_count || 0 }})
        </button>
      </div>

      <!-- Product Catalog Grid -->
      <div class="flex-1 overflow-y-auto p-4">
        <!-- Loading State -->
        <div v-if="loadingProducts" class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-4 gap-3">
          <div
            v-for="i in 8"
            :key="i"
            class="h-56 rounded-2xl bg-white dark:bg-slate-900 animate-pulse border border-slate-200 dark:border-slate-800 p-3 flex flex-col justify-between"
          >
            <div class="w-full aspect-square bg-slate-100 dark:bg-slate-800 rounded-xl mb-2"></div>
            <div class="h-3 bg-slate-200 dark:bg-slate-700 rounded w-3/4 mb-1"></div>
            <div class="h-4 bg-slate-200 dark:bg-slate-700 rounded w-1/2"></div>
          </div>
        </div>

        <!-- Empty Products State -->
        <div
          v-else-if="filteredProducts.length === 0"
          class="h-full flex flex-col items-center justify-center text-center p-8 text-slate-400"
        >
          <PackageX class="w-12 h-12 text-slate-300 dark:text-slate-700 mb-2" />
          <p class="font-bold text-slate-600 dark:text-slate-400">Tidak ada produk ditemukan</p>
          <p class="text-xs text-slate-400 mt-1">Coba kata kunci pencarian atau kategori lain</p>
        </div>

        <!-- Products Grid -->
        <div
          v-else
          class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-4 gap-3"
        >
          <ProductCard
            v-for="product in filteredProducts"
            :key="product.id"
            :product="product"
            @add-to-cart="cartStore.addToCart"
          />
        </div>
      </div>
    </div>

    <!-- RIGHT PANEL: Cart Summary & Fast Checkout (Split-Screen Right) -->
    <div class="w-full lg:w-[420px] xl:w-[460px] flex flex-col bg-white dark:bg-slate-900 border-l border-slate-200 dark:border-slate-800 shadow-xl z-20">
      <!-- Cart Header & Multi-Tab Buttons -->
      <div class="p-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between bg-slate-50/75 dark:bg-slate-950/40">
        <div class="flex items-center gap-2">
          <div class="w-8 h-8 rounded-xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
            <ShoppingBag class="w-4 h-4" />
          </div>
          <div>
            <h3 class="font-extrabold text-slate-900 dark:text-slate-100 text-sm">
              Keranjang Kasir
            </h3>
            <p class="text-[11px] text-slate-500">
              {{ cartStore.totalItemsCount }} item dipilih
            </p>
          </div>
        </div>

        <!-- Action Buttons: Hold Order & Held Orders Modal Trigger -->
        <div class="flex items-center gap-1.5">
          <!-- Held Orders Tab / Badge -->
          <button
            type="button"
            @click="showHeldOrdersModal = true"
            class="relative px-2.5 py-1.5 rounded-xl border border-slate-200 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-800 text-xs font-semibold text-slate-700 dark:text-slate-300 transition-colors flex items-center gap-1"
            title="Lihat Transaksi Digantung"
          >
            <Clock class="w-3.5 h-3.5 text-amber-500" />
            <span class="hidden sm:inline">Gantung</span>
            <span
              v-if="cartStore.heldTransactions.length > 0"
              class="w-4 h-4 rounded-full bg-amber-500 text-white text-[10px] font-extrabold flex items-center justify-center"
            >
              {{ cartStore.heldTransactions.length }}
            </span>
          </button>

          <!-- Hold Active Order Button (F8) -->
          <button
            type="button"
            :disabled="cartStore.items.length === 0"
            @click="cartStore.holdOrder()"
            class="p-2 rounded-xl border border-slate-200 dark:border-slate-700 hover:bg-amber-50 dark:hover:bg-amber-950/40 text-slate-600 dark:text-slate-300 hover:text-amber-600 dark:hover:text-amber-400 transition-colors disabled:opacity-40"
            title="Gantung Transaksi Ini (F8)"
          >
            <PauseCircle class="w-4 h-4" />
          </button>

          <!-- Clear Cart Button (ESC) -->
          <button
            type="button"
            :disabled="cartStore.items.length === 0"
            @click="handleClearCart"
            class="p-2 rounded-xl border border-slate-200 dark:border-slate-700 hover:bg-rose-50 dark:hover:bg-rose-950/40 text-slate-600 dark:text-slate-300 hover:text-rose-600 dark:hover:text-rose-400 transition-colors disabled:opacity-40"
            title="Bersihkan Keranjang (ESC)"
          >
            <Trash2 class="w-4 h-4" />
          </button>
        </div>
      </div>

      <!-- Customer Name Input Bar -->
      <div class="px-4 py-2 border-b border-slate-100 dark:border-slate-800/80 bg-slate-50/40 dark:bg-slate-950/20 flex items-center gap-2">
        <User class="w-3.5 h-3.5 text-slate-400" />
        <input
          v-model="cartStore.customerName"
          type="text"
          placeholder="Nama / Meja Pelanggan (Opsional)..."
          class="w-full bg-transparent text-xs text-slate-800 dark:text-slate-200 placeholder-slate-400 focus:outline-none"
        />
      </div>

      <!-- Cart Items List -->
      <div class="flex-1 overflow-y-auto p-4 space-y-2.5">
        <!-- Empty Cart Notice -->
        <div
          v-if="cartStore.items.length === 0"
          class="h-full flex flex-col items-center justify-center text-center p-6 text-slate-400"
        >
          <ShoppingCart class="w-12 h-12 text-slate-300 dark:text-slate-700 mb-2" />
          <p class="font-bold text-slate-600 dark:text-slate-400 text-sm">Keranjang Belanja Kosong</p>
          <p class="text-xs text-slate-400 mt-1 max-w-[220px]">
            Klik produk pada katalog di sebelah kiri atau scan barcode untuk menambahkan item.
          </p>
        </div>

        <!-- Item Cards -->
        <div
          v-for="item in cartStore.items"
          :key="item.product.id"
          class="p-3 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700/60 flex items-center justify-between gap-3 group hover:border-emerald-500/40 transition-colors"
        >
          <!-- Item Info -->
          <div class="min-w-0 flex-1">
            <h5 class="text-xs sm:text-sm font-bold text-slate-800 dark:text-slate-100 truncate">
              {{ item.product.name }}
            </h5>
            <div class="text-[11px] text-slate-500 font-mono-num mt-0.5">
              {{ formatRupiah(item.product.sell_price) }}
            </div>
            <div class="text-xs font-bold text-emerald-600 dark:text-emerald-400 font-mono-num mt-0.5">
              Total: {{ formatRupiah(item.subtotal) }}
            </div>
          </div>

          <!-- Quantity Stepper Controls -->
          <div class="flex items-center gap-1.5 shrink-0">
            <button
              type="button"
              @click="cartStore.updateQuantity(item.product.id, item.quantity - 1)"
              class="w-7 h-7 rounded-lg bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 text-slate-700 dark:text-slate-200 hover:bg-slate-100 flex items-center justify-center transition-colors"
            >
              <Minus class="w-3.5 h-3.5" />
            </button>

            <span class="w-8 text-center text-xs font-bold font-mono-num text-slate-800 dark:text-slate-100">
              {{ item.quantity }}
            </span>

            <button
              type="button"
              @click="cartStore.updateQuantity(item.product.id, item.quantity + 1)"
              class="w-7 h-7 rounded-lg bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 text-slate-700 dark:text-slate-200 hover:bg-slate-100 flex items-center justify-center transition-colors"
            >
              <Plus class="w-3.5 h-3.5" />
            </button>

            <!-- Delete item button -->
            <button
              type="button"
              @click="cartStore.removeFromCart(item.product.id)"
              class="p-1.5 rounded-lg text-slate-400 hover:text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-950/40 transition-colors ml-1"
              title="Hapus item"
            >
              <Trash2 class="w-3.5 h-3.5" />
            </button>
          </div>
        </div>
      </div>

      <!-- Cart Calculation & Checkout Panel -->
      <div class="p-4 border-t border-slate-200 dark:border-slate-800 bg-slate-50/75 dark:bg-slate-950/60 space-y-3">
        <!-- Subtotal, Diskon, Pajak rows -->
        <div class="space-y-1.5 text-xs text-slate-600 dark:text-slate-400">
          <div class="flex justify-between">
            <span>Subtotal</span>
            <span class="font-mono-num font-semibold text-slate-900 dark:text-slate-100">
              {{ formatRupiah(cartStore.subtotal) }}
            </span>
          </div>

          <!-- Discount Input Row -->
          <div class="flex justify-between items-center">
            <div class="flex items-center gap-1.5">
              <span>Diskon (Rp)</span>
              <button
                type="button"
                @click="showDiscountInput = !showDiscountInput"
                class="text-[10px] text-emerald-600 dark:text-emerald-400 hover:underline font-semibold"
              >
                {{ showDiscountInput ? 'Tutup' : 'Ubah' }}
              </button>
            </div>
            <span class="font-mono-num font-semibold text-emerald-600 dark:text-emerald-400">
              -{{ formatRupiah(cartStore.discountAmount) }}
            </span>
          </div>

          <div v-if="showDiscountInput" class="pt-1">
            <input
              v-model="discountInputValue"
              type="number"
              placeholder="Nominal Diskon..."
              class="w-full px-3 py-1.5 rounded-xl bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-xs font-mono-num focus:outline-none focus:ring-1 focus:ring-emerald-500"
              @input="handleDiscountChange"
            />
          </div>

          <div class="flex justify-between">
            <span>PPN (11%)</span>
            <span class="font-mono-num font-semibold text-slate-900 dark:text-slate-100">
              {{ formatRupiah(cartStore.taxAmount) }}
            </span>
          </div>
        </div>

        <!-- Total Tagihan -->
        <div class="pt-2 border-t border-slate-200 dark:border-slate-800 flex justify-between items-baseline">
          <div>
            <span class="text-xs font-bold uppercase tracking-wider text-slate-500">
              Total Pembayaran
            </span>
            <div class="text-[10px] text-slate-400">Termasuk pajak & diskon</div>
          </div>
          <div class="text-2xl sm:text-3xl font-extrabold text-emerald-600 dark:text-emerald-400 font-mono-num tracking-tight">
            {{ formatRupiah(cartStore.totalAmount) }}
          </div>
        </div>

        <!-- Big Checkout Action Button (F2) -->
        <button
          type="button"
          :disabled="cartStore.items.length === 0"
          @click="openCheckoutModal"
          class="w-full py-3.5 px-4 rounded-2xl bg-emerald-600 hover:bg-emerald-500 active:scale-[0.99] text-white font-extrabold text-sm sm:text-base shadow-xl shadow-emerald-600/30 transition-all flex items-center justify-center gap-2.5 disabled:opacity-40 disabled:cursor-not-allowed group"
        >
          <CreditCard class="w-5 h-5 transition-transform group-hover:scale-110" />
          <span>Checkout Pembayaran (F2)</span>
        </button>
      </div>
    </div>

    <!-- Modals -->
    <!-- 1. Payment Modal -->
    <PaymentModal
      :is-open="showPaymentModal"
      :subtotal="cartStore.subtotal"
      :discount="cartStore.discountAmount"
      :tax="cartStore.taxAmount"
      :total-amount="cartStore.totalAmount"
      :initial-customer-name="cartStore.customerName"
      @close="showPaymentModal = false"
      @process-payment="handleProcessPayment"
      @open-qris="handleOpenQris"
    />

    <!-- 2. Dynamic QRIS Modal -->
    <DynamicQrisModal
      :is-open="showQrisModal"
      :total-amount="cartStore.totalAmount"
      :merchant-name="storeSettings.qris_merchant_name || 'KASIRKU MART & CAFE'"
      :invoice-number="activeInvoiceNumber"
      @close="showQrisModal = false"
      @paid="handleQrisPaidSuccess"
    />

    <!-- 3. Thermal Receipt Modal -->
    <ReceiptModal
      :is-open="showReceiptModal"
      :transaction="completedTransaction"
      :cashier-name="authStore.user?.name"
      :store-info="storeSettings"
      @close="handleCloseReceipt"
    />

    <!-- 4. Held Orders Modal (Multi-tab) -->
    <HeldOrdersModal
      :is-open="showHeldOrdersModal"
      @close="showHeldOrdersModal = false"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import api from '@/services/api'
import { useCartStore } from '@/stores/cart'
import { useAuthStore } from '@/stores/auth'
import { useToastStore } from '@/stores/toast'
import ProductCard from '@/components/pos/ProductCard.vue'
import BarcodeScannerInput from '@/components/pos/BarcodeScannerInput.vue'
import PaymentModal from '@/components/pos/PaymentModal.vue'
import DynamicQrisModal from '@/components/pos/DynamicQrisModal.vue'
import ReceiptModal from '@/components/pos/ReceiptModal.vue'
import HeldOrdersModal from '@/components/pos/HeldOrdersModal.vue'
import {
  Search,
  ShoppingBag,
  ShoppingCart,
  Trash2,
  Clock,
  PauseCircle,
  CreditCard,
  Minus,
  Plus,
  PackageX,
  User,
} from 'lucide-vue-next'

const cartStore = useCartStore()
const authStore = useAuthStore()
const toast = useToastStore()

// State
const products = ref([])
const categories = ref([])
const storeSettings = ref({})
const loadingProducts = ref(true)

const searchQuery = ref('')
const selectedCategoryId = ref('all')
const showDiscountInput = ref(false)
const discountInputValue = ref('')

// Modals
const showPaymentModal = ref(false)
const showQrisModal = ref(false)
const showReceiptModal = ref(false)
const showHeldOrdersModal = ref(false)

const completedTransaction = ref(null)
const activeInvoiceNumber = ref('')

const searchInputRef = ref(null)
const barcodeScannerRef = ref(null)

// Filtered Products Catalog
const filteredProducts = computed(() => {
  let list = products.value

  if (selectedCategoryId.value !== 'all') {
    list = list.filter((p) => p.category_id === selectedCategoryId.value)
  }

  if (searchQuery.value.trim()) {
    const q = searchQuery.value.trim().toLowerCase()
    list = list.filter(
      (p) =>
        p.name.toLowerCase().includes(q) ||
        p.sku.toLowerCase().includes(q) ||
        (p.barcode && p.barcode.toLowerCase().includes(q))
    )
  }

  return list
})

function formatRupiah(amount) {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0,
    maximumFractionDigits: 0,
  }).format(Number(amount || 0))
}

// Fetch initial data
async function loadCatalog() {
  loadingProducts.value = true
  try {
    const [pRes, cRes, sRes] = await Promise.all([
      api.get('/products?all=true'),
      api.get('/categories'),
      api.get('/settings/public'),
    ])

    if (pRes.data.success) products.value = pRes.data.data
    if (cRes.data.success) categories.value = cRes.data.data
    if (sRes.data.success) storeSettings.value = sRes.data.data
  } catch (err) {
    console.error('Failed to load POS catalog:', err)
    toast.error('Gagal memuat katalog produk. Periksa koneksi backend.')
  } finally {
    loadingProducts.value = false
  }
}

function handleScannedProduct(product) {
  cartStore.addToCart(product)
}

function handleDiscountChange() {
  cartStore.setDiscount(discountInputValue.value, 'nominal')
}

function handleClearCart() {
  if (confirm('Bersihkan seluruh item di keranjang?')) {
    cartStore.clearCart()
    discountInputValue.value = ''
    showDiscountInput.value = false
  }
}

function openCheckoutModal() {
  if (cartStore.items.length === 0) return
  showPaymentModal.value = true
}

// Process checkout transaction via API
async function handleProcessPayment(paymentData) {
  try {
    const payload = {
      items: cartStore.items.map((i) => ({
        product_id: i.product.id,
        quantity: i.quantity,
      })),
      payment_method: paymentData.payment_method,
      pay_amount: paymentData.pay_amount,
      discount: cartStore.discountAmount,
      customer_name: paymentData.customer_name || cartStore.customerName,
      notes: cartStore.notes,
    }

    const response = await api.post('/transactions/checkout', payload)

    if (response.data.success) {
      completedTransaction.value = response.data.data
      toast.success('Transaksi berhasil diselesaikan!')

      // Update local catalog stock immediately
      payload.items.forEach((item) => {
        const prod = products.value.find((p) => p.id === item.product_id)
        if (prod) {
          prod.stock = Math.max(0, prod.stock - item.quantity)
        }
      })

      // Close payment modal & show thermal receipt modal
      showPaymentModal.value = false
      showQrisModal.value = false
      cartStore.clearCart()
      discountInputValue.value = ''
      showReceiptModal.value = true
    }
  } catch (err) {
    const errorMsg =
      err.response?.data?.message || 'Checkout gagal diproses oleh server.'
    toast.error(errorMsg)
  }
}

// QRIS Flow
function handleOpenQris(data) {
  showPaymentModal.value = false
  showQrisModal.value = true
}

function handleQrisPaidSuccess() {
  handleProcessPayment({
    payment_method: 'qris',
    pay_amount: cartStore.totalAmount,
    customer_name: cartStore.customerName,
  })
}

function handleCloseReceipt() {
  showReceiptModal.value = false
  completedTransaction.value = null
  // Refocus search or scan
  barcodeScannerRef.value?.focus()
}

// Global Keyboard Shortcuts (F2 = Checkout, F4 = Bayar, F8 = Hold, ESC = Cancel/Close)
function handleGlobalKeydown(e) {
  // Ignore if user is inside an input modal with its own key handling
  if (e.key === 'F2') {
    e.preventDefault()
    if (!showPaymentModal.value && !showReceiptModal.value) {
      openCheckoutModal()
    }
  } else if (e.key === 'F8') {
    e.preventDefault()
    cartStore.holdOrder()
  } else if (e.key === 'Escape') {
    if (showReceiptModal.value) {
      handleCloseReceipt()
    } else if (showQrisModal.value) {
      showQrisModal.value = false
      showPaymentModal.value = true
    } else if (showPaymentModal.value) {
      showPaymentModal.value = false
    } else if (showHeldOrdersModal.value) {
      showHeldOrdersModal.value = false
    }
  } else if (e.key === '/' && document.activeElement.tagName !== 'INPUT') {
    e.preventDefault()
    searchInputRef.value?.focus()
  }
}

onMounted(() => {
  loadCatalog()
  window.addEventListener('keydown', handleGlobalKeydown)
})

onUnmounted(() => {
  window.removeEventListener('keydown', handleGlobalKeydown)
})
</script>

<style scoped>
.no-scrollbar::-webkit-scrollbar {
  display: none;
}
.no-scrollbar {
  -ms-overflow-style: none;
  scrollbar-width: none;
}
</style>
