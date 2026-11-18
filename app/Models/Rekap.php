<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rekap extends Model
{
    use HasFactory;

    protected $table = 'rekap_ronda_harian';
    protected $primaryKey = 'id_rekap';

    protected $fillable = [
        'petugas_id',
        'tanggal_rekap',
        'total_petugas_terjadwal',
        'total_petugas_hadir',
        'total_petugas_ijin',
        'total_petugas_alpha',
        'status_keamanan',
        'isi_rekap',
    ];

    protected $casts = [
        'tanggal_rekap'           => 'date',
        'total_petugas_terjadwal' => 'integer',
        'total_petugas_hadir'     => 'integer',
        'total_petugas_ijin'      => 'integer',
        'total_petugas_alpha'     => 'integer',
    ];

    public function petugas()
    {
        return $this->belongsTo(Petugas::class, 'petugas_id', 'id_petugas');
    }
}
