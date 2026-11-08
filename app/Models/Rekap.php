<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;


class Rekap extends Model
{
    protected $table = 'laporan_petugas';

    public static function laporanPerPetugas()
    {
        return DB::table('laporan_petugas')
            ->select('petugas_id', DB::raw('COUNT(*) as total'))
            ->groupBy('petugas_id')
            ->get();
    }

}
