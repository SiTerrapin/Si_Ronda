# 🛡️ SiRonda

![SiRonda Logo](./public/assets/icon/LogoNav_sironda.png)

**SiRonda** adalah **Aplikasi Penjadwalan dan Monitoring Ronda Malam** berbasis Laravel 12 & Bootstrap 5 (Vite) yang dirancang untuk membantu warga dan pengurus RT/RW mengatur jadwal ronda, memantau kehadiran, mendokumentasikan laporan kejadian malam, serta mempublikasikan jadwal keamanan lingkungan secara transparan.

---

## 🚀 Fitur Utama & Pembagian Hak Akses

1. **Area Publik / Warga (Tanpa Login)**
   - **Beranda (`/`)**: Ringkasan petugas ronda malam ini, statistik singkat, dan situasi keamanan terbaru yang telah diverifikasi.
   - **Jadwal Ronda (`/jadwal`)**: Jadwal ronda mingguan (Senin–Minggu) lengkap dengan filter hari, pencarian nama/pos/RT, dan mode cetak.
   - **Direktori Anggota (`/anggota`)**: Daftar anggota ronda aktif per RT/Blok.
   - **Kegiatan & Situasi (`/kegiatan`)**: Riwayat laporan patroli malam yang sudah terverifikasi.

2. **Petugas Ronda (`role: petugas`)**
   - Melihat jadwal jaga pribadi dan melakukan **Presensi Kehadiran Real-Time** (*Hadir* sesuai jam shift ronda, *Ajukan Izin* di hari jadwal).
   - Mengirim **Laporan Patroli / Kejadian Malam** lengkap dengan kategori situasi dan unggah **Foto Bukti**.
   - Mengelola **Profil & Password Mandiri** di menu `/profile`.

3. **Admin / Pengurus RT (`role: admin`)**
   - **CRUD Data Petugas** (`/petugas`): Tambah, edit, aktifkan/nonaktifkan pendaftar baru (terbatas pada petugas biasa).
   - **Manajemen Plotting Jadwal** (`/plot`): Tambah/edit jadwal manual dengan validasi anti-bentrok, *Auto-Schedule* & *Auto-Rotate* mingguan (ON/OFF & pilihan hari reset), serta tombol cepat **Pengingat WhatsApp (`wa.me`)**.
   - **Verifikasi Laporan** (`/laporan`) & **Rekap Ronda Harian** (`/rekap`).

4. **Super Admin (`role: super_admin`)**
   - Memiliki seluruh hak akses `admin` ditambah otorisasi penuh untuk **mengubah role** pengguna (`petugas` ↔ `admin` ↔ `super_admin`) serta **mengubah gambar Banner/Kegiatan Portal Warga** di `/portal-settings`.

---

## 🛠️ Teknologi yang Digunakan
- **Backend:** Laravel 12 (PHP 8.2+)
- **Frontend:** Blade, Bootstrap 5, Vite
- **Database:** MySQL 8.0 (Docker) / SQLite (`:memory:` untuk testing)

---

## 📌 Cara Menjalankan Proyek

### 1. Jalankan Database dengan Docker (Direkomendasikan)
Pastikan Docker Desktop sudah aktif, lalu jalankan:
```bash
docker compose up -d
```
- **MySQL 8.0** berjalan di port `3306` (database `sironda`, user `root`, password `root`).
- **phpMyAdmin** dapat diakses di `http://localhost:8080`.

### 2. Setup Aplikasi Laravel
```bash
cp .env.example .env
composer install
php artisan key:generate
php artisan migrate:fresh --seed
php artisan storage:link
npm install && npm run build
php artisan serve
```
Buka browser di **`http://127.0.0.1:8000`**.

---

## 🔑 Akun Default (Hasil Seeder)

Data akun awal didefinisikan di `database/seeders/PetugasSeeder.php`:

| Peran | Username | Password |
| :--- | :--- | :--- |
| **Super Admin** | `superadmin` | `superadmin123` |
| **Admin** | `admin` | `admin123` |
| **Tester (Petugas)** | `tester` | `user123` |
| **Petugas 1 – 21** | `petugas1` s/d `petugas21` | `user123` |

*(Catatan: Pendaftar baru melalui `/register` otomatis berstatus `is_active = '0'` dan memerlukan aktivasi oleh Admin sebelum dapat login.)*

---

## 🧪 Menjalankan Pengujian Otomatis

`phpunit.xml` telah dikonfigurasi menggunakan SQLite `:memory:` agar tidak mengubah data database utama:
```bash
php artisan test
```
