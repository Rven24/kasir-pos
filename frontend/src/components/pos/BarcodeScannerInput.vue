<template>
  <div class="relative w-full">
    <div class="relative flex items-center">
      <div class="absolute left-3.5 text-slate-400 flex items-center pointer-events-none">
        <Barcode class="w-5 h-5 text-emerald-500" />
      </div>

      <input
        ref="barcodeInput"
        v-model="barcodeQuery"
        type="text"
        placeholder="Scan Barcode / SKU (Tekan Enter)..."
        class="w-full pl-11 pr-24 py-2.5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-xs sm:text-sm text-slate-900 dark:text-slate-100 placeholder-slate-400 font-mono-num shadow-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/50 focus:border-emerald-500 transition-all"
        @keydown.enter.prevent="handleScan"
      />

      <div class="absolute right-2.5 flex items-center gap-1.5">
        <kbd class="hidden sm:inline-block px-1.5 py-0.5 text-[10px] font-mono text-slate-400 bg-slate-100 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded">
          Enter ↵
        </kbd>
        <button
          v-if="barcodeQuery"
          @click="barcodeQuery = ''"
          class="p-1 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors"
        >
          <X class="w-3.5 h-3.5" />
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { Barcode, X } from 'lucide-vue-next'
import { useToastStore } from '@/stores/toast'

const props = defineProps({
  products: {
    type: Array,
    default: () => [],
  },
})

const emit = defineEmits(['scanned-product'])

const toast = useToastStore()
const barcodeQuery = ref('')
const barcodeInput = ref(null)

function focus() {
  barcodeInput.value?.focus()
}

defineExpose({ focus })

function handleScan() {
  const code = barcodeQuery.value.trim().toLowerCase()
  if (!code) return

  // Find product by exact barcode, SKU, or partial name
  const match = props.products.find(
    (p) =>
      (p.barcode && p.barcode.toLowerCase() === code) ||
      (p.sku && p.sku.toLowerCase() === code) ||
      p.name.toLowerCase() === code
  )

  if (match) {
    emit('scanned-product', match)
    barcodeQuery.value = ''
  } else {
    toast.error(`Barcode/SKU '${code}' tidak ditemukan di katalog produk!`)
    barcodeQuery.value = ''
  }
}
</script>
