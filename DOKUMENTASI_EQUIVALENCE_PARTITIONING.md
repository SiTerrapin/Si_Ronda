# DOKUMENTASI PENGUJIAN PERANGKAT LUNAK: EQUIVALENCE PARTITIONING (EP)
## Sistem Informasi Ronda Terpadu (SiRonda)
*Khusus Menu & Fitur di Dalam Aplikasi (Setelah Login)*

---

### 1. FITUR: PENJADWALAN RONDA OTOMATIS (Menu Jadwal)

**RULE:**
- **Valid:**  
  Data Input $\ge$ 21 Petugas Aktif, Sistem akan menerima data dan mengenerate seluruh jadwal ronda 7 hari (3 petugas/hari, 1 orang leader).
- **Invalid (Kondisi Minimum):**  
  Data Input $<$ 21 Petugas Aktif, Sistem akan menolak dan memunculkan pesan error *"Jumlah petugas aktif tidak cukup untuk penjadwalan."*
- **Invalid (Kondisi Petugas Nonaktif):**  
  Petugas dengan status nonaktif (`is_active = '0'`) tidak dihitung ke kuota minimal 21.

| ID KASUS | SKENARIO | DATA INPUT | HASIL AKTUAL | STATUS |
| :---: | :--- | :---: | :--- | :---: |
| **001** | Input jumlah petugas aktif sesuai kriteria minimal | 21 Petugas Aktif | Sistem menerima data dan berhasil membuat 21 baris jadwal ronda | **Valid** |
| **002** | Input jumlah petugas aktif melebihi kriteria minimal | 25 Petugas Aktif | Sistem menerima data, mengacak petugas, dan berhasil membuat jadwal | **Valid** |
| **003** | Input jumlah petugas aktif di bawah kriteria minimal | 15 Petugas Aktif | Sistem menolak proses dan memunculkan pesan error kuota tidak cukup | **Tidak Valid** |
| **004** | Input jumlah petugas aktif tepat 1 angka di bawah minimal | 20 Petugas Aktif | Sistem menolak proses dan memunculkan pesan error kuota tidak cukup | **Tidak Valid** |
| **005** | Total petugas 25 orang, tetapi yang aktif di bawah kriteria minimal | 18 Petugas Aktif,<br>7 Petugas Nonaktif | Sistem menolak proses dan memunculkan pesan error kuota tidak cukup | **Tidak Valid** |

---

### 2. FITUR: INPUT LAPORAN KEGIATAN RONDA (Menu Laporan)

**RULE:**
- **Valid:**  
  Data Input Isi Laporan $\ge$ 1 Karakter teks peristiwa/kejadian, Sistem akan menerima dan menyimpan laporan ke database.
- **Invalid (Kondisi Minimum):**  
  Data Input Isi Laporan kosong (0 Karakter), Sistem akan menolak dan memunculkan pesan error *"The isi laporan field is required."*
- **Invalid (Kondisi Hanya Spasi):**  
  Data Input Isi Laporan hanya berupa spasi kosong (*whitespace*), Sistem akan menolak dan memunculkan pesan error.

| ID KASUS | SKENARIO | DATA INPUT | HASIL AKTUAL | STATUS |
| :---: | :--- | :---: | :--- | :---: |
| **006** | Input laporan dengan teks peristiwa yang lengkap | `"Pukul 02:00 situasi pos ronda aman terkendali, pintu portal gang sudah dikunci."` | Sistem menerima data, menyimpan laporan, dan memunculkan pesan sukses | **Valid** |
| **007** | Input laporan dengan teks pendek sesuai batas minimal | `"Aman"` (4 Karakter) | Sistem menerima data dan menyimpan laporan | **Valid** |
| **008** | Input laporan di bawah kriteria minimal (kosong) | `""` (0 Karakter) | Sistem menolak data dan memunculkan pesan error field required | **Tidak Valid** |
| **009** | Input laporan hanya berisi spasi kosong | `"     "` (Hanya spasi) | Sistem menolak data dan memunculkan pesan error field required | **Tidak Valid** |

---

### 3. FITUR: DETAIL LAPORAN BERDASARKAN ID (Menu Laporan)

**RULE:**
- **Valid:**  
  Data Input Parameter ID berupa angka bulat positif yang datanya terdaftar di database, Sistem akan menampilkan halaman detail laporan.
- **Invalid (ID Tidak Ditemukan):**  
  Data Input Parameter ID berupa angka yang tidak terdaftar di database, Sistem akan menolak dan memunculkan halaman error 404 Not Found.
- **Invalid (Format ID Bukan Angka):**  
  Data Input Parameter ID bernilai $\le$ 0 atau berupa teks non-numerik, Sistem akan menolak dan memunculkan halaman error 404 Not Found.

| ID KASUS | SKENARIO | DATA INPUT | HASIL AKTUAL | STATUS |
| :---: | :--- | :---: | :--- | :---: |
| **010** | Input ID laporan yang terdaftar di database | `id = 1` (Ada di database) | Sistem menerima permintaan dan menampilkan rincian laporan | **Valid** |
| **011** | Input ID laporan yang tidak ada di database | `id = 99999` (Tidak ada di DB) | Sistem menolak permintaan dan memunculkan error 404 Not Found | **Tidak Valid** |
| **012** | Input ID laporan di bawah batas minimal angka | `id = 0` atau `id = -1` | Sistem menolak permintaan dan memunculkan error 404 Not Found | **Tidak Valid** |
| **013** | Input ID laporan berupa teks karakter | `id = "laporan-malam"` | Sistem menolak permintaan dan memunculkan error 404 Not Found | **Tidak Valid** |

---

### 4. FITUR: PENENTUAN SAPAAN WAKTU (Menu Dashboard)

**RULE:**
- **Valid (Waktu Pagi):**  
  Data Input Jam $00 \le Jam < 11$, Sistem menampilkan sapaan *"Selamat pagi"*.
- **Valid (Waktu Siang):**  
  Data Input Jam $11 \le Jam < 15$, Sistem menampilkan sapaan *"Selamat siang"*.
- **Valid (Waktu Sore):**  
  Data Input Jam $15 \le Jam < 18$, Sistem menampilkan sapaan *"Selamat sore"*.
- **Valid (Waktu Malam):**  
  Data Input Jam $18 \le Jam \le 23$, Sistem menampilkan sapaan *"Selamat malam"*.

| ID KASUS | SKENARIO | DATA INPUT | HASIL AKTUAL | STATUS |
| :---: | :--- | :---: | :--- | :---: |
| **014** | Jam sistem berada pada rentang waktu Pagi | `08:00` | Sistem menampilkan teks: *"Selamat pagi, [Nama Petugas]!"* | **Valid** |
| **015** | Jam sistem tepat batas atas waktu Pagi | `10:59` | Sistem menampilkan teks: *"Selamat pagi, [Nama Petugas]!"* | **Valid** |
| **016** | Jam sistem tepat batas awal waktu Siang | `11:00` | Sistem menampilkan teks: *"Selamat siang, [Nama Petugas]!"* | **Valid** |
| **017** | Jam sistem berada pada rentang waktu Siang | `13:30` | Sistem menampilkan teks: *"Selamat siang, [Nama Petugas]!"* | **Valid** |
| **018** | Jam sistem tepat batas awal waktu Sore | `15:00` | Sistem menampilkan teks: *"Selamat sore, [Nama Petugas]!"* | **Valid** |
| **019** | Jam sistem tepat batas akhir waktu Sore | `17:59` | Sistem menampilkan teks: *"Selamat sore, [Nama Petugas]!"* | **Valid** |
| **020** | Jam sistem tepat batas awal waktu Malam | `18:00` | Sistem menampilkan teks: *"Selamat malam, [Nama Petugas]!"* | **Valid** |
| **021** | Jam sistem berada pada rentang waktu Malam | `22:00` | Sistem menampilkan teks: *"Selamat malam, [Nama Petugas]!"* | **Valid** |

---

### 5. FITUR: FILTER DATA PETUGAS AKTIF (Menu Petugas)

**RULE:**
- **Valid (Petugas Berstatus Aktif):**  
  Data Petugas memiliki status `is_active = '1'`, Sistem akan menampilkan data pada tabel daftar petugas aktif.
- **Invalid / Terfilter (Petugas Nonaktif):**  
  Data Petugas memiliki status `is_active = '0'`, Sistem menolak menampilkan data tersebut pada tabel petugas aktif.

| ID KASUS | SKENARIO | DATA INPUT | HASIL AKTUAL | STATUS |
| :---: | :--- | :---: | :--- | :---: |
| **022** | Menampilkan data petugas dengan status aktif | Status `is_active = '1'` | Sistem menampilkan data petugas pada tabel dengan badge hijau "Aktif" | **Valid** |
| **023** | Memfilter petugas dengan status dinonaktifkan | Status `is_active = '0'` | Sistem tidak menampilkan data petugas tersebut di dalam tabel | **Tidak Valid** |

---

### 6. FITUR: STATUS PERINGATAN LAPORAN HARI INI (Menu Dashboard)

**RULE:**
- **Valid (Sudah Mengirim Laporan):**  
  Data Input Jumlah Laporan Hari Ini $\ge$ 1, Sistem menampilkan jumlah laporan terkirim dan tidak memunculkan kotak peringatan bahaya (*alert-danger*).
- **Invalid / Kondisi Peringatan (Belum Laporan):**  
  Data Input Jumlah Laporan Hari Ini $= 0$, Sistem memunculkan kotak peringatan berwarna merah: *"⚠️ Anda belum mengirim laporan hari ini."*

| ID KASUS | SKENARIO | DATA INPUT | HASIL AKTUAL | STATUS |
| :---: | :--- | :---: | :--- | :---: |
| **024** | Petugas telah mengirim minimal 1 laporan hari ini | Total Laporan Hari Ini = 1 | Sistem menampilkan *"Laporan Terkirim: 1 laporan"*, tidak muncul kotak peringatan bahaya | **Valid** |
| **025** | Petugas mengirim lebih dari 1 laporan hari ini | Total Laporan Hari Ini = 3 | Sistem menampilkan *"Laporan Terkirim: 3 laporan"*, tidak muncul kotak peringatan bahaya | **Valid** |
| **026** | Petugas belum mengirimkan laporan sama sekali hari ini | Total Laporan Hari Ini = 0 | Sistem memunculkan kotak peringatan berwarna merah: *"⚠️ Anda belum mengirim laporan hari ini."* | **Tidak Valid** |

---

### 7. FITUR: REKAPITULASI KEAKTIFAN LAPORAN (Menu Rekap)

**RULE:**
- **Valid (Petugas Memiliki Kontribusi Laporan):**  
  Data Input Total Laporan Petugas $\ge$ 1, Sistem menampilkan nama petugas beserta total laporannya pada tabel, diurutkan dari yang terbanyak (*descending*).
- **Invalid / Terfilter (Petugas Belum Pernah Lapor):**  
  Data Input Total Laporan Petugas $= 0$, Sistem tidak menampilkan petugas tersebut di dalam tabel rekapitulasi keaktifan.

| ID KASUS | SKENARIO | DATA INPUT | HASIL AKTUAL | STATUS |
| :---: | :--- | :---: | :--- | :---: |
| **027** | Petugas dengan jumlah kontribusi laporan terbanyak | Petugas A = 10 Laporan,<br>Petugas B = 4 Laporan | Sistem menampilkan Petugas A pada urutan teratas (ranking 1) dengan total 10 laporan | **Valid** |
| **028** | Petugas dengan kontribusi minimal 1 laporan | Petugas C = 1 Laporan | Sistem menampilkan Petugas C dengan total 1 laporan | **Valid** |
| **029** | Petugas terdaftar namun belum pernah mengirim laporan | Petugas D = 0 Laporan | Sistem tidak menampilkan baris Petugas D di tabel rekapitulasi | **Tidak Valid** |

---

### 8. FITUR: PROTEKSI AKSES HALAMAN INTERNAL (Middleware Autentikasi)

**RULE:**
- **Valid (Sesi Pengguna Terautentikasi):**  
  Pengguna memiliki sesi login aktif (`auth:petugas`), Sistem memberikan izin akses penuh ke menu dalam (`/dashboard`, `/plot`, `/laporan`, `/rekap`, `/petugas`).
- **Invalid (Pengguna Tamu / Tanpa Sesi Login):**  
  Pengguna tidak memiliki sesi login (Guest), Sistem menolak akses dan otomatis mengalihkan (*redirect*) ke halaman `/login`.

| ID KASUS | SKENARIO | DATA INPUT (STATUS SESI) | HASIL AKTUAL | STATUS |
| :---: | :--- | :---: | :--- | :---: |
| **030** | Mengakses menu Dashboard dengan sesi aktif | Sesi `auth:petugas` aktif | Sistem mengizinkan akses dan menampilkan halaman Dashboard | **Valid** |
| **031** | Mengakses menu Jadwal (`/plot`) tanpa login | Sesi kosong (Guest) | Sistem menolak akses dan langsung mengalihkan ke halaman `/login` | **Tidak Valid** |
| **032** | Mengakses menu Laporan (`/laporan`) tanpa login | Sesi kosong (Guest) | Sistem menolak akses dan langsung mengalihkan ke halaman `/login` | **Tidak Valid** |
| **033** | Mengakses menu Rekap (`/rekap`) tanpa login | Sesi kosong (Guest) | Sistem menolak akses dan langsung mengalihkan ke halaman `/login` | **Tidak Valid** |
| **034** | Mengakses menu Petugas (`/petugas`) tanpa login | Sesi kosong (Guest) | Sistem menolak akses dan langsung mengalihkan ke halaman `/login` | **Tidak Valid** |

---

### 9. FITUR: JADWAL RONDA USER HARI INI (Menu Dashboard)

**RULE:**
- **Valid (User Memiliki Giliran Ronda Hari Ini):**  
  Data Input Jadwal User pada Hari Ini $\ge$ 1, Sistem menampilkan keterangan giliran ronda (misal: *"Jadwal Ronda: 1 kali"*).
- **Valid (User Tidak Ada Giliran Ronda Hari Ini):**  
  Data Input Jadwal User pada Hari Ini $= 0$, Sistem menampilkan *"Jadwal Ronda: 0 kali"*.

| ID KASUS | SKENARIO | DATA INPUT | HASIL AKTUAL | STATUS |
| :---: | :--- | :---: | :--- | :---: |
| **035** | Petugas yang sedang login memiliki jadwal pada hari ini | Jadwal Hari Ini = 1 | Sistem menampilkan keterangan: *"Jadwal Ronda: 1 kali"* pada kartu aktivitas | **Valid** |
| **036** | Petugas yang sedang login tidak memiliki jadwal pada hari ini | Jadwal Hari Ini = 0 | Sistem menampilkan keterangan: *"Jadwal Ronda: 0 kali"* pada kartu aktivitas | **Valid** |

