<template>
  <div class="p-4 sm:p-6 space-y-6 max-w-5xl mx-auto">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 dark:text-slate-100 tracking-tight flex items-center gap-2.5">
          <Sliders class="w-6 h-6 text-emerald-500" />
          Pengaturan Toko & Pengguna
        </h1>
        <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
          Konfigurasi identitas toko, format struk printer thermal, dan hak akses staf kasir.
        </p>
      </div>

      <button
        @click="handleSaveSettings"
        :disabled="isSaving"
        class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 active:scale-95 text-white text-xs font-extrabold shadow-lg shadow-emerald-600/25 transition-all flex items-center gap-2 disabled:opacity-50"
      >
        <Save class="w-4 h-4" />
        <span>{{ isSaving ? 'Menyimpan...' : 'Simpan Perubahan' }}</span>
      </button>
    </div>

    <!-- Tabs: Store Settings vs User Management -->
    <div class="flex items-center gap-2 border-b border-slate-200 dark:border-slate-800 pb-3">
      <button
        type="button"
        @click="activeTab = 'store'"
        class="px-4 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-2"
        :class="activeTab === 'store' ? 'bg-emerald-600 text-white shadow-md' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800'"
      >
        <Store class="w-4 h-4" />
        <span>Identitas & Struk Toko</span>
      </button>

      <button
        type="button"
        @click="activeTab = 'users'"
        class="px-4 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-2"
        :class="activeTab === 'users' ? 'bg-emerald-600 text-white shadow-md' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800'"
      >
        <Users class="w-4 h-4" />
        <span>Manajemen Pengguna ({{ users.length }})</span>
      </button>
    </div>

    <!-- TAB 1: STORE & RECEIPT SETTINGS -->
    <div v-if="activeTab === 'store'" class="space-y-6">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Store Identity Card -->
        <div class="p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm space-y-4">
          <h3 class="font-bold text-slate-900 dark:text-slate-100 text-sm flex items-center gap-2">
            <Store class="w-4 h-4 text-emerald-500" />
            Identitas Toko & Profil
          </h3>

          <div>
            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">
              Nama Toko / Usaha
            </label>
            <input
              v-model="settingsForm.store_name"
              type="text"
              class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-xs sm:text-sm focus:outline-none focus:ring-1 focus:ring-emerald-500"
            />
          </div>

          <div>
            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">
              Tagline / Sub-headline
            </label>
            <input
              v-model="settingsForm.store_tagline"
              type="text"
              class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-xs sm:text-sm focus:outline-none focus:ring-1 focus:ring-emerald-500"
            />
          </div>

          <div>
            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">
              Alamat Toko (Tampil di Struk)
            </label>
            <textarea
              v-model="settingsForm.store_address"
              rows="2"
              class="w-full px-3.5 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-xs sm:text-sm focus:outline-none focus:ring-1 focus:ring-emerald-500"
            ></textarea>
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">
                No. Telepon / WhatsApp
              </label>
              <input
                v-model="settingsForm.store_phone"
                type="text"
                class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-xs sm:text-sm focus:outline-none focus:ring-1 focus:ring-emerald-500"
              />
            </div>
            <div>
              <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">
                Pajak PPN (%)
              </label>
              <input
                v-model="settingsForm.tax_percentage"
                type="number"
                class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-xs sm:text-sm font-mono-num focus:outline-none focus:ring-1 focus:ring-emerald-500"
              />
            </div>
          </div>
        </div>

        <!-- Receipt & QRIS Settings Card -->
        <div class="p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm space-y-4">
          <h3 class="font-bold text-slate-900 dark:text-slate-100 text-sm flex items-center gap-2">
            <Printer class="w-4 h-4 text-emerald-500" />
            Pengaturan Struk Thermal & QRIS
          </h3>

          <div>
            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">
              Catatan / Footer Struk
            </label>
            <textarea
              v-model="settingsForm.receipt_footer"
              rows="4"
              class="w-full px-3.5 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-xs sm:text-sm focus:outline-none focus:ring-1 focus:ring-emerald-500"
            ></textarea>
          </div>

          <div>
            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">
              Nama Merchant QRIS
            </label>
            <input
              v-model="settingsForm.qris_merchant_name"
              type="text"
              class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-xs sm:text-sm focus:outline-none focus:ring-1 focus:ring-emerald-500"
            />
          </div>

          <div>
            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">
              NMID / ID Merchant QRIS
            </label>
            <input
              v-model="settingsForm.qris_merchant_id"
              type="text"
              class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-xs sm:text-sm font-mono-num focus:outline-none focus:ring-1 focus:ring-emerald-500"
            />
          </div>
        </div>
      </div>
    </div>

    <!-- TAB 2: USER MANAGEMENT -->
    <div v-else-if="activeTab === 'users'" class="space-y-4">
      <div class="flex justify-between items-center">
        <div>
          <h3 class="font-extrabold text-slate-900 dark:text-slate-100 text-base">
            Daftar Akun Kasir & Pengguna
          </h3>
          <p class="text-xs text-slate-500">Kelola kredensial dan hak akses pengguna sistem</p>
        </div>

        <button
          @click="openAddUserModal"
          class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 active:scale-95 text-white text-xs font-extrabold shadow-md transition-all flex items-center gap-1.5"
        >
          <UserPlus class="w-4 h-4" />
          <span>Tambah Pengguna</span>
        </button>
      </div>

      <!-- Users Table -->
      <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 overflow-hidden shadow-sm">
        <table class="w-full text-left border-collapse text-xs sm:text-sm">
          <thead>
            <tr class="border-b border-slate-200 dark:border-slate-800 bg-slate-50/80 dark:bg-slate-950/60 text-[11px] font-bold uppercase tracking-wider text-slate-500">
              <th class="py-3 px-4">Nama</th>
              <th class="py-3 px-4">Email</th>
              <th class="py-3 px-4">Role / Peran</th>
              <th class="py-3 px-4 text-center">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
            <tr v-for="u in users" :key="u.id" class="hover:bg-slate-50 dark:hover:bg-slate-800/40">
              <td class="py-3 px-4 font-bold text-slate-800 dark:text-slate-200">
                {{ u.name }}
              </td>
              <td class="py-3 px-4 text-slate-500">
                {{ u.email }}
              </td>
              <td class="py-3 px-4">
                <span
                  class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase"
                  :class="{
                    'bg-purple-100 text-purple-700 dark:bg-purple-950/70 dark:text-purple-400': u.role === 'owner',
                    'bg-indigo-100 text-indigo-700 dark:bg-indigo-950/70 dark:text-indigo-400': u.role === 'admin',
                    'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/70 dark:text-emerald-400': u.role === 'kasir',
                  }"
                >
                  {{ u.role }}
                </span>
              </td>
              <td class="py-3 px-4 text-center">
                <div class="flex items-center justify-center gap-1">
                  <button
                    @click="openEditUserModal(u)"
                    class="p-1.5 rounded-lg text-slate-400 hover:text-emerald-600 transition-colors"
                  >
                    <Edit3 class="w-4 h-4" />
                  </button>
                  <button
                    v-if="u.id !== authStore.user?.id"
                    @click="handleDeleteUser(u)"
                    class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 transition-colors"
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

    <!-- User Modal (Add / Edit) -->
    <div
      v-if="showUserModal"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/75 backdrop-blur-sm"
    >
      <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl shadow-2xl max-w-sm w-full p-6 animate-in zoom-in-95 duration-150">
        <div class="flex justify-between items-center mb-4">
          <h3 class="font-extrabold text-slate-900 dark:text-slate-100 text-base">
            {{ isEditingUser ? 'Edit Pengguna' : 'Tambah Pengguna Baru' }}
          </h3>
          <button @click="showUserModal = false" class="text-slate-400 hover:text-slate-600">
            <X class="w-5 h-5" />
          </button>
        </div>

        <form @submit.prevent="handleSaveUser" class="space-y-3.5">
          <div>
            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">
              Nama Lengkap *
            </label>
            <input
              v-model="userForm.name"
              type="text"
              required
              class="w-full px-3.5 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-xs sm:text-sm focus:outline-none focus:ring-1 focus:ring-emerald-500"
            />
          </div>

          <div>
            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">
              Email *
            </label>
            <input
              v-model="userForm.email"
              type="email"
              required
              class="w-full px-3.5 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-xs sm:text-sm focus:outline-none focus:ring-1 focus:ring-emerald-500"
            />
          </div>

          <div>
            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">
              Kata Sandi {{ isEditingUser ? '(Kosongkan jika tetap)' : '*' }}
            </label>
            <input
              v-model="userForm.password"
              type="password"
              :required="!isEditingUser"
              placeholder="••••••••"
              class="w-full px-3.5 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-xs sm:text-sm focus:outline-none focus:ring-1 focus:ring-emerald-500"
            />
          </div>

          <div>
            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">
              Peran (Role) *
            </label>
            <select
              v-model="userForm.role"
              required
              class="w-full px-3.5 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-xs sm:text-sm focus:outline-none focus:ring-1 focus:ring-emerald-500"
            >
              <option value="kasir">Kasir (Hanya Akses Terminal Kasir)</option>
              <option value="admin">Admin (Akses Kasir + Produk + Laporan)</option>
              <option value="owner">Owner (Akses Penuh Seluruh Sistem)</option>
            </select>
          </div>

          <div class="pt-3 border-t border-slate-200 dark:border-slate-800 flex justify-end gap-2">
            <button
              type="button"
              @click="showUserModal = false"
              class="px-4 py-2 rounded-xl border border-slate-300 dark:border-slate-700 text-xs font-semibold hover:bg-slate-100 transition-colors"
            >
              Batal
            </button>
            <button
              type="submit"
              class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold transition-all"
            >
              Simpan
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '@/services/api'
import { useAuthStore } from '@/stores/auth'
import { useToastStore } from '@/stores/toast'
import {
  Sliders,
  Store,
  Users,
  Printer,
  Save,
  UserPlus,
  Edit3,
  Trash2,
  X,
} from 'lucide-vue-next'

const authStore = useAuthStore()
const toast = useToastStore()

const activeTab = ref('store')
const isSaving = ref(false)

const settingsForm = ref({
  store_name: '',
  store_tagline: '',
  store_address: '',
  store_phone: '',
  receipt_footer: '',
  tax_percentage: '11',
  qris_merchant_name: '',
  qris_merchant_id: '',
})

const users = ref([])
const showUserModal = ref(false)
const isEditingUser = ref(false)
const editingUserId = ref(null)

const userForm = ref({
  name: '',
  email: '',
  password: '',
  role: 'kasir',
})

async function loadSettings() {
  try {
    const res = await api.get('/settings')
    if (res.data.success) {
      settingsForm.value = { ...settingsForm.value, ...res.data.data }
    }
  } catch (err) {
    toast.error('Gagal memuat pengaturan toko')
  }
}

async function handleSaveSettings() {
  isSaving.value = true
  try {
    const res = await api.post('/settings', settingsForm.value)
    if (res.data.success) {
      toast.success('Pengaturan toko berhasil disimpan!')
    }
  } catch (err) {
    toast.error('Gagal menyimpan pengaturan toko')
  } finally {
    isSaving.value = false
  }
}

async function loadUsers() {
  try {
    const res = await api.get('/users')
    if (res.data.success) {
      users.value = res.data.data
    }
  } catch (err) {
    console.warn(err)
  }
}

function openAddUserModal() {
  isEditingUser.value = false
  editingUserId.value = null
  userForm.value = {
    name: '',
    email: '',
    password: '',
    role: 'kasir',
  }
  showUserModal.value = true
}

function openEditUserModal(u) {
  isEditingUser.value = true
  editingUserId.value = u.id
  userForm.value = {
    name: u.name,
    email: u.email,
    password: '',
    role: u.role,
  }
  showUserModal.value = true
}

async function handleSaveUser() {
  try {
    if (isEditingUser.value) {
      const payload = { ...userForm.value }
      if (!payload.password) delete payload.password
      const res = await api.put(`/users/${editingUserId.value}`, payload)
      if (res.data.success) {
        toast.success('Pengguna berhasil diperbarui!')
        showUserModal.value = false
        loadUsers()
      }
    } else {
      const res = await api.post('/users', userForm.value)
      if (res.data.success) {
        toast.success('Pengguna baru berhasil ditambahkan!')
        showUserModal.value = false
        loadUsers()
      }
    }
  } catch (err) {
    const msg = err.response?.data?.message || 'Gagal menyimpan pengguna'
    toast.error(msg)
  }
}

async function handleDeleteUser(u) {
  if (confirm(`Hapus pengguna '${u.name}'?`)) {
    try {
      const res = await api.delete(`/users/${u.id}`)
      if (res.data.success) {
        toast.success('Pengguna berhasil dihapus')
        loadUsers()
      }
    } catch (err) {
      toast.error('Gagal menghapus pengguna')
    }
  }
}

onMounted(() => {
  loadSettings()
  loadUsers()
})
</script>
