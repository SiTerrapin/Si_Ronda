<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Petugas;
use App\Models\Plot;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

class EquivalencePartitioningTest extends TestCase
{
    use RefreshDatabase;

    /* =========================================================================
     * 1. REGISTRASI PETUGAS (AuthController::register)
     * ========================================================================= */

    /**
     * TC-REG-01: Partisi Valid
     * Registrasi dengan seluruh data valid dan unik.
     */
    public function test_registrasi_partisi_valid(): void
    {
        $response = $this->post('/register', [
            'username'              => 'budi_santoso',
            'password'              => 'rahasia123',
            'password_confirmation' => 'rahasia123',
            'nama_lengkap'          => 'Budi Santoso',
            'gender'                => 'M',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('petugas', [
            'username'     => 'budi_santoso',
            'nama_lengkap' => 'Budi Santoso',
            'gender'       => 'M',
            'is_active'    => '1',
        ]);
    }

    /**
     * TC-REG-02: Partisi Invalid (Username Duplikat)
     */
    public function test_registrasi_partisi_username_duplikat(): void
    {
        Petugas::create([
            'username'     => 'budi_santoso',
            'password'     => Hash::make('password123'),
            'nama_lengkap' => 'Budi Lama',
            'gender'       => 'M',
            'is_active'    => '1',
        ]);

        $response = $this->post('/register', [
            'username'              => 'budi_santoso',
            'password'              => 'passwordBaru123',
            'password_confirmation' => 'passwordBaru123',
            'nama_lengkap'          => 'Budi Baru',
            'gender'                => 'M',
        ]);

        $response->assertSessionHasErrors('username');
    }

    /**
     * TC-REG-05: BVA / Partisi Invalid (Password < 6 karakter)
     */
    public function test_registrasi_partisi_password_terlalu_pendek(): void
    {
        $response = $this->post('/register', [
            'username'              => 'andi123',
            'password'              => '12345',
            'password_confirmation' => '12345',
            'nama_lengkap'          => 'Andi Wijaya',
            'gender'                => 'M',
        ]);

        $response->assertSessionHasErrors('password');
    }

    /**
     * TC-REG-07: Partisi Invalid (Konfirmasi Password Tidak Cocok)
     */
    public function test_registrasi_partisi_password_mismatch(): void
    {
        $response = $this->post('/register', [
            'username'              => 'andi123',
            'password'              => 'rahasia123',
            'password_confirmation' => 'berbeda123',
            'nama_lengkap'          => 'Andi Wijaya',
            'gender'                => 'M',
        ]);

        $response->assertSessionHasErrors('password');
    }

    /**
     * TC-REG-08: Partisi Invalid (Gender di luar 'M' dan 'F')
     */
    public function test_registrasi_partisi_gender_invalid(): void
    {
        $response = $this->post('/register', [
            'username'              => 'andi123',
            'password'              => 'rahasia123',
            'password_confirmation' => 'rahasia123',
            'nama_lengkap'          => 'Andi Wijaya',
            'gender'                => 'X',
        ]);

        $response->assertSessionHasErrors('gender');
    }

    /* =========================================================================
     * 2. LOGIN PETUGAS (AuthController::login)
     * ========================================================================= */

    /**
     * TC-LOG-01: Partisi Valid
     * Login dengan username dan password benar serta status akun aktif.
     */
    public function test_login_partisi_valid(): void
    {
        $petugas = Petugas::create([
            'username'     => 'ronda_user',
            'password'     => Hash::make('password123'),
            'nama_lengkap' => 'Petugas Ronda',
            'gender'       => 'M',
            'is_active'    => '1',
        ]);

        $response = $this->post('/login', [
            'username' => 'ronda_user',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($petugas, 'petugas');
    }

    /**
     * TC-LOG-02: Partisi Invalid (Akun Nonaktif)
     */
    public function test_login_partisi_akun_nonaktif(): void
    {
        Petugas::create([
            'username'     => 'user_nonaktif',
            'password'     => Hash::make('password123'),
            'nama_lengkap' => 'Warga Nonaktif',
            'gender'       => 'M',
            'is_active'    => '0',
        ]);

        $response = $this->post('/login', [
            'username' => 'user_nonaktif',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors(['username' => 'Akun nonaktif']);
        $this->assertGuest('petugas');
    }

    /**
     * TC-LOG-03: Partisi Invalid (Password Salah)
     */
    public function test_login_partisi_password_salah(): void
    {
        Petugas::create([
            'username'     => 'ronda_user',
            'password'     => Hash::make('passwordBenar123'),
            'nama_lengkap' => 'Petugas Ronda',
            'gender'       => 'M',
            'is_active'    => '1',
        ]);

        $response = $this->post('/login', [
            'username' => 'ronda_user',
            'password' => 'passwordSalah',
        ]);

        $response->assertSessionHasErrors(['username' => 'Username atau password salah']);
        $this->assertGuest('petugas');
    }

    /* =========================================================================
     * 3. PENJADWALAN OTOMATIS (PlotController::autoSchedule)
     * ========================================================================= */

    /**
     * TC-SCH-01: BVA / Partisi Invalid (Petugas Kurang dari 21)
     */
    public function test_penjadwalan_partisi_petugas_kurang(): void
    {
        $petugasAdmin = Petugas::create([
            'username'     => 'admin',
            'password'     => Hash::make('admin123'),
            'nama_lengkap' => 'Admin Ronda',
            'gender'       => 'M',
            'is_active'    => '1',
        ]);

        // Buat hanya 10 petugas aktif (< 21)
        Petugas::factory()->count(10)->create(['is_active' => '1']);

        $response = $this->actingAs($petugasAdmin, 'petugas')
            ->post('/plot/schedule');

        $response->assertSessionHas('error', 'Jumlah petugas aktif tidak cukup untuk penjadwalan.');
        $this->assertEquals(0, Plot::count());
    }

    /**
     * TC-SCH-02: BVA / Partisi Valid (Tepat 21 Petugas Aktif)
     */
    public function test_penjadwalan_partisi_petugas_cukup_21(): void
    {
        $petugasAdmin = Petugas::create([
            'username'     => 'admin',
            'password'     => Hash::make('admin123'),
            'nama_lengkap' => 'Admin Ronda',
            'gender'       => 'M',
            'is_active'    => '1',
        ]);

        // Buat 20 lagi sehingga total tepat 21 petugas aktif
        Petugas::factory()->count(20)->create(['is_active' => '1']);

        $response = $this->actingAs($petugasAdmin, 'petugas')
            ->post('/plot/schedule');

        $response->assertRedirect(route('plot.index'));
        $response->assertSessionHas('success', 'Penjadwalan otomatis berhasil.');
        $this->assertEquals(21, Plot::count());

        // Pastikan ada 7 leader (1 leader per hari)
        $this->assertEquals(7, Plot::where('is_leader', '1')->count());
    }
}

