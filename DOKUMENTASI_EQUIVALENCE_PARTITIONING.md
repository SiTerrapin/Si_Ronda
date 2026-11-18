# DOKUMENTASI PENGUJIAN PERANGKAT LUNAK: EQUIVALENCE PARTITIONING (EP)
## Sistem Informasi Ronda Terpadu (SiRonda)
*Tersinkronisasi dengan `tests/Feature/EquivalencePartitioningTest.php` dan `tests/Feature/InternalMenuEquivalencePartitioningTest.php`*

---

### 1. FITUR: REGISTRASI & MODERASI AKUN (`/register`)

**RULE:**
- **Valid:**  
  Username unik (1–20 karakter), password $\ge$ 6 karakter dan cocok dengan konfirmasi, nama lengkap terisi, serta gender `'M'` atau `'F'`. Sistem membuat akun dengan `role = 'petugas'` dan `is_active = '0'` (menunggu aktivasi Admin sebelum dapat login).
- **Invalid (Username Duplikat):**  
  Username sudah terdaftar di tabel `petugas`, sistem menolak dengan error validasi `username`.
- **Invalid (Password < 6 Karakter / Mismatch):**  
  Password kurang dari 6 karakter atau tidak sama dengan konfirmasi, sistem menolak dengan error validasi `password`.
- **Invalid (Gender di luar M/F):**  
  Gender selain `'M'` dan `'F'`, sistem menolak dengan error validasi `gender`.

| ID KASUS | SKENARIO | DATA INPUT | HASIL AKTUAL | STATUS |
| :---: | :--- | :---: | :--- | :---: |
| **REG-01** | Registrasi lengkap dengan data valid & unik | `username: "budi_santoso"`, `pass: "rahasia123"`, `gender: "M"` | Redirect ke `/login`, akun tersimpan dengan `role = 'petugas'` dan `is_active = '0'` | **Valid** |
| **REG-02** | Registrasi dengan username yang sudah terdaftar | `username: "budi_santoso"` (duplikat) | Sistem menolak dan mengembalikan error validasi `username` | **Tidak Valid** |
| **REG-03** | Registrasi dengan password di bawah 6 karakter | `password: "12345"` (5 karakter) | Sistem menolak dan mengembalikan error validasi `password` | **Tidak Valid** |
| **REG-04** | Registrasi dengan konfirmasi password berbeda | `pass: "rahasia123"`, `konf: "berbeda123"` | Sistem menolak dan mengembalikan error validasi `password` | **Tidak Valid** |
| **REG-05** | Registrasi dengan nilai gender tidak sah | `gender: "X"` | Sistem menolak dan mengembalikan error validasi `gender` | **Tidak Valid** |

---

### 2. FITUR: LOGIN PETUGAS & ADMIN (`/login`)

**RULE:**
- **Valid:**  
  Username terdaftar, password cocok, dan `is_active = '1'`. Sistem mengautentikasi pada guard `petugas` dan mengarahkan ke `/dashboard`.
- **Invalid (Akun Nonaktif / Belum Diaktifkan Admin):**  
  Kredensial benar tetapi `is_active = '0'`, sistem menolak login dengan pesan *"Akun nonaktif"*.
- **Invalid (Password / Username Salah):**  
  Username tidak ditemukan atau password salah, sistem menolak login dengan pesan *"Username atau password salah"*.

| ID KASUS | SKENARIO | DATA INPUT | HASIL AKTUAL | STATUS |
| :---: | :--- | :---: | :--- | :---: |
| **LOG-01** | Login dengan kredensial valid & akun aktif | `user: "ronda_user"`, `pass: "password123"`, `is_active: '1'` | Terautentikasi di guard `petugas`, redirect ke `/dashboard` | **Valid** |
| **LOG-02** | Login dengan akun yang berstatus nonaktif | `user: "user_nonaktif"`, `is_active: '0'` | Ditolak, tetap guest, muncul error *"Akun nonaktif"* | **Tidak Valid** |
| **LOG-03** | Login dengan password salah | `user: "ronda_user"`, `pass: "passwordSalah"` | Ditolak, tetap guest, muncul error *"Username atau password salah"* | **Tidak Valid** |

---

### 3. FITUR: PENJADWALAN RONDA OTOMATIS PROPORSIONAL (`POST /plot/schedule` — Admin Only)

**RULE:**
- **Valid ($\ge 1$ Petugas Aktif):**  
  Jumlah Petugas Aktif $\ge 1$, sistem mengacak dan merotasi petugas secara proporsional untuk 7 hari (maksimal 3 pos per hari: `Pos Utama`, `Pos Utara`, `Pos Selatan`, dengan 1 komandan/leader setiap hari).
- **Invalid (0 Petugas Aktif):**  
  Jumlah Petugas Aktif $= 0$, sistem menolak penjadwalan dan memunculkan pesan error *"Belum ada petugas aktif untuk penjadwalan."*

| ID KASUS | SKENARIO | DATA INPUT | HASIL AKTUAL | STATUS |
| :---: | :--- | :---: | :--- | :---: |
| **SCH-01** | Penjadwalan otomatis dengan petugas aktif $\ge 21$ | 21 Petugas Aktif | Berhasil membuat 21 plot jadwal (7 hari $\times$ 3 pos, 7 leader) | **Valid** |
| **SCH-02** | Penjadwalan otomatis proporsional saat petugas aktif $< 21$ ($\ge 1$) | 11 Petugas Aktif | Berhasil merotasi secara proporsional menjadi 21 plot jadwal (7 leader) | **Valid** |
| **SCH-03** | Penjadwalan saat tidak ada petugas aktif sama sekali | 0 Petugas Aktif | Sistem menolak dan memunculkan pesan error kuota kosong | **Tidak Valid** |

---

### 4. FITUR: PLOTTING JADWAL MANUAL & VALIDASI ANTI-BENTROK (`POST /plot` — Admin Only)

**RULE:**
- **Valid:**  
  Petugas aktif belum dijadwalkan pada kombinasi `nama_hari` dan `shift` yang sama. Sistem menyimpan slot jadwal baru.
- **Invalid (Bentrok Jadwal):**  
  Petugas yang sama di-plot ulang pada `nama_hari` dan `shift` yang sama. Sistem menolak dengan error validasi `petugas_id`.

| ID KASUS | SKENARIO | DATA INPUT | HASIL AKTUAL | STATUS |
| :---: | :--- | :---: | :--- | :---: |
| **PLT-01** | Plot petugas pada hari & shift yang belum terisi | `Budi` — `Senin`, `Malam (22:00 - 04:00)` | Jadwal tersimpan dan redirect ke `/plot` | **Valid** |
| **PLT-02** | Plot petugas yang sama pada hari & shift yang sama | `Budi` — `Senin`, `Malam (22:00 - 04:00)` (slot kedua) | Sistem menolak dengan error validasi anti-bentrok pada `petugas_id` | **Tidak Valid** |

---

### 5. FITUR: PRESENSI KEHADIRAN REAL-TIME (`PATCH /plot/{id}/presensi`)

**RULE:**
- **Valid (Dalam Jam Jadwal Ronda):**  
  Petugas menekan `Hadir` atau `Izin` tepat pada rentang hari & jam shift jadwal rondanya (misal Selasa 22:00 – Rabu 04:00 WIB); atau Admin/Super Admin mengubah status kapan saja.
- **Invalid (Di Luar Jam Jadwal / Bukan Pemilik):**  
  Petugas menekan `Hadir` atau `Izin` sebelum jam shift dimulai / setelah shift berakhir, atau mengubah jadwal milik petugas lain (`403`).

| ID KASUS | SKENARIO | DATA INPUT | HASIL AKTUAL | STATUS |
| :---: | :--- | :---: | :--- | :---: |
| **PRS-01** | Petugas mengisi presensi `Hadir` saat jam shift aktif | Selasa `23:00` WIB / Rabu `02:30` WIB (`Hadir`) | Status pada `plot_ronda` terbarui menjadi `Hadir` | **Valid** |
| **PRS-02** | Petugas mengajukan `Izin` saat jam shift aktif | Selasa `22:30` WIB (`Izin`) | Status pada `plot_ronda` terbarui menjadi `Izin` | **Valid** |
| **PRS-03** | Petugas mengisi presensi `Hadir` / `Izin` sebelum jam shift | Selasa `14:00` WIB (`Hadir` / `Izin`) | Sistem menolak dengan pesan error waktu presensi | **Tidak Valid** |
| **PRS-04** | Petugas mengisi presensi `Hadir` / `Izin` setelah shift berakhir | Rabu `08:00` WIB (`Hadir` / `Izin`) | Sistem menolak dengan pesan error waktu presensi | **Tidak Valid** |
| **PRS-05** | Petugas mengubah presensi milik petugas lain | `status_kehadiran: "Hadir"` pada plot user lain | Sistem menolak dengan status HTTP `403 Forbidden` | **Tidak Valid** |

---

### 6. FITUR: LAPORAN PATROLI, DETAIL, & VERIFIKASI ADMIN (`/laporan`)

**RULE:**
- **Input Laporan Valid:** `isi_laporan` terisi ($\ge 1$ karakter); laporan tersimpan dengan `status_verifikasi = 'Pending'`.
- **Input Laporan Invalid:** `isi_laporan` kosong (`""`), sistem menolak dengan error validasi `isi_laporan`.
- **Detail Laporan:** ID terdaftar menampilkan HTTP `200`, ID tidak ada menampilkan HTTP `404`.
- **Verifikasi Admin:** Admin mengubah status laporan menjadi `Diverifikasi` via `PATCH /laporan/{id}/verify`.

| ID KASUS | SKENARIO | DATA INPUT | HASIL AKTUAL | STATUS |
| :---: | :--- | :---: | :--- | :---: |
| **LAP-01** | Kirim laporan patroli dengan deskripsi valid | `kategori: "Aman"`, `isi_laporan: "Situasi aman..."` | Tersimpan dengan `status_verifikasi = 'Pending'` | **Valid** |
| **LAP-02** | Kirim laporan dalam kondisi isi kosong | `isi_laporan: ""` | Sistem menolak dengan error validasi `isi_laporan` | **Tidak Valid** |
| **LAP-03** | Admin memverifikasi laporan masuk | `status_verifikasi: "Diverifikasi"` | Status laporan di database berubah menjadi `Diverifikasi` | **Valid** |
| **DET-01** | Mengakses detail laporan dengan ID terdaftar | `id = 1` (ada di DB) | HTTP `200` dan menampilkan isi laporan | **Valid** |
| **DET-02** | Mengakses detail laporan dengan ID tidak ditemukan | `id = 99999` | HTTP `404 Not Found` | **Tidak Valid** |

---

### 7. FITUR: HAK AKSES (RBAC 4 LEVEL: PUBLIK, PETUGAS, ADMIN, SUPER ADMIN)

**RULE:**
- **Publik / Warga (Tanpa Login):** `/`, `/jadwal`, `/anggota`, `/kegiatan` selalu dapat diakses (`200`).
- **Guest ke Area Internal:** Mengakses `/dashboard`, `/plot`, `/laporan`, `/rekap`, `/petugas`, `/profile` tanpa login dialihkan ke `/login`.
- **Petugas ke Menu Admin:** Mengakses `/petugas/create`, `/plot/create`, `/plot/schedule`, `/rekap` ditolak `403 Forbidden`.
- **Admin Biasa ke Menu Super Admin:** Mengubah role (`PATCH /petugas/{id}/role`) atau mengakses `/portal-settings` ditolak `403 Forbidden`.
- **Super Admin:** Memiliki akses penuh termasuk ubah role (`petugas` ↔ `admin`) dan kustomisasi gambar Portal Warga.

| ID KASUS | SKENARIO | DATA INPUT (ROLE/SESI) | HASIL AKTUAL | STATUS |
| :---: | :--- | :---: | :--- | :---: |
| **SEC-01** | Warga mengakses area publik tanpa login | Guest $\rightarrow$ `/`, `/jadwal`, `/anggota`, `/kegiatan` | Semua halaman publik tampil dengan HTTP `200` | **Valid** |
| **SEC-02** | Guest mengakses menu internal | Guest $\rightarrow$ `/dashboard`, `/plot`, `/laporan`, `/rekap`, `/petugas`, `/profile` | Ditolak dan dialihkan ke `/login` | **Tidak Valid** |
| **SEC-03** | Petugas biasa mengakses endpoint khusus Admin | Role `petugas` $\rightarrow$ `/petugas/create`, `/plot/schedule`, `/rekap` | Ditolak dengan HTTP `403 Forbidden` | **Tidak Valid** |
| **SEC-04** | Admin biasa mencoba ubah role atau akses `/portal-settings` | Role `admin` $\rightarrow$ `PATCH /petugas/{id}/role`, `/portal-settings` | Ditolak dengan HTTP `403 Forbidden` | **Tidak Valid** |
| **SEC-05** | Super Admin mengubah role & gambar portal warga | Role `super_admin` $\rightarrow$ `PATCH /petugas/{id}/role`, `POST /portal-settings` | Role terbarui & gambar portal tersimpan | **Valid** |

---

### 8. FITUR: DASHBOARD (SAPAAN WAKTU WIB), FILTER PETUGAS, & REKAP HARIAN

**RULE:**
- **Sapaan Waktu (`Asia/Jakarta`):** Pagi ($00 \le Jam < 11$), Siang ($11 \le Jam < 15$), Sore ($15 \le Jam < 18$), Malam ($18 \le Jam \le 23$).
- **Filter Petugas (`/petugas`):** Untuk role `petugas`, hanya akun `is_active = '1'` yang ditampilkan.
- **Generate Rekap Harian (`POST /rekap/generate` — Admin):** Menyimpan ringkasan terjadwal, hadir, izin, alfa, dan status keamanan ke `rekap_ronda_harian`.

| ID KASUS | SKENARIO | DATA INPUT | HASIL AKTUAL | STATUS |
| :---: | :--- | :---: | :--- | :---: |
| **SAP-01..04** | Sapaan waktu dashboard WIB | `08:00`, `13:00`, `16:00`, `21:00` | Menampilkan *Selamat pagi / siang / sore / malam* sesuai jam WIB | **Valid** |
| **PTG-01** | Petugas biasa melihat daftar petugas | 1 aktif, 1 nonaktif (`is_active = '0'`) | Hanya petugas aktif yang muncul; petugas nonaktif tersembunyi | **Valid** |
| **RKP-01** | Admin men-generate rekap ronda harian | `tanggal: 2026-11-18`, `status: Aman` | Record tersimpan di tabel `rekap_ronda_harian` | **Valid** |
