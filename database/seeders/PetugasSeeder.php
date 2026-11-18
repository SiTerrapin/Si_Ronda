<?php

namespace Database\Seeders;

use App\Models\Laporan;
use App\Models\Petugas;
use App\Models\Plot;
use App\Models\Rekap;
use App\Services\AutoScheduleService;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class PetugasSeeder extends Seeder
{
    public function run(): void
    {
        // Super Admin
        Petugas::updateOrCreate(
            ['username' => 'superadmin'],
            [
                'password'     => Hash::make('superadmin123'),
                'nama_lengkap' => 'Super Admin SiRonda',
                'no_hp'        => '081200000000',
                'alamat'       => 'Kantor Sekretariat RW',
                'gender'       => 'M',
                'role'         => 'super_admin',
                'is_active'    => '1',
            ]
        );

        // Admin / Pengurus RT
        $admin = Petugas::updateOrCreate(
            ['username' => 'admin'],
            [
                'password'     => Hash::make('admin123'),
                'nama_lengkap' => 'Admin Ronda',
                'no_hp'        => '081234567890',
                'alamat'       => 'RT 01 / Pos Utama',
                'gender'       => 'M',
                'role'         => 'admin',
                'is_active'    => '1',
            ]
        );

        // Tester (Petugas)
        Petugas::updateOrCreate(
            ['username' => 'tester'],
            [
                'password'     => Hash::make('user123'),
                'nama_lengkap' => 'User Tester',
                'no_hp'        => '081234567891',
                'alamat'       => 'RT 01 / Blok A',
                'gender'       => 'F',
                'role'         => 'petugas',
                'is_active'    => '1',
            ]
        );

        // 21 Petugas aktif dengan nama warga acak & password default user123
        $daftarPetugas = [
            ['nama' => 'Budi Santoso',    'gender' => 'M'],
            ['nama' => 'Siti Aminah',     'gender' => 'F'],
            ['nama' => 'Agus Pratama',    'gender' => 'M'],
            ['nama' => 'Dewi Lestari',    'gender' => 'F'],
            ['nama' => 'Hendra Wijaya',   'gender' => 'M'],
            ['nama' => 'Rina Kartika',    'gender' => 'F'],
            ['nama' => 'Eko Prasetyo',    'gender' => 'M'],
            ['nama' => 'Sri Wahyuni',     'gender' => 'F'],
            ['nama' => 'Bambang Sutrisno','gender' => 'M'],
            ['nama' => 'Nurul Hidayah',   'gender' => 'F'],
            ['nama' => 'Joko Susilo',     'gender' => 'M'],
            ['nama' => 'Ratna Sari',      'gender' => 'F'],
            ['nama' => 'Dedi Kurniawan',  'gender' => 'M'],
            ['nama' => 'Yuni Astuti',     'gender' => 'F'],
            ['nama' => 'Rudi Hartono',    'gender' => 'M'],
            ['nama' => 'Ani Suryani',     'gender' => 'F'],
            ['nama' => 'Fajar Nugroho',   'gender' => 'M'],
            ['nama' => 'Maya Puspita',    'gender' => 'F'],
            ['nama' => 'Arif Rahman',     'gender' => 'M'],
            ['nama' => 'Indah Permata',   'gender' => 'F'],
            ['nama' => 'Taufik Hidayat',  'gender' => 'M'],
        ];

        $petugasList = [];
        foreach ($daftarPetugas as $idx => $data) {
            $i = $idx + 1;
            $rt = ($idx % 3) + 1;
            $blok = ['A', 'B', 'C'][$idx % 3];
            $petugasList[] = Petugas::updateOrCreate(
                ['username' => 'petugas' . $i],
                [
                    'password'     => Hash::make('user123'),
                    'nama_lengkap' => $data['nama'],
                    'no_hp'        => '081300000' . str_pad((string) $i, 3, '0', STR_PAD_LEFT),
                    'alamat'       => 'RT 0' . $rt . ' / Blok ' . $blok,
                    'gender'       => $data['gender'],
                    'role'         => 'petugas',
                    'is_active'    => '1',
                ]
            );
        }

        // Seed jadwal awal jika belum ada agar halaman publik langsung informatif
        if (Plot::count() === 0) {
            $hariList = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
            $posList = ['Pos Utama', 'Pos Utara', 'Pos Selatan'];
            $startOfWeek = Carbon::now('Asia/Jakarta')->startOfWeek(Carbon::MONDAY);
            $index = 0;

            foreach ($hariList as $dayOffset => $hari) {
                $tanggalHari = $startOfWeek->copy()->addDays($dayOffset)->toDateString();
                for ($i = 0; $i < 3; $i++) {
                    Plot::create([
                        'petugas_id'       => $petugasList[$index]->id_petugas,
                        'nama_hari'        => $hari,
                        'tanggal_ronda'    => $tanggalHari,
                        'pos_jaga'         => $posList[$i],
                        'shift'            => 'Malam (22:00 - 04:00)',
                        'is_leader'        => $i === 0 ? '1' : '0',
                        'is_active'        => '1',
                        'status_kehadiran' => 'Terjadwal',
                    ]);
                    $index++;
                }
            }
        }

        // Tandai siklus minggu lalu agar jadwal awal tidak langsung diacak saat baru di-seed
        $prevSaturday = Carbon::now('Asia/Jakarta')->startOfWeek(Carbon::MONDAY)->subWeek()->addDays(5)->toDateString();
        AutoScheduleService::saveConfig([
            'auto_rotate_enabled' => true,
            'reset_after_day'     => 'Sabtu',
            'last_rotated_cycle'  => $prevSaturday,
        ]);

        // Seed contoh laporan terverifikasi & pending
        if (Laporan::count() === 0) {
            Laporan::create([
                'petugas_id'        => $petugasList[0]->id_petugas,
                'tanggal_lapor'     => Carbon::now()->subDays(1)->setTime(23, 30),
                'kategori_situasi'  => 'Aman',
                'isi_laporan'       => 'Patroli keliling blok A sampai C berjalan aman, seluruh portal komplek terkunci pada pukul 23:00 WIB.',
                'status_verifikasi' => 'Diverifikasi',
            ]);

            Laporan::create([
                'petugas_id'        => $petugasList[1]->id_petugas,
                'tanggal_lapor'     => Carbon::now()->subHours(2),
                'kategori_situasi'  => 'Mencurigakan',
                'isi_laporan'       => 'Ditemukan kendaraan tidak dikenal parkir cukup lama di depan gang B3, sudah dihampiri dan ternyata tamu warga.',
                'status_verifikasi' => 'Pending',
            ]);
        }

        // Seed contoh rekap harian
        if (Rekap::count() === 0) {
            Rekap::create([
                'petugas_id'              => $admin->id_petugas,
                'tanggal_rekap'           => Carbon::yesterday()->toDateString(),
                'total_petugas_terjadwal' => 3,
                'total_petugas_hadir'     => 3,
                'total_petugas_ijin'      => 0,
                'total_petugas_alpha'     => 0,
                'status_keamanan'         => 'Aman',
                'isi_rekap'               => 'Seluruh petugas hadir lengkap. Situasi lingkungan kondusif hingga pagi.',
            ]);
        }
    }
}
