# 🪙 Fintrac.AI — Asisten Keuangan Pintar & Modern

Fintrac.AI adalah aplikasi pencatatan keuangan pribadi (Finance Tracker) modern berbasis web yang terintegrasi dengan kecerdasan buatan (**AI - DeepSeek v3**). Aplikasi ini dirancang menggunakan **Parker Style Reference** (Light theme, minimalis premium, dengan dominasi warna Electric Blue dan Ember Orange).

Aplikasi ini mencakup fitur otentikasi pengguna, manajemen transaksi (pemasukan/pengeluaran) reaktif, dasbor analitik visual interaktif, pembuatan kategori dinamis secara otomatis oleh AI, serta fitur obrolan AI yang fungsional untuk menambah atau membatalkan transaksi.

---

## 🚀 Fitur Utama
1. **Dashboard Analytics (Pure Visual):** Ringkasan keuangan (Saldo Total, Pemasukan, Pengeluaran), Grafik Tren Keuangan (Harian/Bulanan), dan Donut Chart pembagian Pengeluaran & Pemasukan per kategori.
2. **Interactive Period Filtering:** Filter seluruh data dashboard & transaksi secara reaktif berdasarkan *Hari Ini*, *Minggu Ini*, *Bulan Ini*, *Tahun Ini*, atau *Semua Waktu*.
3. **Transaction Management (CRUD):** Tambah transaksi manual lewat form slide-down (tanpa ganti halaman), hapus transaksi, dan dukung paginasi tabel.
4. **Dynamic Categories:** Pembuatan kategori baru secara langsung (baik manual via modal popup, maupun otomatis saat AI mendeteksi kategori baru yang belum ada di database).
5. **AI Chat Assistant (DeepSeek v3):** Chatbot asisten keuangan yang selalu siaga di pojok kanan atas untuk:
   - Mencatat transaksi otomatis: *"Beli kopi aren 20rb"*
   - Mencatat multi-transaksi sekaligus: *"Beli bensin 15rb dan nasi goreng 12rb"*
   - Menghapus/membatalkan transaksi jika salah catat: *"gajadi beli kopi aren tadi, tolong hapus"*
   - Auto-reload otomatis setelah mencatat/menghapus transaksi via AI.

---

## 🛠️ Tech Stack
- **Backend:** Laravel 11
- **Database:** MySQL (XAMPP / MariaDB)
- **Frontend:** Blade Templates, TailwindCSS (CDN), Alpine.js
- **Charts:** Chart.js
- **Integrasi AI:** DeepSeek API

---

## ⚙️ Persyaratan Sistem
Sebelum memulai, pastikan perangkat Anda sudah terpasang:
- PHP >= 8.2
- Composer
- XAMPP (untuk mengaktifkan Apache & MySQL)
- Koneksi internet (untuk request AI ke DeepSeek API)

---

## 📥 Panduan Instalasi & Setup Kelompok

Ikuti langkah-langkah di bawah ini untuk menjalankan project di komputer masing-masing:

### 1. Clone Repositori
Clone project dari GitHub ke folder lokal Anda:
```bash
git clone https://github.com/gungwisnu/imskeuangan.git
cd imskeuangan
```

### 2. Install Dependensi PHP (Laravel)
Jalankan perintah berikut untuk mengunduh semua library package yang dibutuhkan:
```bash
composer install
```

### 3. Konfigurasi Environment File (`.env`)
Salin file `.env.example` menjadi `.env`:
```bash
cp .env.example .env
```
Buka file `.env` yang baru dibuat di VS Code/Text Editor lainnya, lalu sesuaikan bagian konfigurasi database dan API key seperti berikut:

```ini
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=imskeuangan
DB_USERNAME=root
DB_PASSWORD=

# Integrasi DeepSeek API
DEEPSEEK_API_KEY=sk-your-actual-api-key
DEEPSEEK_BASE_URL=https://api.deepseek.com/v1

# Pastikan session driver adalah database untuk tracking chat log & session
SESSION_DRIVER=database
```

### 4. Generate Application Key
Jalankan command berikut untuk membuat key enkripsi aplikasi Laravel:
```bash
php artisan key:generate
```

### 5. Siapkan Database
1. Buka **XAMPP Control Panel**, lalu klik **Start** pada modul **Apache** dan **MySQL**.
2. Buka browser dan pergi ke [http://localhost/phpmyadmin](http://localhost/phpmyadmin).
3. Buat database baru bernama **`imskeuangan`**.

### 6. Jalankan Migrasi & Database Seeder
Jalankan migrasi tabel-tabel database sekaligus mengisi kategori dasar default:
```bash
php artisan migrate --seed
```

### 7. Jalankan Server Lokal
Nyalakan server development Laravel:
```bash
php artisan serve
```
Buka browser Anda dan akses aplikasi di:
👉 **[http://127.0.0.1:8000](http://127.0.0.1:8000)**

---

## 🔑 Akun Uji Coba (Testing)
Anda bisa mendaftar akun baru lewat tombol **Daftar** di pojok kanan atas, atau menggunakan akun demo bawaan berikut untuk langsung melihat visualisasi data:
- **Email:** `demo@example.com`
- **Password:** `password123`

---

## 💬 Cara Mencoba Fitur AI Chat
Setelah berhasil masuk ke dashboard, klik tombol **Tanya AI** di bagian navigasi atas untuk membuka asisten keuangan. Anda bisa mencoba beberapa perintah berikut:

1. **Mencatat Satu Transaksi:**
   > *"Tadi pagi saya beli bensin motor sebesar 15.000"*
2. **Mencatat Beberapa Transaksi Sekaligus:**
   > *"Baru saja beli nasi goreng 15 ribu dan es teh manis 5 ribu"*
3. **Mencatat dengan Kategori Baru (Auto-Create):**
   > *"Kemarin bayar hutang ke temen 100 ribu"* (AI akan mendeteksi kategori baru bernama `Hutang` dan otomatis menambahkannya ke database).
4. **Membatalkan / Menghapus Transaksi:**
   > *"eh ternyata aku gajadi beli nasi goreng tadi, tolong diapus"* (AI akan menghapus transaksi nasi goreng terakhir Anda dari tabel secara otomatis).

---

## 👥 Kelompok Pengembangan
- **Identitas Kelompok:** 053.089.098.106.150
- **Repositori:** [gungwisnu/imskeuangan](https://github.com/gungwisnu/imskeuangan)
