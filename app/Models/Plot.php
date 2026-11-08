<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plot extends Model
{
    use HasFactory;

    protected $table = 'plot_ronda';
    protected $primaryKey = 'id_plot';

    protected $fillable = [
        'petugas_id',
        'nama_hari',
        'is_active',
        'is_leader',
    ];

    public function petugas()
    {
        return $this->belongsTo(Petugas::class, 'petugas_id', 'id_petugas');
    }
}