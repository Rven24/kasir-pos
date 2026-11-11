<template>
  <div
    class="group relative bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl overflow-hidden p-3 transition-all duration-200 hover:shadow-lg hover:border-emerald-500/40 hover:-translate-y-0.5 flex flex-col justify-between cursor-pointer select-none"
    :class="{
      'opacity-50 pointer-events-none grayscale': isOutOfStock,
      'ring-1 ring-amber-500/40': isLowStock && !isOutOfStock,
    }"
    @click="handleClick"
  >
    <!-- Top Row: Category & Stock Status -->
    <div class="flex items-center justify-between gap-1 mb-2">
      <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 truncate max-w-[120px]">
        {{ product.category?.name || 'Umum' }}
      </span>

      <!-- Stock Status Badge -->
      <span
        v-if="isOutOfStock"
        class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-700 dark:bg-rose-950/70 dark:text-rose-400"
      >
        Habis
      </span>
      <span
        v-else-if="isLowStock"
        class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-700 dark:bg-amber-950/70 dark:text-amber-400 flex items-center gap-1"
        title="Peringatan Stok Menipis"
      >
        <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
        Sisa {{ product.stock }}
      </span>
      <span
        v-else
        class="text-[10px] font-medium text-slate-400"
      >
        Stok {{ product.stock }}
      </span>
    </div>

    <!-- Product Image -->
    <div class="relative w-full aspect-square rounded-xl overflow-hidden bg-slate-100 dark:bg-slate-800 mb-2.5 flex items-center justify-center">
      <img
        v-if="product.image"
        :src="product.image"
        :alt="product.name"
        class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105"
        loading="lazy"
        @error="handleImgError"
      />
      <div v-else class="text-slate-300 dark:text-slate-700">
        <Package class="w-10 h-10" />
      </div>

      <!-- Quick Add Hover Overlay -->
      <div
        v-if="!isOutOfStock"
        class="absolute inset-0 bg-emerald-600/20 backdrop-blur-[2px] opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center"
      >
        <span class="w-10 h-10 rounded-full bg-emerald-600 text-white shadow-lg flex items-center justify-center transform scale-75 group-hover:scale-100 transition-transform">
          <Plus class="w-6 h-6 stroke-[2.5]" />
        </span>
      </div>
    </div>

    <!-- Product Details -->
    <div>
      <h4 class="font-bold text-slate-800 dark:text-slate-100 text-xs sm:text-sm line-clamp-2 leading-snug group-hover:text-emerald-600 dark:group-hover:text-emerald-400 transition-colors">
        {{ product.name }}
      </h4>
      <div class="text-[10px] font-mono text-slate-400 mt-0.5 truncate">
        SKU: {{ product.sku }}
      </div>
    </div>

    <!-- Bottom Row: Price & Add button -->
    <div class="mt-3 pt-2 border-t border-slate-100 dark:border-slate-800/80 flex items-center justify-between">
      <div class="font-extrabold text-sm sm:text-base text-emerald-600 dark:text-emerald-400 font-mono-num">
        {{ formatRupiah(product.sell_price) }}
      </div>

      <button
        type="button"
        :disabled="isOutOfStock"
        class="w-7 h-7 rounded-lg bg-slate-100 dark:bg-slate-800 hover:bg-emerald-600 dark:hover:bg-emerald-600 text-slate-700 dark:text-slate-300 hover:text-white flex items-center justify-center transition-colors shadow-sm"
        title="Tambah ke Keranjang"
      >
        <Plus class="w-4 h-4" />
      </button>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { Plus, Package } from 'lucide-vue-next'

const props = defineProps({
  product: {
    type: Object,
    required: true,
  },
})

const emit = defineEmits(['add-to-cart'])

const isOutOfStock = computed(() => Number(props.product.stock) <= 0)
const isLowStock = computed(() => {
  const stock = Number(props.product.stock)
  const min = Number(props.product.min_stock_alert || 5)
  return stock > 0 && stock <= min
})

function formatRupiah(amount) {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0,
    maximumFractionDigits: 0,
  }).format(Number(amount || 0))
}

function handleImgError(e) {
  e.target.style.display = 'none'
}

function handleClick() {
  if (!isOutOfStock.value) {
    emit('add-to-cart', props.product)
  }
}
</script>
