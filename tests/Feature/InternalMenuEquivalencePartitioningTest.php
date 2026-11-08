<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Petugas;
use App\Models\Plot;
use App\Models\Laporan;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

class InternalMenuEquivalencePartitioningTest extends TestCase
{
    use RefreshDatabase;

    private Petugas $petugasAktif;

    protected function setUp(): void
    {
        parent::setUp();

        // Buat petugas aktif default untuk sesi login menu dalam
        $this->petugasAktif = Petugas::create([
            'username'     => 'petugas_utama',
            'password'     => Hash::make('password123'),
            'nama_lengkap' => 'Budi Santoso',
            'gender'       => 'M',
            'is_active'    => '1',
        ]);
    }

    /* =========================================================================
     * 1. MENU JADWAL RONDA (/plot) - Penjadwalan Otomatis
     * ========================================================================= */

    /**
     * JDW-001 & JDW-002: Partisi Valid (Petugas Aktif >= 21)
     */
    public function test_penjadwalan_partisi_valid_petugas_cukup(): void
    {
        // Total petugas aktif: 1 (setUp) + 20 (factory) = 21 petugas
        Petugas::factory()->count(20)->create(['is_active' => '1']);

        $response = $this->actingAs($this->petugasAktif, 'petugas')
            ->post('/plot/schedule');

        $response->assertRedirect(route('plot.index'));
        $response->assertSessionHas('success', 'Penjadwalan otomatis berhasil.');
        $this->assertEquals(21, Plot::count());
        $this->assertEquals(7, Plot::where('is_leader', '1')->count());
    }

    /**
     * JDW-003 & JDW-004: Partisi Invalid (Petugas Aktif < 21)
     */
    public function test_penjadwalan_partisi_invalid_petugas_kurang(): void
    {
        // Hanya 1 petugas (setUp) + 10 = 11 petugas (< 21)
        Petugas::factory()->count(10)->create(['is_active' => '1']);

        $response = $this->actingAs($this->petugasAktif, 'petugas')
            ->post('/plot/schedule');

        $response->assertSessionHas('error', 'Jumlah petugas aktif tidak cukup untuk penjadwalan.');
        $this->assertEquals(0, Plot::count());
    }

    /* =========================================================================
     * 2. MENU LAPORAN RONDA (/laporan) - Form Kirim Laporan
     * ========================================================================= */

    /**
     * LAP-001 & LAP-002: Partisi Valid (Teks Laporan Terisi)
     */
    public function test_kirim_laporan_partisi_valid(): void
    {
        $response = $this->actingAs($this->petugasAktif, 'petugas')
            ->post('/laporan', [
                'isi_laporan' => 'Pukul 02:00 situasi pos ronda aman terkendali.',
            ]);

        $response->assertSessionHas('success', 'Laporan berhasil disimpan.');
        $this->assertDatabaseHas('laporan_petugas', [
            'petugas_id'  => $this->petugasAktif->id_petugas,
            'isi_laporan' => 'Pukul 02:00 situasi pos ronda aman terkendali.',
        ]);
    }

    /**
     * LAP-003: Partisi Invalid (Isi Laporan Kosong)
     */
    public function test_kirim_laporan_partisi_invalid_kosong(): void
    {
        $response = $this->actingAs($this->petugasAktif, 'petugas')
            ->post('/laporan', [
                'isi_laporan' => '',
            ]);

        $response->assertSessionHasErrors('isi_laporan');
        $this->assertEquals(0, Laporan::count());
    }

    /* =========================================================================
     * 3. MENU LAPORAN RONDA (/laporan/{id}) - Detail Laporan
     * ========================================================================= */

    /**
     * DET-001: Partisi Valid (ID Terdaftar)
     */
    public function test_detail_laporan_partisi_valid_id_terdaftar(): void
    {
        $laporan = Laporan::create([
            'petugas_id'    => $this->petugasAktif->id_petugas,
            'tanggal_lapor' => now(),
            'isi_laporan'   => 'Laporan ronda malam hari ini.',
        ]);

        $response = $this->actingAs($this->petugasAktif, 'petugas')
            ->get('/laporan/' . $laporan->id_lapor);

        $response->assertStatus(200);
        $response->assertSee('Laporan ronda malam hari ini.');
    }

    /**
     * DET-002: Partisi Invalid (ID Tidak Ditemukan -> 404)
     */
    public function test_detail_laporan_partisi_invalid_id_tidak_ada(): void
    {
        $response = $this->actingAs($this->petugasAktif, 'petugas')
            ->get('/laporan/99999');

        $response->assertStatus(404);
    }

    /* =========================================================================
     * 4. MENU DASHBOARD (/dashboard) - Logika Sapaan Waktu 24 Jam
     * ========================================================================= */

    /**
     * SAP-001: Partisi Pagi (08:00)
     */
    public function test_dashboard_sapaan_pagi(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 11, 8, 8, 0, 0));

        $response = $this->actingAs($this->petugasAktif, 'petugas')
            ->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Selamat pagi, Budi Santoso!');
    }

    /**
     * SAP-004: Partisi Siang (13:00)
     */
    public function test_dashboard_sapaan_siang(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 11, 8, 13, 0, 0));

        $response = $this->actingAs($this->petugasAktif, 'petugas')
            ->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Selamat siang, Budi Santoso!');
    }

    /**
     * SAP-005: Partisi Sore (16:00)
     */
    public function test_dashboard_sapaan_sore(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 11, 8, 16, 0, 0));

        $response = $this->actingAs($this->petugasAktif, 'petugas')
            ->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Selamat sore, Budi Santoso!');
    }

    /**
     * SAP-008: Partisi Malam (21:00)
     */
    public function test_dashboard_sapaan_malam(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 11, 8, 21, 0, 0));

        $response = $this->actingAs($this->petugasAktif, 'petugas')
            ->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Selamat malam, Budi Santoso!');
    }

    /* =========================================================================
     * 5. MENU PETUGAS (/petugas) - Filter Keaktifan Petugas
     * ========================================================================= */

    /**
     * PTG-001 & PTG-002: Hanya Menampilkan Petugas Aktif
     */
    public function test_menu_petugas_hanya_tampilkan_petugas_aktif(): void
    {
        Petugas::create([
            'username'     => 'petugas_nonaktif_1',
            'password'     => Hash::make('password123'),
            'nama_lengkap' => 'Warga Yang Sudah Nonaktif',
            'gender'       => 'M',
            'is_active'    => '0', // Nonaktif
        ]);

        $response = $this->actingAs($this->petugasAktif, 'petugas')
            ->get('/petugas');

        $response->assertStatus(200);
        $response->assertSee('Budi Santoso'); // Aktif
        $response->assertDontSee('Warga Yang Sudah Nonaktif'); // Terfilter
    }

    /* =========================================================================
     * 6. PROTEKSI AKSES MENU INTERNAL (Middleware auth:petugas)
     * ========================================================================= */

    /**
     * SEC-002 s/d SEC-005: Guest Ditolak Masuk ke Menu Dalam
     */
    public function test_guest_tidak_bisa_akses_menu_dalam(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
        $this->get('/plot')->assertRedirect('/login');
        $this->get('/laporan')->assertRedirect('/login');
        $this->get('/rekap')->assertRedirect('/login');
        $this->get('/petugas')->assertRedirect('/login');
    }
}

