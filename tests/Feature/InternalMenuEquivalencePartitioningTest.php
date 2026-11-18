<?php

namespace Tests\Feature;

use App\Models\Laporan;
use App\Models\Petugas;
use App\Models\Plot;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class InternalMenuEquivalencePartitioningTest extends TestCase
{
    use RefreshDatabase;

    private Petugas $adminUser;
    private Petugas $petugasBiasa;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = Petugas::create([
            'username'     => 'admin_test',
            'password'     => Hash::make('admin123'),
            'nama_lengkap' => 'Admin Ronda',
            'no_hp'        => '081234567890',
            'alamat'       => 'RT 01 / Pos Utama',
            'gender'       => 'M',
            'role'         => 'admin',
            'is_active'    => '1',
        ]);

        $this->petugasBiasa = Petugas::create([
            'username'     => 'petugas_utama',
            'password'     => Hash::make('password123'),
            'nama_lengkap' => 'Budi Santoso',
            'no_hp'        => '081298765432',
            'alamat'       => 'RT 02 / Blok B',
            'gender'       => 'M',
            'role'         => 'petugas',
            'is_active'    => '1',
        ]);
    }

    public function test_halaman_publik_dapat_diakses_tanpa_login(): void
    {
        $this->get('/')->assertStatus(200);
        $this->get('/jadwal')->assertStatus(200);
        $this->get('/anggota')->assertStatus(200);
        $this->get('/kegiatan')->assertStatus(200);
    }

    public function test_rbac_petugas_biasa_ditolak_akses_menu_admin(): void
    {
        $this->actingAs($this->petugasBiasa, 'petugas')->get('/petugas/create')->assertStatus(403);
        $this->actingAs($this->petugasBiasa, 'petugas')->get('/plot/create')->assertStatus(403);
        $this->actingAs($this->petugasBiasa, 'petugas')->post('/plot/schedule')->assertStatus(403);
        $this->actingAs($this->petugasBiasa, 'petugas')->get('/rekap')->assertStatus(403);
    }

    public function test_admin_dapat_kelola_petugas_dan_plot_anti_bentrok(): void
    {
        // Tambah plot pertama
        $res1 = $this->actingAs($this->adminUser, 'petugas')->post('/plot', [
            'petugas_id' => $this->petugasBiasa->id_petugas,
            'nama_hari'  => 'Senin',
            'pos_jaga'   => 'Pos Utama',
            'shift'      => 'Malam (22:00 - 04:00)',
            'is_leader'  => '1',
        ]);
        $res1->assertRedirect(route('plot.index'));

        // Tambah plot kedua untuk petugas sama di hari & shift yang sama -> ditolak (anti-bentrok)
        $res2 = $this->actingAs($this->adminUser, 'petugas')->post('/plot', [
            'petugas_id' => $this->petugasBiasa->id_petugas,
            'nama_hari'  => 'Senin',
            'pos_jaga'   => 'Pos Utara',
            'shift'      => 'Malam (22:00 - 04:00)',
            'is_leader'  => '0',
        ]);
        $res2->assertSessionHasErrors('petugas_id');
        $this->assertEquals(1, Plot::count());
    }

    public function test_petugas_dapat_mengisi_presensi_pada_jadwalnya_sendiri(): void
    {
        // Selasa 10 Nov 2026 pukul 23:00 WIB (masuk dalam shift Malam 22:00 - 04:00)
        Carbon::setTestNow(Carbon::create(2026, 11, 10, 23, 0, 0, 'Asia/Jakarta'));

        $plot = Plot::create([
            'petugas_id'       => $this->petugasBiasa->id_petugas,
            'nama_hari'        => 'Selasa',
            'pos_jaga'         => 'Pos Utama',
            'shift'            => 'Malam (22:00 - 04:00)',
            'is_leader'        => '0',
            'is_active'        => '1',
            'status_kehadiran' => 'Terjadwal',
        ]);

        $response = $this->actingAs($this->petugasBiasa, 'petugas')
            ->patch("/plot/{$plot->id_plot}/presensi", [
                'status_kehadiran' => 'Hadir',
            ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('plot_ronda', [
            'id_plot'          => $plot->id_plot,
            'status_kehadiran' => 'Hadir',
        ]);
    }

    public function test_kirim_laporan_dan_verifikasi_admin(): void
    {
        $resStore = $this->actingAs($this->petugasBiasa, 'petugas')
            ->post('/laporan', [
                'kategori_situasi' => 'Aman',
                'isi_laporan'      => 'Pukul 02:00 situasi pos ronda aman terkendali.',
            ]);

        $resStore->assertSessionHas('success', 'Laporan berhasil disimpan.');
        $laporan = Laporan::first();
        $this->assertEquals('Pending', $laporan->status_verifikasi);

        // Admin memverifikasi
        $resVerify = $this->actingAs($this->adminUser, 'petugas')
            ->patch("/laporan/{$laporan->id_lapor}/verify", [
                'status_verifikasi' => 'Diverifikasi',
            ]);

        $resVerify->assertSessionHas('success');
        $this->assertDatabaseHas('laporan_petugas', [
            'id_lapor'          => $laporan->id_lapor,
            'status_verifikasi' => 'Diverifikasi',
        ]);
    }

    public function test_kirim_laporan_partisi_invalid_kosong(): void
    {
        $response = $this->actingAs($this->petugasBiasa, 'petugas')
            ->post('/laporan', [
                'isi_laporan' => '',
            ]);

        $response->assertSessionHasErrors('isi_laporan');
        $this->assertEquals(0, Laporan::count());
    }

    public function test_detail_laporan_partisi_valid_dan_404(): void
    {
        $laporan = Laporan::create([
            'petugas_id'        => $this->petugasBiasa->id_petugas,
            'tanggal_lapor'     => now(),
            'kategori_situasi'  => 'Aman',
            'isi_laporan'       => 'Laporan ronda malam hari ini.',
            'status_verifikasi' => 'Pending',
        ]);

        $this->actingAs($this->petugasBiasa, 'petugas')
            ->get('/laporan/' . $laporan->id_lapor)
            ->assertStatus(200)
            ->assertSee('Laporan ronda malam hari ini.');

        $this->actingAs($this->petugasBiasa, 'petugas')
            ->get('/laporan/99999')
            ->assertStatus(404);
    }

    public function test_dashboard_sapaan_pagi_siang_sore_malam(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 11, 8, 8, 0, 0, 'Asia/Jakarta'));
        $this->actingAs($this->petugasBiasa, 'petugas')->get('/dashboard')->assertSee('Selamat pagi, Budi Santoso!');

        Carbon::setTestNow(Carbon::create(2026, 11, 8, 13, 0, 0, 'Asia/Jakarta'));
        $this->actingAs($this->petugasBiasa, 'petugas')->get('/dashboard')->assertSee('Selamat siang, Budi Santoso!');

        Carbon::setTestNow(Carbon::create(2026, 11, 8, 16, 0, 0, 'Asia/Jakarta'));
        $this->actingAs($this->petugasBiasa, 'petugas')->get('/dashboard')->assertSee('Selamat sore, Budi Santoso!');

        Carbon::setTestNow(Carbon::create(2026, 11, 8, 21, 0, 0, 'Asia/Jakarta'));
        $this->actingAs($this->petugasBiasa, 'petugas')->get('/dashboard')->assertSee('Selamat malam, Budi Santoso!');
    }

    public function test_menu_petugas_hanya_tampilkan_petugas_aktif_untuk_petugas_biasa(): void
    {
        Petugas::create([
            'username'     => 'petugas_nonaktif_1',
            'password'     => Hash::make('password123'),
            'nama_lengkap' => 'Warga Yang Sudah Nonaktif',
            'gender'       => 'M',
            'role'         => 'petugas',
            'is_active'    => '0',
        ]);

        $response = $this->actingAs($this->petugasBiasa, 'petugas')->get('/petugas');
        $response->assertStatus(200);
        $response->assertSee('Budi Santoso');
        $response->assertDontSee('Warga Yang Sudah Nonaktif');
    }

    public function test_admin_dapat_generate_rekap_harian(): void
    {
        $response = $this->actingAs($this->adminUser, 'petugas')
            ->post('/rekap/generate', [
                'tanggal_rekap'   => '2026-11-18',
                'status_keamanan' => 'Aman',
                'isi_rekap'       => 'Malam kondusif tanpa kendala.',
            ]);

        $response->assertRedirect(route('rekap.index'));
        $this->assertTrue(
            \App\Models\Rekap::whereDate('tanggal_rekap', '2026-11-18')
                ->where('status_keamanan', 'Aman')
                ->exists()
        );
    }

    public function test_guest_tidak_bisa_akses_menu_dalam(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
        $this->get('/plot')->assertRedirect('/login');
        $this->get('/laporan')->assertRedirect('/login');
        $this->get('/rekap')->assertRedirect('/login');
        $this->get('/petugas')->assertRedirect('/login');
    }
}
