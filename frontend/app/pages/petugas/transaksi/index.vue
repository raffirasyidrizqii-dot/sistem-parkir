<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'

definePageMeta({
  middleware: 'auth'
})

const { $api } = useNuxtApp() as any
const router = useRouter()

const transaksiList = ref<any[]>([])
const searchQuery = ref('')
const selectedType = ref<'all' | 'non-member' | 'member'>('all')
const loading = ref(false)

const fetchTransaksi = async () => {
  loading.value = true
  try {
    const res = await $api.get('/transaksi')
    if (res.data?.data) {
      transaksiList.value = res.data.data
    }
  } catch (error) {
    console.error('Gagal mengambil data transaksi:', error)
  } finally {
    loading.value = false
  }
}

// Deteksi tipe transaksi (Member atau Non-Member)
const getTipeTransaksi = (item: any): 'Member' | 'Non-Member' => {
  if (item.tipe) {
    return item.tipe.toLowerCase().includes('member') && !item.tipe.toLowerCase().includes('non')
      ? 'Member'
      : 'Non-Member'
  }
  if (item.kode_member || (item.kode_tiket && item.kode_tiket.startsWith('MBR-'))) {
    return 'Member'
  }
  return 'Non-Member'
}

const filteredList = computed(() => {
  const q = searchQuery.value.trim().toLowerCase()

  return transaksiList.value.filter((item) => {
    const tipe = getTipeTransaksi(item)

    // Filter berdasarkan tab
    if (selectedType.value === 'member' && tipe !== 'Member') return false
    if (selectedType.value === 'non-member' && tipe !== 'Non-Member') return false

    // Filter berdasarkan pencarian
    if (!q) return true

    const kodeTiket = (item.kode_tiket || item.kode_member || '').toLowerCase()
    const noPlat = (item.no_plat || item.plat_nomor || '').toLowerCase()
    const kategori = (item.kategori || '').toLowerCase()
    const nama = (item.nama_member || '').toLowerCase()

    return kodeTiket.includes(q) || noPlat.includes(q) || kategori.includes(q) || nama.includes(q)
  })
})

const totalPendapatan = computed(() => {
  return filteredList.value.reduce((acc, curr) => acc + Number(curr.total_bayar || curr.total_tarif || 0), 0)
})

const countMember = computed(() => {
  return transaksiList.value.filter((item) => getTipeTransaksi(item) === 'Member').length
})

const countNonMember = computed(() => {
  return transaksiList.value.filter((item) => getTipeTransaksi(item) === 'Non-Member').length
})

const formatRupiah = (val: any) => {
  const num = Number(val)
  return isNaN(num) ? '0' : new Intl.NumberFormat('id-ID').format(num)
}

const formatTanggal = (dateStr: string) => {
  if (!dateStr) return '-'
  return new Date(dateStr).toLocaleString('id-ID', {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const logout = async () => {
  try {
    await $api.post('/logout')
  } catch {}
  localStorage.removeItem('token')
  router.push('/')
}

onMounted(() => {
  fetchTransaksi()
})
</script>

<template>
  <div class="min-h-screen bg-[#F8FAFC] flex font-sans antialiased text-slate-800">
    <!-- SIDEBAR PERSIS REFERENSI (TANPA GATE MASUK) -->
    <aside class="w-64 bg-[#0B0F19] text-slate-400 flex flex-col justify-between py-6 px-4 shrink-0 select-none hidden md:flex">
      <div>
        <div class="flex items-center justify-between px-2 mb-7">
          <div class="flex items-center gap-2.5">
            <div class="w-9 h-9 rounded-xl bg-cyan-400 text-[#0B0F19] flex items-center justify-center font-black text-base shadow-[0_0_15px_rgba(34,211,238,0.3)]">
              P
            </div>
            <div>
              <span class="text-base font-extrabold tracking-tight text-white block leading-none">PARKIR</span>
              <span class="text-[9px] text-slate-500 font-bold uppercase tracking-widest mt-0.5 block">PLAZA ANDALAS</span>
            </div>
          </div>
          <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
        </div>

        <nav class="space-y-1">
          <NuxtLink
            to="/petugas"
            class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-slate-400 hover:text-white hover:bg-slate-800/40 font-semibold text-xs transition"
          >
            <span class="text-sm">⊞</span>
            <span>Dashboard</span>
          </NuxtLink>

          <NuxtLink
            to="/petugas/keluar"
            class="flex items-center justify-between px-3.5 py-2.5 rounded-xl text-slate-400 hover:text-white hover:bg-slate-800/40 text-xs font-semibold transition"
          >
            <div class="flex items-center gap-3">
              <span class="text-sm">🚪</span>
              <span>Gate Keluar (Kasir)</span>
            </div>
            <span class="text-xs text-slate-600">›</span>
          </NuxtLink>

          <!-- Active Menu: Kelola Transaksi -->
          <NuxtLink
            to="/petugas/transaksi"
            class="flex items-center justify-between px-3.5 py-2.5 rounded-xl bg-slate-800/90 text-white font-semibold text-xs shadow-xs"
          >
            <div class="flex items-center gap-3">
              <span class="text-sm text-cyan-400">🚗</span>
              <span>Kelola Transaksi</span>
            </div>
            <span class="text-xs text-cyan-400 font-bold">●</span>
          </NuxtLink>

          <NuxtLink
            to="/petugas/member/select"
            class="flex items-center justify-between px-3.5 py-2.5 rounded-xl text-slate-400 hover:text-white hover:bg-slate-800/40 text-xs font-semibold transition"
          >
            <div class="flex items-center gap-3">
              <span class="text-sm">👥</span>
              <span>Kelola Member</span>
            </div>
            <span class="text-xs text-slate-600">›</span>
          </NuxtLink>

          <NuxtLink
            to="/petugas/laporan"
            class="flex items-center justify-between px-3.5 py-2.5 rounded-xl text-slate-400 hover:text-white hover:bg-slate-800/40 text-xs font-semibold transition"
          >
            <div class="flex items-center gap-3">
              <span class="text-sm">📊</span>
              <span>Laporan</span>
            </div>
            <span class="text-xs text-slate-600">›</span>
          </NuxtLink>

          <NuxtLink
            to="/petugas/markir"
            class="flex items-center justify-between px-3.5 py-2.5 rounded-xl text-slate-400 hover:text-white hover:bg-slate-800/40 text-xs font-semibold transition"
          >
            <div class="flex items-center gap-3">
              <span class="text-sm">🅿️</span>
              <span>Sedang Parkir</span>
            </div>
            <span class="text-xs text-slate-600">›</span>
          </NuxtLink>
        </nav>
      </div>

      <div class="space-y-3 pt-4 border-t border-slate-800/80">
        <div class="bg-slate-900/90 border border-slate-800 px-3.5 py-2.5 rounded-2xl flex items-center gap-3">
          <div class="w-8 h-8 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 flex items-center justify-center text-xs font-bold">
            P
          </div>
          <div class="min-w-0 flex-1">
            <p class="text-xs font-bold text-white truncate">Petugas Parkir</p>
            <p class="text-[10px] text-emerald-400 font-medium">Sistem Aktif</p>
          </div>
        </div>

        <button
          @click="logout"
          class="w-full flex items-center gap-3 px-3.5 py-2 rounded-xl text-xs font-semibold text-rose-400 hover:bg-rose-500/10 transition cursor-pointer"
        >
          <span>🚪</span>
          <span>Logout</span>
        </button>
      </div>
    </aside>

    <!-- CONTENT AREA -->
    <main class="flex-1 flex flex-col min-w-0 h-screen overflow-y-auto">
      <header class="bg-white px-8 py-5 flex items-center justify-between border-b border-slate-100 shrink-0">
        <div>
          <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">TRANSACTION HISTORY</span>
          <h1 class="text-xl font-extrabold text-slate-900 tracking-tight">Kelola Riwayat Transaksi</h1>
        </div>

        <div class="flex items-center gap-3">
          <NuxtLink
            to="/petugas"
            class="bg-slate-100 hover:bg-slate-200 text-slate-700 px-4 py-2 rounded-full text-xs font-bold transition flex items-center gap-1.5 cursor-pointer shadow-xs"
          >
            <span>←</span> Dashboard
          </NuxtLink>
        </div>
      </header>

      <div class="p-8 space-y-6">
        <!-- STATISTIK RINGKAS TRANSAKSI -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
          <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-xs flex items-center justify-between">
            <div>
              <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mb-1">Total Transaksi</span>
              <h3 class="text-2xl font-black text-slate-900">{{ transaksiList.length }} Data</h3>
              <span class="text-[11px] text-slate-400">Seluruh riwayat checkout</span>
            </div>
            <div class="w-10 h-10 rounded-xl bg-slate-100 text-slate-700 flex items-center justify-center text-base font-bold">
              📑
            </div>
          </div>

          <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-xs flex items-center justify-between">
            <div>
              <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mb-1">Total Kasir Non-Member</span>
              <h3 class="text-2xl font-black text-emerald-600">{{ countNonMember }} Unit</h3>
              <span class="text-[11px] text-slate-400">Tiket berbayar harian</span>
            </div>
            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-base font-bold">
              🎟️
            </div>
          </div>

          <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-xs flex items-center justify-between">
            <div>
              <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mb-1">Checkout Member</span>
              <h3 class="text-2xl font-black text-cyan-600">{{ countMember }} Akses</h3>
              <span class="text-[11px] text-slate-400">Akses kartu langganan</span>
            </div>
            <div class="w-10 h-10 rounded-xl bg-cyan-50 text-cyan-600 flex items-center justify-center text-base font-bold">
              👥
            </div>
          </div>
        </div>

        <!-- SEARCH BAR & FILTER TIPE TIKET -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
          <!-- Filter Tab: All / Non-Member / Member -->
          <div class="bg-slate-100 p-1 rounded-2xl flex items-center gap-1 shrink-0">
            <button
              type="button"
              @click="selectedType = 'all'"
              :class="selectedType === 'all' ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-500 hover:text-slate-900'"
              class="px-4 py-2 rounded-xl text-xs font-black transition cursor-pointer"
            >
              Semua ({{ transaksiList.length }})
            </button>
            <button
              type="button"
              @click="selectedType = 'non-member'"
              :class="selectedType === 'non-member' ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-500 hover:text-slate-900'"
              class="px-4 py-2 rounded-xl text-xs font-black transition cursor-pointer"
            >
              Non-Member ({{ countNonMember }})
            </button>
            <button
              type="button"
              @click="selectedType = 'member'"
              :class="selectedType === 'member' ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-500 hover:text-slate-900'"
              class="px-4 py-2 rounded-xl text-xs font-black transition cursor-pointer"
            >
              Member ({{ countMember }})
            </button>
          </div>

          <!-- Input Search -->
          <div class="relative w-full sm:w-72">
            <span class="absolute left-3.5 top-2.5 text-xs text-slate-400">🔍</span>
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Cari kode tiket / plat nomor..."
              class="w-full pl-9 pr-4 py-2.5 bg-white border border-slate-200 rounded-2xl text-xs font-semibold focus:outline-none focus:border-[#0284C7] focus:ring-2 focus:ring-[#0284C7]/20 transition"
            />
          </div>
        </div>

        <!-- TABLE SECTION -->
        <div class="bg-white rounded-3xl border border-slate-100 shadow-xs overflow-hidden">
          <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
              <thead>
                <tr class="bg-slate-50/80 border-b border-slate-100 text-[11px] font-bold text-slate-400 uppercase tracking-wider">
                  <th class="py-3.5 px-5">Kode Identitas</th>
                  <th class="py-3.5 px-5">Tipe Akses</th>
                  <th class="py-3.5 px-5">Kategori</th>
                  <th class="py-3.5 px-5">No. Plat / Nama</th>
                  <th class="py-3.5 px-5 text-right">Tarif</th>
                  <th class="py-3.5 px-5 text-right">Uang Tunai</th>
                  <th class="py-3.5 px-5 text-right">Kembalian</th>
                  <th class="py-3.5 px-5 text-center">Waktu Transaksi</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100 text-xs font-semibold text-slate-700">
                <tr v-if="loading">
                  <td colspan="8" class="py-12 text-center text-slate-400 font-bold">
                    Memuat data riwayat transaksi...
                  </td>
                </tr>
                <tr v-else-if="filteredList.length === 0">
                  <td colspan="8" class="py-12 text-center text-slate-400 font-bold">
                    Tidak ada riwayat transaksi yang cocok dengan pencarian/filter.
                  </td>
                </tr>
                <tr
                  v-else
                  v-for="item in filteredList"
                  :key="item.id"
                  class="hover:bg-slate-50/60 transition"
                >
                  <!-- Kode Tiket / Member -->
                  <td class="py-4 px-5 font-mono font-black text-indigo-600">
                    {{ item.kode_tiket || item.kode_member || '-' }}
                  </td>

                  <!-- Badge Tipe: Member / Non-Member -->
                  <td class="py-4 px-5">
                    <span
                      class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wider inline-flex items-center gap-1"
                      :class="getTipeTransaksi(item) === 'Member'
                        ? 'bg-cyan-50 text-cyan-700 border border-cyan-200'
                        : 'bg-emerald-50 text-emerald-700 border border-emerald-200'"
                    >
                      <span>{{ getTipeTransaksi(item) === 'Member' ? '★' : '●' }}</span>
                      <span>{{ getTipeTransaksi(item) }}</span>
                    </span>
                  </td>

                  <!-- Kategori Kendaraan -->
                  <td class="py-4 px-5 capitalize font-medium text-slate-700">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl bg-slate-100 text-[11px] font-bold">
                      {{ (item.kategori || '').toLowerCase().includes('mobil') ? '🚗 Mobil' : '🛵 Motor' }}
                    </span>
                  </td>

                  <!-- No Plat / Nama -->
                  <td class="py-4 px-5 font-mono font-extrabold text-slate-900 uppercase">
                    {{ item.no_plat || item.plat_nomor || item.nama_member || '-' }}
                  </td>

                  <!-- Tarif Bayar -->
                  <td class="py-4 px-5 text-right font-mono font-black text-slate-900">
                    <template v-if="getTipeTransaksi(item) === 'Member'">
                      <span class="text-cyan-600 font-bold">Langganan</span>
                    </template>
                    <template v-else>
                      Rp {{ formatRupiah(item.total_bayar || item.total_tarif || 0) }}
                    </template>
                  </td>

                  <!-- Uang Bayar Tunai -->
                  <td class="py-4 px-5 text-right font-mono font-extrabold text-emerald-600">
                    <template v-if="getTipeTransaksi(item) === 'Member'">
                      <span class="text-slate-400 font-medium">-</span>
                    </template>
                    <template v-else>
                      Rp {{ formatRupiah(item.uang_bayar || item.total_bayar || 0) }}
                    </template>
                  </td>

                  <!-- Kembalian -->
                  <td class="py-4 px-5 text-right font-mono text-slate-500">
                    <template v-if="getTipeTransaksi(item) === 'Member'">
                      <span class="text-slate-400 font-medium">-</span>
                    </template>
                    <template v-else>
                      Rp {{ formatRupiah(item.kembalian || 0) }}
                    </template>
                  </td>

                  <!-- Waktu Transaksi -->
                  <td class="py-4 px-5 text-center text-slate-500 font-medium">
                    {{ formatTanggal(item.created_at || item.tanggal_bayar || item.waktu_keluar) }}
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </main>
  </div>
</template>