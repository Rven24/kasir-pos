# 🛒 KasirKu POS (Posify System)

> **Smart & Fast Point-of-Sale System for Modern Retail & F&B**  
> Dibangun dengan arsitektur modern berkecepatan tinggi menggunakan **Laravel v13 RESTful API** dan **Vue.js 3 (Composition API) + Pinia + Tailwind CSS v4**.

---

## 🚀 Fitur Unggulan Sistem

### 1. ⚡ Fast Cashier & Billing Interface (Halaman Kasir Split-Screen)
- **Split-Screen UX**: Katalog Produk visual di sebelah kiri dan Panel Ringkasan Keranjang & Pembayaran di sebelah kanan.
- **Pencarian Kilat & Filter Kategori**: Filter instan tanpa refresh halaman.
- **Hardware Barcode / SKU Scanner**: Input scanner barcode USB / webcam untuk penambahan produk langsung ke keranjang belanja.
- **Pintasan Keyboard (Quick Keys)**:
  - `F2` : Membuka popup Checkout Pembayaran.
  - `F4` : Memproses & konfirmasi Bayar.
  - `F8` : Menyimpan / Menggantung transaksi aktif (*Hold Order*).
  - `ESC`: Membatalkan transaksi / menutup modal / bersihkan keranjang.
  - `/`  : Fokus otomatis ke kotak pencarian atau scanner barcode.

### 2. 🧾 Dynamic Thermal Receipt (Live Preview & Auto Print)
- Komponen `ReceiptModal.vue` dengan format cetak thermal printer **58mm** dan **80mm**.
- Terintegrasi CSS `@media print` untuk mencetak langsung ke printer thermal struk Bluetooth/USB.
- Menampilkan identitas toko, nomor invoice otomatis, detail item, subtotal, diskon, PPN 11%, nominal bayar, kembalian, dan barcode invoice.

### 3. 📱 Dynamic QRIS Payment Generator
- Generator QRIS dinamis real-time menggunakan standar EMVCo dengan nominal tagihan tepat.
- Timer countdown kedaluwarsa 5 menit.
- Fitur simulasi pembayaran scan berhasil dari pelanggan.

### 4. ⏸️ Multi-Tab Order Management (Gantung Transaksi)
- Simpan antrean pelanggan yang belum selesai memilih barang ke antrean gantung (*Held Orders*).
- Muat kembali transaksi kapan saja tanpa kehilangan daftar item.

### 5. 📦 Smart Inventory & Real-Time Stock Warning
- Master data produk: SKU, Barcode, Kategori, Harga Beli (Modal), Harga Jual, Stok, dan Batas Minimum Stok (*Low Stock Alert*).
- Peringatan otomatis (*badge* kuning & merah) jika stok menipis atau habis.
- Fitur **Stock Opname** (Penyesuaian stok fisik: Set baru, Tambah, Kurang dengan catatan log).
- Manajemen master kategori produk.

### 6. 📊 Dynamic Sales & Revenue Analytics Dashboard
- Ringkasan omzet harian, transaksi hari ini, omzet bulanan, dan total katalog SKU.
- Grafik tren penjualan 7 hari terakhir.
- Pembagian metode pembayaran (Tunai, QRIS, Transfer Bank).
- Daftar Top 5 Produk Terlaris (*Top Selling Products*).
- Riwayat transaksi lengkap dengan filter tanggal (Hari ini, Kemarin, Minggu ini, Bulan ini) & cetak ulang struk.
- Fitur Ekspor Laporan Penjualan ke file CSV / Excel.

### 7. ⚙️ Pengaturan Toko & Manajemen Pengguna
- Konfigurasi profil toko (Nama, Tagline, Alamat, Telepon, Email, Footer Struk).
- Pengaturan persentase Pajak PPN (11%) dan ID Merchant QRIS.
- Manajemen Pengguna dengan 3 tingkatan hak akses (*Role*):
  - **Owner**: Akses penuh ke seluruh sistem, laporan, dan manajemen pengguna.
  - **Admin**: Mengelola master produk, kategori, stok opname, dan laporan analitik.
  - **Kasir**: Terminal kasir cepat untuk transaksi belanja.

---

## 🔑 Kredensial Akun Bawaan (Demo)

| Role | Email | Password | Hak Akses |
|---|---|---|---|
| **Owner** | `owner@kasirku.com` | `password` | Seluruh Modul Sistem & Manajemen User |
| **Admin** | `admin@kasirku.com` | `password` | Kasir, Inventaris Produk, Laporan Analitik |
| **Kasir** | `kasir@kasirku.com` | `password` | Terminal Transaksi Kasir POS |

---

## 🛠️ Cara Menjalankan Aplikasi

### Opsi 1: Satu Klik (Windows)
Cukup jalankan file:
```bash
run.bat
```

### Opsi 2: Menjalankan Secara Manual

#### 1. Jalankan Backend (Laravel RESTful API):
```bash
cd backend
php artisan serve --port=8000
```
> *API akan aktif di `http://127.0.0.1:8000`*

#### 2. Jalankan Frontend (Vue.js 3 + Vite):
Buka terminal baru di folder proyek:
```bash
cd frontend
npm run dev
```
> *Aplikasi KasirKu POS dapat diakses di browser: `http://localhost:5173`*

---

## 📁 Struktur Direktori Proyek

```
kasir-pos/
├── backend/                  # Laravel v13 RESTful API
│   ├── app/
│   │   ├── Http/Controllers/Api/
│   │   │   ├── AuthController.php          # Login, Register, User Management
│   │   │   ├── CategoryController.php      # Master Kategori
│   │   │   ├── ProductController.php       # CRUD Produk, Alert Stok, Stock Opname
│   │   │   ├── TransactionController.php   # Checkout (DB Transaction), Invoice, Riwayat
│   │   │   ├── DashboardController.php     # Ringkasan Omzet, Grafik, Top Produk
│   │   │   └── SettingController.php       # Pengaturan Toko & Footer Struk
│   │   ├── Models/                         # Eloquent Models (User, Product, Transaction, dll)
│   │   └── Traits/ApiResponse.php          # Format JSON terstruktur (success, message, data, errors)
│   ├── database/
│   │   ├── migrations/                     # Skema Database Relasional POS
│   │   └── seeders/DatabaseSeeder.php      # Seeder realistis data ritel Indonesia & akun demo
│   └── routes/api.php                      # Endpoint API dengan proteksi Laravel Sanctum
│
├── frontend/                 # Vue.js 3 + Vite Single Page Application
│   ├── src/
│   │   ├── components/
│   │   │   ├── layout/                     # AppNavbar, AppSidebar (Collapsible & Fullscreen)
│   │   │   └── pos/                        # ProductCard, BarcodeScannerInput, PaymentModal,
│   │   │                                   # DynamicQrisModal, ReceiptModal, HeldOrdersModal
│   │   ├── stores/                         # Pinia Stores (cartStore, authStore, toastStore)
│   │   ├── views/                          # CashierView, InventoryView, DashboardView, SettingsView, LoginView
│   │   ├── services/api.js                 # Axios Interceptor dengan Bearer Token
│   │   ├── style.css                       # Tailwind CSS v4 + @media print thermal styles
│   │   └── router/index.js                 # Navigation Guards & Role-Based Access
│   └── vite.config.js                      # Vite + Tailwind plugin & API Proxy
│
└── run.bat                   # Skrip instan untuk menjalankan backend & frontend bersamaan
```
