<?php

namespace Tests\Feature;

use App\Models\Petugas;
use App\Models\Plot;
use App\Services\AutoScheduleService;
use App\Services\PortalSettings;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RolePortalPresensiProfileTest extends TestCase
{
    use RefreshDatabase;

    private Petugas $superAdmin;
    private Petugas $admin;
    private Petugas $petugas;

    protected function setUp(): void
    {
        parent::setUp();

        $this->superAdmin = Petugas::create([
            'username'     => 'superadmin_t',
            'password'     => Hash::make('superadmin123'),
            'nama_lengkap' => 'Super Admin Test',
            'gender'       => 'M',
            'role'         => 'super_admin',
            'is_active'    => '1',
        ]);

        $this->admin = Petugas::create([
            'username'     => 'admin_t',
            'password'     => Hash::make('admin123'),
            'nama_lengkap' => 'Admin Test',
            'gender'       => 'M',
            'role'         => 'admin',
            'is_active'    => '1',
        ]);

        $this->petugas = Petugas::create([
            'username'     => 'petugas_t',
            'password'     => Hash::make('user123'),
            'nama_lengkap' => 'Petugas Test',
            'gender'       => 'M',
            'role'         => 'petugas',
            'is_active'    => '1',
        ]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        PortalSettings::reset();
        $autoPath = storage_path('app/' . AutoScheduleService::CONFIG_PATH);
        if (file_exists($autoPath)) {
            unlink($autoPath);
        }
        parent::tearDown();
    }

    /* =========================================================================
     * (a) RBAC Super Admin vs Admin vs Petugas (Role & Portal Settings)
     * ========================================================================= */

    public function test_hanya_super_admin_yang_dapat_mengubah_role(): void
    {
        // Petugas & Admin biasa ditolak 403
        $this->actingAs($this->petugas, 'petugas')
            ->patch("/petugas/{$this->petugas->id_petugas}/role", ['role' => 'admin'])
            ->assertStatus(403);

        $this->actingAs($this->admin, 'petugas')
            ->patch("/petugas/{$this->petugas->id_petugas}/role", ['role' => 'admin'])
            ->assertStatus(403);

        // Admin biasa juga ditolak 403 jika manipulasi role saat create petugas
        $this->actingAs($this->admin, 'petugas')
            ->post('/petugas', [
                'username'              => 'hack_admin',
                'password'              => 'secret123',
                'password_confirmation' => 'secret123',
                'nama_lengkap'          => 'Hack Admin',
                'gender'                => 'M',
                'role'                  => 'admin',
                'is_active'             => '1',
            ])
            ->assertStatus(403);

        // Super Admin berhasil menaikkan petugas -> admin
        $this->actingAs($this->superAdmin, 'petugas')
            ->patch("/petugas/{$this->petugas->id_petugas}/role", ['role' => 'admin'])
            ->assertSessionHas('success');

        $this->assertDatabaseHas('petugas', [
            'id_petugas' => $this->petugas->id_petugas,
            'role'       => 'admin',
        ]);

        // Super Admin berhasil menurunkan admin -> petugas
        $this->actingAs($this->superAdmin, 'petugas')
            ->patch("/petugas/{$this->petugas->id_petugas}/role", ['role' => 'petugas'])
            ->assertSessionHas('success');

        $this->assertDatabaseHas('petugas', [
            'id_petugas' => $this->petugas->id_petugas,
            'role'       => 'petugas',
        ]);
    }

    public function test_hanya_super_admin_yang_dapat_kelola_portal_settings(): void
    {
        Storage::fake('public');

        // Petugas & Admin biasa ditolak 403
        $this->actingAs($this->petugas, 'petugas')->get('/portal-settings')->assertStatus(403);
        $this->actingAs($this->admin, 'petugas')->get('/portal-settings')->assertStatus(403);
        $this->actingAs($this->admin, 'petugas')->post('/portal-settings', ['hero_title' => 'Test'])->assertStatus(403);

        // Super Admin dapat mengunggah gambar baru & reset ke default
        $bgFile = UploadedFile::fake()->image('desa_baru.jpg', 800, 450);
        $kegFile = UploadedFile::fake()->image('kegiatan_baru.png', 600, 400);

        $res = $this->actingAs($this->superAdmin, 'petugas')
            ->post('/portal-settings', [
                'hero_title'      => 'Portal Ronda RW 05',
                'hero_background' => $bgFile,
                'kegiatan_image'  => $kegFile,
            ]);

        $res->assertRedirect(route('portal.settings'));
        $settings = PortalSettings::get();
        $this->assertEquals('Portal Ronda RW 05', $settings['hero_title']);
        $this->assertStringStartsWith('storage/portal/', $settings['hero_background']);
        $this->assertStringStartsWith('storage/portal/', $settings['kegiatan_image']);

        // Pastikan tampil di halaman publik beranda
        $this->get('/')->assertStatus(200)->assertSee('Portal Ronda RW 05');

        // Reset ke gambar default
        $this->actingAs($this->superAdmin, 'petugas')
            ->delete('/portal-settings/reset')
            ->assertRedirect(route('portal.settings'));

        $resetSettings = PortalSettings::get();
        $this->assertEquals(PortalSettings::DEFAULT_HERO_BG, $resetSettings['hero_background']);
        $this->assertEquals(PortalSettings::DEFAULT_KEGIATAN_IMG, $resetSettings['kegiatan_image']);
    }

    /* =========================================================================
     * (b) Validasi Waktu Presensi Real-Time (Carbon::setTestNow)
     * ========================================================================= */

    public function test_validasi_presensi_realtime_sesuai_hari_dan_jam_shift(): void
    {
        // Jadwal hari Selasa, Shift Malam (22:00 - 04:00) -> 10 Nov 2026 adalah Selasa
        $plot = Plot::create([
            'petugas_id'       => $this->petugas->id_petugas,
            'nama_hari'        => 'Selasa',
            'tanggal_ronda'    => '2026-11-10',
            'pos_jaga'         => 'Pos Utama',
            'shift'            => 'Malam (22:00 - 04:00)',
            'is_leader'        => '0',
            'is_active'        => '1',
            'status_kehadiran' => 'Terjadwal',
        ]);

        // 0. Selasa dini hari pukul 02:04 WIB (jam 00:00 - 04:00 pada hari jadwalnya) -> Hadir diizinkan
        Carbon::setTestNow(Carbon::create(2026, 11, 10, 2, 4, 0, 'Asia/Jakarta'));
        $this->actingAs($this->petugas, 'petugas')
            ->patch("/plot/{$plot->id_plot}/presensi", ['status_kehadiran' => 'Hadir'])
            ->assertSessionHas('success');
        $this->assertDatabaseHas('plot_ronda', ['id_plot' => $plot->id_plot, 'status_kehadiran' => 'Hadir']);
        $plot->refresh()->update(['status_kehadiran' => 'Terjadwal']);

        // 1. Selasa siang pukul 14:00 WIB (sebelum jam shift 22:00) -> Hadir & Izin ditolak
        Carbon::setTestNow(Carbon::create(2026, 11, 10, 14, 0, 0, 'Asia/Jakarta'));
        $this->actingAs($this->petugas, 'petugas')
            ->patch("/plot/{$plot->id_plot}/presensi", ['status_kehadiran' => 'Hadir'])
            ->assertSessionHas('error');
        $this->actingAs($this->petugas, 'petugas')
            ->patch("/plot/{$plot->id_plot}/presensi", ['status_kehadiran' => 'Izin'])
            ->assertSessionHas('error');
        $this->assertDatabaseHas('plot_ronda', ['id_plot' => $plot->id_plot, 'status_kehadiran' => 'Terjadwal']);

        // 2. Selasa malam pukul 22:30 WIB (masuk jam shift 22:00 - 04:00) -> Izin diizinkan
        Carbon::setTestNow(Carbon::create(2026, 11, 10, 22, 30, 0, 'Asia/Jakarta'));
        $this->actingAs($this->petugas, 'petugas')
            ->patch("/plot/{$plot->id_plot}/presensi", ['status_kehadiran' => 'Izin'])
            ->assertSessionHas('success');
        $this->assertDatabaseHas('plot_ronda', ['id_plot' => $plot->id_plot, 'status_kehadiran' => 'Izin']);

        // 3. Rabu dini hari pukul 02:30 WIB (masih rentang 22:00 - 04:00) -> Hadir diizinkan
        Carbon::setTestNow(Carbon::create(2026, 11, 11, 2, 30, 0, 'Asia/Jakarta'));
        $this->actingAs($this->petugas, 'petugas')
            ->patch("/plot/{$plot->id_plot}/presensi", ['status_kehadiran' => 'Hadir'])
            ->assertSessionHas('success');
        $this->assertDatabaseHas('plot_ronda', ['id_plot' => $plot->id_plot, 'status_kehadiran' => 'Hadir']);

        // 4. Rabu pagi pukul 08:00 WIB (sudah lewat 04:00) -> Izin & Hadir ditolak
        Carbon::setTestNow(Carbon::create(2026, 11, 11, 8, 0, 0, 'Asia/Jakarta'));
        $this->actingAs($this->petugas, 'petugas')
            ->patch("/plot/{$plot->id_plot}/presensi", ['status_kehadiran' => 'Izin'])
            ->assertSessionHas('error');
        $this->actingAs($this->petugas, 'petugas')
            ->patch("/plot/{$plot->id_plot}/presensi", ['status_kehadiran' => 'Hadir'])
            ->assertSessionHas('error');
    }

    /* =========================================================================
     * (c) Auto-Rotate Mingguan Setelah Hari Terpilih Selesai
     * ========================================================================= */

    public function test_auto_rotate_mingguan_terpicu_setelah_shift_hari_reset_selesai(): void
    {
        // Buat slot lama dengan status Hadir pada Sabtu 14 Nov 2026
        $oldPlot = Plot::create([
            'petugas_id'       => $this->petugas->id_petugas,
            'nama_hari'        => 'Sabtu',
            'tanggal_ronda'    => '2026-11-14',
            'pos_jaga'         => 'Pos Utama',
            'shift'            => 'Malam (22:00 - 04:00)',
            'is_leader'        => '1',
            'is_active'        => '1',
            'status_kehadiran' => 'Hadir',
        ]);

        AutoScheduleService::saveConfig([
            'auto_rotate_enabled' => true,
            'reset_after_day'     => 'Sabtu',
            'last_rotated_cycle'  => '2026-11-07', // siklus minggu sebelumnya
        ]);

        // Sabtu 14 Nov 2026 pukul 23:00 WIB -> shift belum selesai (selesai Minggu 04:00), belum rotate
        Carbon::setTestNow(Carbon::create(2026, 11, 14, 23, 0, 0, 'Asia/Jakarta'));
        $this->actingAs($this->admin, 'petugas')->get('/plot')->assertStatus(200);
        $this->assertDatabaseHas('plot_ronda', ['id_plot' => $oldPlot->id_plot, 'status_kehadiran' => 'Hadir']);

        // Minggu 15 Nov 2026 pukul 04:30 WIB -> sudah melewati 04:00, auto-rotate terpicu!
        Carbon::setTestNow(Carbon::create(2026, 11, 15, 4, 30, 0, 'Asia/Jakarta'));
        $this->actingAs($this->admin, 'petugas')->get('/plot')->assertStatus(200);

        $configAfter = AutoScheduleService::getConfig();
        $this->assertEquals('2026-11-14', $configAfter['last_rotated_cycle']);
        // Jadwal baru telah digenerate dan status kehadiran kembali ke Terjadwal
        $this->assertGreaterThan(1, Plot::count());
        $this->assertEquals(0, Plot::where('status_kehadiran', 'Hadir')->count());
    }

    /* =========================================================================
     * (d) Update Profil Mandiri
     * ========================================================================= */

    public function test_user_dapat_update_profil_dan_password_mandiri(): void
    {
        $this->actingAs($this->petugas, 'petugas')
            ->get('/profile')
            ->assertStatus(200)
            ->assertSee('Petugas Test');

        // Update data profil tanpa ganti password
        $this->actingAs($this->petugas, 'petugas')
            ->put('/profile', [
                'username'     => 'petugas_baru',
                'nama_lengkap' => 'Petugas Diperbarui',
                'no_hp'        => '081233344455',
                'alamat'       => 'RT 03 / Blok C',
                'gender'       => 'M',
            ])
            ->assertRedirect(route('profile.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('petugas', [
            'id_petugas'   => $this->petugas->id_petugas,
            'username'     => 'petugas_baru',
            'nama_lengkap' => 'Petugas Diperbarui',
        ]);

        // Ganti password dengan current_password salah -> ditolak
        $this->actingAs($this->petugas, 'petugas')
            ->put('/profile', [
                'username'                  => 'petugas_baru',
                'nama_lengkap'              => 'Petugas Diperbarui',
                'gender'                    => 'M',
                'current_password'          => 'salah123',
                'new_password'              => 'passwordBaru123',
                'new_password_confirmation' => 'passwordBaru123',
            ])
            ->assertSessionHasErrors('current_password');

        // Ganti password dengan current_password benar -> berhasil
        $this->actingAs($this->petugas, 'petugas')
            ->put('/profile', [
                'username'                  => 'petugas_baru',
                'nama_lengkap'              => 'Petugas Diperbarui',
                'gender'                    => 'M',
                'current_password'          => 'user123',
                'new_password'              => 'passwordBaru123',
                'new_password_confirmation' => 'passwordBaru123',
            ])
            ->assertSessionHas('success');

        $this->petugas->refresh();
        $this->assertTrue(Hash::check('passwordBaru123', $this->petugas->password));
    }
}
