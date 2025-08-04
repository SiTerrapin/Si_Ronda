<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
class AnggotaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('anggota')->insert([
            'nama'=>'ardian123',
            'password'=>'123',
            'alamat'=>'jalan jalan',
            'no_hp'=>'081231231',
            'is_active'=>'0',
            'nama_lengkap'=>'Bagas Putra Ardian',
            'gender'=>'M',
        ]);
    }
}
