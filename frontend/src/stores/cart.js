import { defineStore } from 'pinia'
import { ref, computed, watch } from 'vue'
import { useToastStore } from './toast'

export const useCartStore = defineStore('cart', () => {
  const toast = useToastStore()

  // State
  const items = ref([])
  const activeDiscount = ref(0) // flat amount in Rp or percent
  const discountType = ref('nominal') // 'nominal' | 'percent'
  const taxRate = ref(0.11) // 11% PPN standard Indonesia
  const customerName = ref('')
  const notes = ref('')

  // Held transactions (Gantung transaksi / multi-tab)
  const heldTransactions = ref(
    JSON.parse(localStorage.getItem('pos_held_transactions') || '[]')
  )

  // Watch and persist held transactions
  watch(
    heldTransactions,
    (val) => {
      localStorage.setItem('pos_held_transactions', JSON.stringify(val))
    },
    { deep: true }
  )

  // Getters
  const totalItemsCount = computed(() => {
    return items.value.reduce((acc, item) => acc + item.quantity, 0)
  })

  const subtotal = computed(() => {
    return items.value.reduce((acc, item) => {
      return acc + (Number(item.product.sell_price) * item.quantity)
    }, 0)
  })

  const discountAmount = computed(() => {
    if (activeDiscount.value <= 0) return 0
    if (discountType.value === 'percent') {
      return Math.round((subtotal.value * Number(activeDiscount.value)) / 100)
    }
    return Math.min(Number(activeDiscount.value), subtotal.value)
  })

  const taxableAmount = computed(() => {
    return Math.max(0, subtotal.value - discountAmount.value)
  })

  const taxAmount = computed(() => {
    return Math.round(taxableAmount.value * taxRate.value)
  })

  const totalAmount = computed(() => {
    return taxableAmount.value + taxAmount.value
  })

  // Actions
  function addToCart(product, qty = 1) {
    // Check if product is in stock
    if (product.stock <= 0) {
      toast.error(`Stok '${product.name}' habis! Tidak dapat ditambahkan.`)
      return false
    }

    const existingIndex = items.value.findIndex((item) => item.product.id === product.id)

    if (existingIndex > -1) {
      const currentQty = items.value[existingIndex].quantity
      if (currentQty + qty > product.stock) {
        toast.warning(
          `Stok '${product.name}' tersisa ${product.stock}. Tidak dapat menambahkan lebih.`
        )
        return false
      }
      items.value[existingIndex].quantity += qty
      items.value[existingIndex].subtotal =
        items.value[existingIndex].quantity * Number(product.sell_price)
    } else {
      if (qty > product.stock) {
        toast.warning(`Stok '${product.name}' hanya tersisa ${product.stock}.`)
        return false
      }
      items.value.push({
        product: { ...product },
        quantity: qty,
        subtotal: qty * Number(product.sell_price),
      })
    }

    toast.info(`'${product.name}' masuk keranjang`, 1500)
    return true
  }

  function removeFromCart(productId) {
    const item = items.value.find((i) => i.product.id === productId)
    if (item) {
      items.value = items.value.filter((i) => i.product.id !== productId)
      toast.info(`'${item.product.name}' dihapus dari keranjang`, 1500)
    }
  }

  function updateQuantity(productId, newQty) {
    const item = items.value.find((i) => i.product.id === productId)
    if (!item) return

    if (newQty <= 0) {
      removeFromCart(productId)
      return
    }

    if (newQty > item.product.stock) {
      toast.warning(
        `Batas stok maksimum '${item.product.name}' adalah ${item.product.stock}`
      )
      item.quantity = item.product.stock
      item.subtotal = item.quantity * Number(item.product.sell_price)
      return
    }

    item.quantity = newQty
    item.subtotal = newQty * Number(item.product.sell_price)
  }

  function setDiscount(val, type = 'nominal') {
    activeDiscount.value = Number(val) || 0
    discountType.value = type
  }

  function clearCart() {
    items.value = []
    activeDiscount.value = 0
    customerName.value = ''
    notes.value = ''
  }

  /**
   * Hold Current Transaction (Gantung Transaksi / Multi-Tab)
   */
  function holdOrder(customLabel = '') {
    if (items.value.length === 0) {
      toast.warning('Keranjang kosong, tidak ada transaksi untuk digantung.')
      return false
    }

    const heldId = 'HOLD-' + Date.now().toString(36).toUpperCase()
    const label =
      customLabel.trim() ||
      customerName.value.trim() ||
      `Pesanan #${heldTransactions.value.length + 1}`

    heldTransactions.value.unshift({
      id: heldId,
      label,
      customerName: customerName.value,
      notes: notes.value,
      items: JSON.parse(JSON.stringify(items.value)),
      activeDiscount: activeDiscount.value,
      discountType: discountType.value,
      subtotal: subtotal.value,
      totalAmount: totalAmount.value,
      heldAt: new Date().toISOString(),
    })

    clearCart()
    toast.success(`Transaksi "${label}" berhasil disimpan / digantung!`)
    return true
  }

  /**
   * Restore a held transaction back into active cart
   */
  function restoreOrder(heldId) {
    const index = heldTransactions.value.findIndex((h) => h.id === heldId)
    if (index === -1) return false

    const held = heldTransactions.value[index]

    // If current cart already has items, prompt or hold it automatically
    if (items.value.length > 0) {
      // Auto hold active cart first
      holdOrder('Antrean Sebelumnya')
    }

    items.value = held.items
    activeDiscount.value = held.activeDiscount || 0
    discountType.value = held.discountType || 'nominal'
    customerName.value = held.customerName || ''
    notes.value = held.notes || ''

    // Remove from held list
    heldTransactions.value.splice(index, 1)
    toast.success(`Transaksi "${held.label}" berhasil dimuat kembali!`)
    return true
  }

  function removeHeldOrder(heldId) {
    heldTransactions.value = heldTransactions.value.filter((h) => h.id !== heldId)
    toast.info('Transaksi gantung dihapus.')
  }

  return {
    items,
    activeDiscount,
    discountType,
    taxRate,
    customerName,
    notes,
    heldTransactions,
    totalItemsCount,
    subtotal,
    discountAmount,
    taxableAmount,
    taxAmount,
    totalAmount,
    addToCart,
    removeFromCart,
    updateQuantity,
    setDiscount,
    clearCart,
    holdOrder,
    restoreOrder,
    removeHeldOrder,
  }
})
