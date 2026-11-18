<?php

namespace Tests\Feature;

use App\Models\Petugas;
use App\Models\Plot;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class EquivalencePartitioningTest extends TestCase
{
    use RefreshDatabase;

    /* =========================================================================
     * 1. REGISTRASI PETUGAS (Moderasi Aktivasi Admin)
     * ========================================================================= */

    public function test_registrasi_partisi_valid(): void
    {
        $response = $this->post('/register', [
            'username'              => 'budi_santoso',
            'password'              => 'rahasia123',
            'password_confirmation' => 'rahasia123',
            'nama_lengkap'          => 'Budi Santoso',
            'no_hp'                 => '081234567890',
            'alamat'                => 'RT 01 / Blok A',
            'gender'                => 'M',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('petugas', [
            'username'     => 'budi_santoso',
            'nama_lengkap' => 'Budi Santoso',
            'gender'       => 'M',
            'role'         => 'petugas',
            'is_active'    => '0',
        ]);
    }

    public function test_registrasi_partisi_username_duplikat(): void
    {
        Petugas::create([
            'username'     => 'budi_santoso',
            'password'     => Hash::make('password123'),
            'nama_lengkap' => 'Budi Lama',
            'gender'       => 'M',
            'role'         => 'petugas',
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
     * 2. LOGIN PETUGAS
     * ========================================================================= */

    public function test_login_partisi_valid(): void
    {
        $petugas = Petugas::create([
            'username'     => 'ronda_user',
            'password'     => Hash::make('password123'),
            'nama_lengkap' => 'Petugas Ronda',
            'gender'       => 'M',
            'role'         => 'petugas',
            'is_active'    => '1',
        ]);

        $response = $this->post('/login', [
            'username' => 'ronda_user',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($petugas, 'petugas');
    }

    public function test_login_partisi_akun_nonaktif(): void
    {
        Petugas::create([
            'username'     => 'user_nonaktif',
            'password'     => Hash::make('password123'),
            'nama_lengkap' => 'Warga Nonaktif',
            'gender'       => 'M',
            'role'         => 'petugas',
            'is_active'    => '0',
        ]);

        $response = $this->post('/login', [
            'username' => 'user_nonaktif',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors(['username' => 'Akun nonaktif']);
        $this->assertGuest('petugas');
    }

    public function test_login_partisi_password_salah(): void
    {
        Petugas::create([
            'username'     => 'ronda_user',
            'password'     => Hash::make('passwordBenar123'),
            'nama_lengkap' => 'Petugas Ronda',
            'gender'       => 'M',
            'role'         => 'petugas',
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
     * 3. PENJADWALAN OTOMATIS FLEKSIBEL (Admin Only)
     * ========================================================================= */

    public function test_penjadwalan_partisi_petugas_kurang_dari_21_tetap_proporsional(): void
    {
        $admin = Petugas::create([
            'username'     => 'admin',
            'password'     => Hash::make('admin123'),
            'nama_lengkap' => 'Admin Ronda',
            'gender'       => 'M',
            'role'         => 'admin',
            'is_active'    => '1',
        ]);

        Petugas::factory()->count(10)->create(['is_active' => '1']);

        $response = $this->actingAs($admin, 'petugas')
            ->post('/plot/schedule');

        $response->assertRedirect(route('plot.index'));
        $response->assertSessionHas('success');
        $this->assertEquals(21, Plot::count());
        $this->assertEquals(7, Plot::where('is_leader', '1')->count());
    }

    public function test_penjadwalan_partisi_petugas_cukup_21(): void
    {
        $admin = Petugas::create([
            'username'     => 'admin',
            'password'     => Hash::make('admin123'),
            'nama_lengkap' => 'Admin Ronda',
            'gender'       => 'M',
            'role'         => 'admin',
            'is_active'    => '1',
        ]);

        Petugas::factory()->count(20)->create(['is_active' => '1']);

        $response = $this->actingAs($admin, 'petugas')
            ->post('/plot/schedule');

        $response->assertRedirect(route('plot.index'));
        $response->assertSessionHas('success');
        $this->assertEquals(21, Plot::count());
        $this->assertEquals(7, Plot::where('is_leader', '1')->count());
    }
}
