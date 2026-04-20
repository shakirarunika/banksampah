# ♻️ Bank Sampah (Waste Management ERP)

Sistem Informasi Manajemen Bank Sampah berbasis Web yang dibangun menggunakan **Laravel 12** dan **Livewire 3**. Aplikasi ini dirancang untuk memudahkan operasional pengelolaan sampah perusahaan/instansi, mulai dari penimbangan sampah karyawan, manajemen harga sampah, hingga pencairan saldo.

---

## ✨ Fitur Utama

### 1. 📊 Dashboard Komprehensif
- Tampilan metrik global dan personal (Total Uang Masuk, Total Keluar, Total Sampah/Kg).
- Grafik interaktif tren penyetoran sampah bulanan.
- *Leaderboard* nasabah (karyawan) dengan kontribusi sampah terbanyak.

### 2. 🗃️ Manajemen Master Data
- **Karyawan / Nasabah**: Pengelolaan data nasabah terintegrasi dengan data divisi.
- **Divisi**: Pengelompokan karyawan berdasarkan divisi/departemen asal.
- **Master Sampah & Harga**: Pengelolaan jenis sampah beserta harga per Kg yang dinamis.

### 3. ⚖️ Transaksi Penimbangan (Deposit)
- Fitur "Keranjang Timbangan" yang memungkinkan multi-input jenis sampah dalam satu struk.
- Perhitungan otomatis *subtotal* berdasarkan harga sampah terkini.
- Dukungan Import transaksi via Excel untuk migrasi data historis.
- Keamanan pembatalan transaksi (*Void*) yang mencegah saldo menjadi minus.

### 4. 💸 Pencairan Saldo (Withdrawal)
- Karyawan/Nasabah dapat mencairkan saldo hasil tabungan sampah.
- Validasi ketat untuk memastikan penarikan tidak melebihi total saldo aktif.

---

## 🏗️ Arsitektur & Teknologi

Proyek ini telah direfaktor untuk menerapkan standar **Clean Code** tingkat industri:

- **Framework**: Laravel 12.0 & PHP 8.2+
- **Frontend**: Livewire 3 (Reactive components) & Tailwind CSS
- **Database**: MySQL / SQLite (Development)
- **Design Pattern**:
  - **Service Classes**: Pemisahan logika bisnis yang rumit (seperti `DB::beginTransaction()` untuk transaksi dan void) ke dalam `TransactionService`.
  - **Form Objects**: Validasi form Livewire diisolasi menggunakan `Livewire\Form` (contoh: `TransactionForm`).
  - **PHP Backed Enums**: Penggunaan `App\Enums\TransactionStatus` yang kuat secara tipe data (*strongly-typed*) untuk menghindari salah ketik (typo).
- **Performance**: Penambahan *Database Indexing* pada kolom pencarian krusial (seperti `weighing_at` dan `status`) untuk mencegah *Full Table Scan* saat data membengkak.

---

## 🚀 Cara Instalasi Lokal

Ikuti langkah-langkah berikut untuk menjalankan aplikasi di komputer lokal:

1. **Clone repositori ini:**
   ```bash
   git clone https://github.com/shakirarunika/banksampah.git
   cd banksampah
   ```

2. **Install dependensi PHP & Node.js:**
   ```bash
   composer install
   npm install && npm run build
   ```

3. **Salin file konfigurasi environment:**
   ```bash
   cp .env.example .env
   ```

4. **Generate Application Key:**
   ```bash
   php artisan key:generate
   ```

5. **Konfigurasi Database** di file `.env`, lalu jalankan migrasi:
   ```bash
   php artisan migrate --seed
   ```

6. **Jalankan server pengembangan:**
   ```bash
   php artisan serve
   ```

## 🔒 Keamanan & Validasi
Sistem ini memproteksi aksi-aksi berisiko tinggi. Contoh:
- Admin tidak dapat menghapus akun miliknya sendiri.
- Data Master (Sampah/User) tidak bisa dihapus jika fisik datanya masih terkait dengan riwayat transaksi yang ada, menjaga integritas riwayat finansial.

---
*Dibuat untuk pengelolaan sampah yang lebih baik dan pelestarian lingkungan.* 🌱
