<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Laporan extends Model
{
    use HasFactory;

    protected $table = 'laporan_petugas';
    protected $primaryKey = 'id_lapor';

    protected $fillable = [
        'petugas_id',
        'plot_id',
        'tanggal_lapor',
        'kategori_situasi',
        'isi_laporan',
        'foto_bukti',
        'status_verifikasi',
    ];

    protected $casts = [
        'tanggal_lapor' => 'datetime',
    ];

    public function petugas()
    {
        return $this->belongsTo(Petugas::class, 'petugas_id', 'id_petugas');
    }

    public function plot()
    {
        return $this->belongsTo(Plot::class, 'plot_id', 'id_plot');
    }
}
