<?php

namespace Database\Seeders;

use App\Models\Petugas;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class PetugasSeeder extends Seeder
{
    public function run(): void
    {
        // Admin
        Petugas::updateOrCreate(
            ['username' => 'admin'],
            [
                'password' => Hash::make('admin123'),
                'nama_lengkap' => 'Admin Ronda',
                'gender' => 'M',
                'is_active' => '1',
            ]
        );

        // Tester
        Petugas::updateOrCreate(
            ['username' => 'tester'],
            [
                'password' => Hash::make('tester123'),
                'nama_lengkap' => 'User Tester',
                'gender' => 'F',
                'is_active' => '1',
            ]
        );

        // 21 Petugas aktif
        for ($i = 1; $i <= 21; $i++) {
            Petugas::updateOrCreate(
                ['username' => 'petugas' . $i],
                [
                    'password' => Hash::make('password' . $i),
                    'nama_lengkap' => 'Petugas ' . $i,
                    'gender' => $i % 2 === 0 ? 'M' : 'F',
                    'is_active' => '1',
                ]
            );
        }
    }
}