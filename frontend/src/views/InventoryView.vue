<template>
  <div class="p-4 sm:p-6 space-y-6 max-w-7xl mx-auto">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 dark:text-slate-100 tracking-tight flex items-center gap-2.5">
          <Package class="w-6 h-6 text-emerald-500" />
          Manajemen Inventaris & Produk
        </h1>
        <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
          Kelola master data barang, harga beli/jual, stok opname, dan peringatan batas minimum.
        </p>
      </div>

      <div class="flex items-center gap-2">
        <button
          @click="openCategoryModal"
          class="px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-800 hover:bg-slate-100 dark:hover:bg-slate-800 text-xs font-bold text-slate-700 dark:text-slate-300 transition-colors flex items-center gap-2"
        >
          <Layers class="w-4 h-4 text-emerald-500" />
          <span>Kategori</span>
        </button>

        <button
          @click="openAddProductModal"
          class="px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 active:scale-95 text-white text-xs font-extrabold shadow-lg shadow-emerald-600/25 transition-all flex items-center gap-2"
        >
          <Plus class="w-4 h-4" />
          <span>Tambah Produk</span>
        </button>
      </div>
    </div>

    <!-- Low Stock Alert Banner (If Any) -->
    <div
      v-if="lowStockItems.length > 0"
      class="p-4 rounded-2xl bg-amber-500/10 border border-amber-500/30 flex items-start sm:items-center justify-between gap-3 flex-wrap"
    >
      <div class="flex items-center gap-3">
        <div class="w-9 h-9 rounded-xl bg-amber-500 text-white flex items-center justify-center shrink-0">
          <AlertTriangle class="w-5 h-5" />
        </div>
        <div>
          <div class="text-xs sm:text-sm font-extrabold text-amber-800 dark:text-amber-300">
            Perhatian: {{ lowStockItems.length }} Produk Memiliki Stok Menipis!
          </div>
          <div class="text-[11px] sm:text-xs text-amber-700/80 dark:text-amber-400/80">
            Segera lakukan restock atau penyesuaian stok opname agar transaksi kasir tidak terhambat.
          </div>
        </div>
      </div>

      <button
        @click="filterOnlyLowStock = !filterOnlyLowStock"
        class="px-3 py-1.5 rounded-xl bg-amber-500 hover:bg-amber-400 text-white text-xs font-bold transition-colors ml-auto sm:ml-0"
      >
        {{ filterOnlyLowStock ? 'Tampilkan Semua Produk' : 'Filter Produk Menipis' }}
      </button>
    </div>

    <!-- Filter & Search Toolbar -->
    <div class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 flex flex-col md:flex-row items-center gap-3">
      <!-- Search Input -->
      <div class="relative flex-1 w-full">
        <Search class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none" />
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Cari nama barang, SKU, atau Barcode..."
          class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 text-xs sm:text-sm text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/50"
        />
      </div>

      <!-- Category Filter -->
      <div class="w-full md:w-56">
        <select
          v-model="selectedCategory"
          class="w-full px-3 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 text-xs sm:text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-emerald-500/50"
        >
          <option value="all">Semua Kategori</option>
          <option v-for="cat in categories" :key="cat.id" :value="cat.id">
            {{ cat.name }}
          </option>
        </select>
      </div>

      <!-- Quick Low Stock Toggle -->
      <button
        type="button"
        @click="filterOnlyLowStock = !filterOnlyLowStock"
        class="w-full md:w-auto px-4 py-2.5 rounded-xl border text-xs font-bold transition-all whitespace-nowrap flex items-center justify-center gap-2"
        :class="filterOnlyLowStock ? 'bg-amber-500 border-amber-400 text-white' : 'border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800'"
      >
        <AlertTriangle class="w-4 h-4" />
        <span>Stok Tipis Saja</span>
      </button>
    </div>

    <!-- Products Table -->
    <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 overflow-hidden shadow-sm">
      <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="border-b border-slate-200 dark:border-slate-800 bg-slate-50/80 dark:bg-slate-950/60 text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
              <th class="py-3.5 px-4">Produk</th>
              <th class="py-3.5 px-4">Kategori</th>
              <th class="py-3.5 px-4 font-mono">SKU / Barcode</th>
              <th class="py-3.5 px-4">Harga Beli</th>
              <th class="py-3.5 px-4">Harga Jual</th>
              <th class="py-3.5 px-4 text-center">Stok</th>
              <th class="py-3.5 px-4 text-center">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-xs sm:text-sm">
            <!-- Loading -->
            <tr v-if="loading">
              <td colspan="7" class="py-12 text-center text-slate-400">
                <div class="animate-spin w-6 h-6 border-2 border-emerald-500 border-t-transparent rounded-full mx-auto mb-2"></div>
                <span>Memuat data produk...</span>
              </td>
            </tr>

            <!-- Empty -->
            <tr v-else-if="filteredProducts.length === 0">
              <td colspan="7" class="py-12 text-center text-slate-400">
                <PackageX class="w-8 h-8 mx-auto text-slate-300 dark:text-slate-700 mb-2" />
                <span>Tidak ada data produk yang sesuai.</span>
              </td>
            </tr>

            <!-- Rows -->
            <tr
              v-for="product in filteredProducts"
              :key="product.id"
              class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition-colors"
            >
              <!-- Name & Image -->
              <td class="py-3 px-4">
                <div class="flex items-center gap-3">
                  <div class="w-10 h-10 rounded-xl overflow-hidden bg-slate-100 dark:bg-slate-800 shrink-0 border border-slate-200 dark:border-slate-700">
                    <img
                      v-if="product.image"
                      :src="product.image"
                      :alt="product.name"
                      class="w-full h-full object-cover"
                    />
                    <div v-else class="w-full h-full flex items-center justify-center text-slate-300 dark:text-slate-600">
                      <Package class="w-5 h-5" />
                    </div>
                  </div>
                  <div class="min-w-0">
                    <div class="font-bold text-slate-900 dark:text-slate-100 truncate max-w-xs">
                      {{ product.name }}
                    </div>
                    <div class="text-[11px] text-slate-400 truncate max-w-xs">
                      {{ product.description || 'Tidak ada deskripsi' }}
                    </div>
                  </div>
                </div>
              </td>

              <!-- Category -->
              <td class="py-3 px-4">
                <span class="inline-block px-2.5 py-1 rounded-full text-[11px] font-semibold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300">
                  {{ product.category?.name || '-' }}
                </span>
              </td>

              <!-- SKU / Barcode -->
              <td class="py-3 px-4 font-mono-num text-xs text-slate-600 dark:text-slate-400">
                <div class="font-semibold">{{ product.sku }}</div>
                <div class="text-[10px] text-slate-400">{{ product.barcode || '-' }}</div>
              </td>

              <!-- Buy Price -->
              <td class="py-3 px-4 font-mono-num text-slate-600 dark:text-slate-400">
                {{ formatRupiah(product.buy_price) }}
              </td>

              <!-- Sell Price -->
              <td class="py-3 px-4 font-mono-num font-bold text-emerald-600 dark:text-emerald-400">
                {{ formatRupiah(product.sell_price) }}
              </td>

              <!-- Stock & Warning Badge -->
              <td class="py-3 px-4 text-center">
                <div class="inline-flex flex-col items-center">
                  <span
                    class="px-2.5 py-0.5 rounded-full text-xs font-bold font-mono-num"
                    :class="{
                      'bg-rose-100 text-rose-700 dark:bg-rose-950/70 dark:text-rose-400': product.stock <= 0,
                      'bg-amber-100 text-amber-700 dark:bg-amber-950/70 dark:text-amber-400': product.stock > 0 && product.stock <= (product.min_stock_alert || 5),
                      'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/70 dark:text-emerald-400': product.stock > (product.min_stock_alert || 5),
                    }"
                  >
                    {{ product.stock }}
                  </span>
                  <span class="text-[10px] text-slate-400 mt-0.5">Min: {{ product.min_stock_alert || 5 }}</span>
                </div>
              </td>

              <!-- Actions -->
              <td class="py-3 px-4 text-center">
                <div class="flex items-center justify-center gap-1">
                  <!-- Stock Opname Button -->
                  <button
                    @click="openStockAdjustModal(product)"
                    class="p-1.5 rounded-lg text-slate-500 hover:text-amber-600 hover:bg-amber-50 dark:hover:bg-amber-950/40 transition-colors"
                    title="Penyesuaian Stok (Opname)"
                  >
                    <ArrowDownUp class="w-4 h-4" />
                  </button>

                  <!-- Edit Button -->
                  <button
                    @click="openEditProductModal(product)"
                    class="p-1.5 rounded-lg text-slate-500 hover:text-emerald-600 hover:bg-emerald-50 dark:hover:bg-emerald-950/40 transition-colors"
                    title="Edit Produk"
                  >
                    <Edit3 class="w-4 h-4" />
                  </button>

                  <!-- Delete Button -->
                  <button
                    @click="handleDeleteProduct(product)"
                    class="p-1.5 rounded-lg text-slate-500 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/40 transition-colors"
                    title="Hapus Produk"
                  >
                    <Trash2 class="w-4 h-4" />
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- MODAL 1: Add / Edit Product Form -->
    <div
      v-if="showProductModal"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/75 backdrop-blur-sm"
    >
      <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl shadow-2xl max-w-xl w-full max-h-[90vh] flex flex-col overflow-hidden animate-in zoom-in-95 duration-150">
        <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 flex justify-between items-center bg-slate-50 dark:bg-slate-950/50">
          <h3 class="font-extrabold text-slate-900 dark:text-slate-100 text-base">
            {{ isEditingProduct ? 'Edit Master Produk' : 'Tambah Produk Baru' }}
          </h3>
          <button @click="showProductModal = false" class="text-slate-400 hover:text-slate-600">
            <X class="w-5 h-5" />
          </button>
        </div>

        <form @submit.prevent="handleSaveProduct" class="p-6 overflow-y-auto space-y-4 flex-1">
          <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
              Nama Produk *
            </label>
            <input
              v-model="productForm.name"
              type="text"
              required
              placeholder="Contoh: Kopi Susu Aren 250ml"
              class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-xs sm:text-sm focus:outline-none focus:ring-1 focus:ring-emerald-500"
            />
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                Kategori *
              </label>
              <select
                v-model="productForm.category_id"
                required
                class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-xs sm:text-sm focus:outline-none focus:ring-1 focus:ring-emerald-500"
              >
                <option value="" disabled>Pilih Kategori</option>
                <option v-for="c in categories" :key="c.id" :value="c.id">
                  {{ c.name }}
                </option>
              </select>
            </div>

            <div>
              <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                SKU Produk *
              </label>
              <input
                v-model="productForm.sku"
                type="text"
                required
                placeholder="Contoh: MNM-005"
                class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-xs sm:text-sm font-mono-num focus:outline-none focus:ring-1 focus:ring-emerald-500"
              />
            </div>
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                Barcode
              </label>
              <input
                v-model="productForm.barcode"
                type="text"
                placeholder="Scan / ketik barcode..."
                class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-xs sm:text-sm font-mono-num focus:outline-none focus:ring-1 focus:ring-emerald-500"
              />
            </div>

            <div>
              <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                Batas Minimum Stok (Alert)
              </label>
              <input
                v-model.number="productForm.min_stock_alert"
                type="number"
                min="0"
                placeholder="5"
                class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-xs sm:text-sm font-mono-num focus:outline-none focus:ring-1 focus:ring-emerald-500"
              />
            </div>
          </div>

          <div class="grid grid-cols-3 gap-3">
            <div>
              <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                Harga Beli (Modal) *
              </label>
              <input
                v-model.number="productForm.buy_price"
                type="number"
                step="500"
                min="0"
                required
                placeholder="0"
                class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-xs sm:text-sm font-mono-num focus:outline-none focus:ring-1 focus:ring-emerald-500"
              />
            </div>

            <div>
              <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                Harga Jual *
              </label>
              <input
                v-model.number="productForm.sell_price"
                type="number"
                step="500"
                min="0"
                required
                placeholder="0"
                class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-xs sm:text-sm font-mono-num font-bold text-emerald-600 focus:outline-none focus:ring-1 focus:ring-emerald-500"
              />
            </div>

            <div>
              <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                Stok Awal *
              </label>
              <input
                v-model.number="productForm.stock"
                type="number"
                min="0"
                required
                placeholder="0"
                class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-xs sm:text-sm font-mono-num focus:outline-none focus:ring-1 focus:ring-emerald-500"
              />
            </div>
          </div>

          <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
              URL Foto Produk (Opsional)
            </label>
            <input
              v-model="productForm.image"
              type="url"
              placeholder="https://images.unsplash.com/..."
              class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-xs sm:text-sm focus:outline-none focus:ring-1 focus:ring-emerald-500"
            />
          </div>

          <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
              Deskripsi Singkat
            </label>
            <textarea
              v-model="productForm.description"
              rows="2"
              placeholder="Catatan bahan, rasa, atau spesifikasi barang..."
              class="w-full px-3.5 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-xs sm:text-sm focus:outline-none focus:ring-1 focus:ring-emerald-500"
            ></textarea>
          </div>

          <div class="pt-4 border-t border-slate-200 dark:border-slate-800 flex justify-end gap-2">
            <button
              type="button"
              @click="showProductModal = false"
              class="px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 text-xs font-semibold hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors"
            >
              Batal
            </button>
            <button
              type="submit"
              class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-extrabold shadow-lg shadow-emerald-600/25 transition-all"
            >
              Simpan Produk
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- MODAL 2: Stock Adjustment (Stock Opname) -->
    <div
      v-if="showStockModal"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/75 backdrop-blur-sm"
    >
      <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl shadow-2xl max-w-sm w-full p-6 animate-in zoom-in-95 duration-150">
        <div class="flex justify-between items-center mb-4">
          <h3 class="font-extrabold text-slate-900 dark:text-slate-100 text-base flex items-center gap-2">
            <ArrowDownUp class="w-5 h-5 text-amber-500" />
            Penyesuaian Stok (Opname)
          </h3>
          <button @click="showStockModal = false" class="text-slate-400 hover:text-slate-600">
            <X class="w-5 h-5" />
          </button>
        </div>

        <div class="p-3 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 mb-4">
          <div class="font-bold text-xs sm:text-sm text-slate-800 dark:text-slate-200 truncate">
            {{ selectedProductForStock?.name }}
          </div>
          <div class="text-xs text-slate-500 mt-0.5">
            Stok Saat Ini: <span class="font-bold font-mono text-emerald-600">{{ selectedProductForStock?.stock }}</span> unit
          </div>
        </div>

        <form @submit.prevent="handleSaveStockAdjustment" class="space-y-3.5">
          <div>
            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">
              Jenis Penyesuaian
            </label>
            <div class="grid grid-cols-3 gap-1.5">
              <button
                type="button"
                @click="stockForm.type = 'set'"
                class="py-2 text-xs font-bold rounded-xl border transition-all"
                :class="stockForm.type === 'set' ? 'bg-amber-500 text-white border-amber-400' : 'bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700 text-slate-600'"
              >
                Set Baru
              </button>
              <button
                type="button"
                @click="stockForm.type = 'add'"
                class="py-2 text-xs font-bold rounded-xl border transition-all"
                :class="stockForm.type === 'add' ? 'bg-emerald-600 text-white border-emerald-500' : 'bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700 text-slate-600'"
              >
                + Tambah
              </button>
              <button
                type="button"
                @click="stockForm.type = 'sub'"
                class="py-2 text-xs font-bold rounded-xl border transition-all"
                :class="stockForm.type === 'sub' ? 'bg-rose-600 text-white border-rose-500' : 'bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700 text-slate-600'"
              >
                - Kurangi
              </button>
            </div>
          </div>

          <div>
            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">
              Jumlah (Unit)
            </label>
            <input
              v-model.number="stockForm.quantity"
              type="number"
              min="1"
              required
              class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-sm font-mono-num font-bold focus:outline-none focus:ring-1 focus:ring-amber-500"
            />
          </div>

          <div>
            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">
              Catatan Opname
            </label>
            <input
              v-model="stockForm.notes"
              type="text"
              placeholder="Contoh: Barang rusak, opname fisik..."
              class="w-full px-3.5 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-xs focus:outline-none focus:ring-1 focus:ring-amber-500"
            />
          </div>

          <div class="pt-3 border-t border-slate-200 dark:border-slate-800 flex justify-end gap-2">
            <button
              type="button"
              @click="showStockModal = false"
              class="px-4 py-2 rounded-xl border border-slate-300 dark:border-slate-700 text-xs font-semibold hover:bg-slate-100 transition-colors"
            >
              Batal
            </button>
            <button
              type="submit"
              class="px-4 py-2 rounded-xl bg-amber-500 hover:bg-amber-400 text-white text-xs font-extrabold shadow-md transition-all"
            >
              Simpan Stok
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- MODAL 3: Category Management -->
    <div
      v-if="showCategoryModal"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/75 backdrop-blur-sm"
    >
      <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl shadow-2xl max-w-md w-full p-6 animate-in zoom-in-95 duration-150">
        <div class="flex justify-between items-center mb-4">
          <h3 class="font-extrabold text-slate-900 dark:text-slate-100 text-base flex items-center gap-2">
            <Layers class="w-5 h-5 text-emerald-500" />
            Kelola Kategori Produk
          </h3>
          <button @click="showCategoryModal = false" class="text-slate-400 hover:text-slate-600">
            <X class="w-5 h-5" />
          </button>
        </div>

        <!-- Add Category Input -->
        <div class="flex gap-2 mb-4">
          <input
            v-model="newCategoryName"
            type="text"
            placeholder="Nama kategori baru..."
            class="flex-1 px-3.5 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-xs focus:outline-none focus:ring-1 focus:ring-emerald-500"
            @keydown.enter.prevent="handleAddCategory"
          />
          <button
            @click="handleAddCategory"
            class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold transition-all shrink-0"
          >
            Tambah
          </button>
        </div>

        <!-- Category List -->
        <div class="space-y-2 max-h-60 overflow-y-auto pr-1">
          <div
            v-for="cat in categories"
            :key="cat.id"
            class="p-2.5 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/40 flex justify-between items-center text-xs"
          >
            <div>
              <span class="font-bold text-slate-800 dark:text-slate-200">{{ cat.name }}</span>
              <span class="text-[10px] text-slate-400 ml-2">({{ cat.products_count || 0 }} produk)</span>
            </div>
            <button
              v-if="cat.products_count === 0"
              @click="handleDeleteCategory(cat.id)"
              class="text-slate-400 hover:text-rose-500 p-1"
              title="Hapus Kategori"
            >
              <Trash2 class="w-3.5 h-3.5" />
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import api from '@/services/api'
import { useToastStore } from '@/stores/toast'
import {
  Package,
  Plus,
  Layers,
  AlertTriangle,
  Search,
  PackageX,
  ArrowDownUp,
  Edit3,
  Trash2,
  X,
} from 'lucide-vue-next'

const toast = useToastStore()

// State
const products = ref([])
const categories = ref([])
const loading = ref(true)

const searchQuery = ref('')
const selectedCategory = ref('all')
const filterOnlyLowStock = ref(false)

// Modals
const showProductModal = ref(false)
const isEditingProduct = ref(false)
const editingProductId = ref(null)

const showStockModal = ref(false)
const selectedProductForStock = ref(null)

const showCategoryModal = ref(false)
const newCategoryName = ref('')

const productForm = ref({
  category_id: '',
  name: '',
  sku: '',
  barcode: '',
  buy_price: 0,
  sell_price: 0,
  stock: 0,
  min_stock_alert: 5,
  image: '',
  description: '',
})

const stockForm = ref({
  type: 'add',
  quantity: 1,
  notes: '',
})

const lowStockItems = computed(() => {
  return products.value.filter((p) => {
    const s = Number(p.stock)
    const m = Number(p.min_stock_alert || 5)
    return s <= m
  })
})

const filteredProducts = computed(() => {
  let list = products.value

  if (filterOnlyLowStock.value) {
    list = list.filter((p) => Number(p.stock) <= Number(p.min_stock_alert || 5))
  }

  if (selectedCategory.value !== 'all') {
    list = list.filter((p) => p.category_id === Number(selectedCategory.value))
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

async function loadData() {
  loading.value = true
  try {
    const [pRes, cRes] = await Promise.all([
      api.get('/products?all=true'),
      api.get('/categories'),
    ])
    if (pRes.data.success) products.value = pRes.data.data
    if (cRes.data.success) categories.value = cRes.data.data
  } catch (err) {
    toast.error('Gagal mengambil data produk')
  } finally {
    loading.value = false
  }
}

function openAddProductModal() {
  isEditingProduct.value = false
  editingProductId.value = null
  productForm.value = {
    category_id: categories.value[0]?.id || '',
    name: '',
    sku: 'SKU-' + Date.now().toString(36).toUpperCase(),
    barcode: '',
    buy_price: 0,
    sell_price: 0,
    stock: 10,
    min_stock_alert: 5,
    image: '',
    description: '',
  }
  showProductModal.value = true
}

function openEditProductModal(product) {
  isEditingProduct.value = true
  editingProductId.value = product.id
  productForm.value = {
    category_id: product.category_id,
    name: product.name,
    sku: product.sku,
    barcode: product.barcode || '',
    buy_price: Number(product.buy_price),
    sell_price: Number(product.sell_price),
    stock: product.stock,
    min_stock_alert: product.min_stock_alert || 5,
    image: product.image || '',
    description: product.description || '',
  }
  showProductModal.value = true
}

async function handleSaveProduct() {
  try {
    if (isEditingProduct.value) {
      const res = await api.put(`/products/${editingProductId.value}`, productForm.value)
      if (res.data.success) {
        toast.success('Produk berhasil diperbarui!')
        showProductModal.value = false
        loadData()
      }
    } else {
      const res = await api.post('/products', productForm.value)
      if (res.data.success) {
        toast.success('Produk baru berhasil ditambahkan!')
        showProductModal.value = false
        loadData()
      }
    }
  } catch (err) {
    const msg = err.response?.data?.message || 'Gagal menyimpan produk.'
    toast.error(msg)
  }
}

async function handleDeleteProduct(product) {
  if (confirm(`Apakah Anda yakin ingin menghapus produk '${product.name}'?`)) {
    try {
      const res = await api.delete(`/products/${product.id}`)
      if (res.data.success) {
        toast.success('Produk berhasil dihapus')
        loadData()
      }
    } catch (err) {
      toast.error('Gagal menghapus produk')
    }
  }
}

// Stock Adjustment
function openStockAdjustModal(product) {
  selectedProductForStock.value = product
  stockForm.value = {
    type: 'add',
    quantity: 5,
    notes: 'Restock harian',
  }
  showStockModal.value = true
}

async function handleSaveStockAdjustment() {
  try {
    const res = await api.post(
      `/products/${selectedProductForStock.value.id}/adjust-stock`,
      stockForm.value
    )
    if (res.data.success) {
      toast.success('Stok berhasil diperbarui!')
      showStockModal.value = false
      loadData()
    }
  } catch (err) {
    const msg = err.response?.data?.message || 'Gagal mengubah stok'
    toast.error(msg)
  }
}

// Category modal
function openCategoryModal() {
  newCategoryName.value = ''
  showCategoryModal.value = true
}

async function handleAddCategory() {
  if (!newCategoryName.value.trim()) return
  try {
    const res = await api.post('/categories', { name: newCategoryName.value.trim() })
    if (res.data.success) {
      toast.success('Kategori berhasil ditambahkan')
      newCategoryName.value = ''
      loadData()
    }
  } catch (err) {
    toast.error('Gagal menambah kategori')
  }
}

async function handleDeleteCategory(id) {
  try {
    const res = await api.delete(`/categories/${id}`)
    if (res.data.success) {
      toast.success('Kategori berhasil dihapus')
      loadData()
    }
  } catch (err) {
    const msg = err.response?.data?.message || 'Gagal menghapus kategori'
    toast.error(msg)
  }
}

onMounted(() => {
  loadData()
})
</script>
