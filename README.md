# Sistem Manajemen Parkir Digital

Aplikasi sistem pengelolaan perparkiran kendaraan, kasir pos keluar, manajemen tiket barcode, dan pendataan member parkir berbasis web. Menggunakan arsitektur monorepo dengan **Laravel 11** sebagai REST API backend dan **Nuxt 3** sebagai antarmuka frontend.

---

## Tech Stack

- **Backend**: Laravel 11, PHP 8.2+, MySQL / SQLite, Laravel Sanctum
- **Frontend**: Nuxt 3, Vue 3 (Composition API), Tailwind CSS, Pinia, Lucide Icons
- **Database Dump**: MySQL schema & sample data disertakan pada file `parkir2.sql`

---

## Fitur Utama

1. **Pos Masuk & Cetak Tiket**
   - Pembuatan tiket parkir otomatis berdasarkan jenis kendaraan (Motor, Mobil, Truk/Bus).
   - Penomoran barcode unik dan pencatatan waktu masuk secara realtime.

2. **Pos Keluar & Kasir Transaksi**
   - Pemindaian/input nomor tiket parkir kendaraan keluar.
   - Penghitungan durasi parkir dan tarif otomatis (tarif flat / per jam).
   - Modul pembayaran tunai dengan kalkulasi uang bayar dan kembalian.
   - Cetak struk bukti pembayaran parkir.

3. **Manajemen Member Parkir**
   - Pendaftaran dan pendataan kendaraan member (plat nomor, nama pemilik, masa berlaku).
   - Akses gate otomatis untuk member aktif tanpa dikenakan tarif reguler.
   - Peringatan masa kedaluwarsa keanggotaan member.

4. **Laporan & Rekap Pendapatan**
   - Laporan transaksi harian, mingguan, dan bulanan.
   - Pemisahan laporan kendaraan non-member vs kendaraan member.
   - Ringkasan total pendapatan kasir dan sirkulasi kendaraan masuk/keluar.

5. **Multi-Role User Management**
   - **Admin**: Akses penuh dashboard analitik, manajemen petugas, pengaturan tarif, dan rekap keuangan.
   - **Petugas Kasir**: Operasional gerbang masuk/keluar, scan tiket, dan transaksi pembayaran.
   - **User / Member**: Halaman pengecekan status parkir dan informasi keanggotaan.

---

## Panduan Instalasi Lokal

### Prasyarat
- PHP 8.2+ dan Composer
- Node.js 18+ / 20+ dan npm
- MySQL Server (misal: Laragon / XAMPP)

### 1. Import Database
1. Buat database baru di MySQL dengan nama `parkir`.
2. Import file skema dan data bawaan:
   ```bash
   mysql -u root -p parkir < parkir2.sql
   ```

### 2. Setup Backend (Laravel)
```bash
cd backend

# Install dependensi
composer install

# Buat file konfigurasi environment
cp .env.example .env

# Sesuaikan DB_DATABASE=parkir, DB_USERNAME, DB_PASSWORD di file .env

# Generate app key
php artisan key:generate

# Jalankan server API (port 8000)
php artisan serve
```

### 3. Setup Frontend (Nuxt 3)
```bash
cd frontend

# Install dependensi
npm install

# Jalankan server Nuxt (port 3000)
npm run dev
```

Buka peramban di `http://localhost:3000` untuk mulai menggunakan aplikasi.

---

## Lisensi
Dirilis di bawah lisensi [MIT](LICENSE).
