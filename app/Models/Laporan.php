<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class Laporan extends Model
{
    use HasFactory;

    protected $table = 'laporan_petugas';
    protected $primaryKey = 'id_lapor';

    protected $fillable = [
        'petugas_id',
        'tanggal_lapor',
        'isi_laporan',
    ];

    public function petugas()
    {
        return $this->belongsTo(Petugas::class, 'petugas_id', 'id_petugas');
    }

    public $timestamps = true;
}
