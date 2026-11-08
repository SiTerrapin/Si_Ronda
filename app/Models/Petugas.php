<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Petugas extends Authenticatable
{
    use HasFactory;

    protected $table = 'petugas';
    protected $primaryKey = 'id_petugas';
    protected $fillable = [
        'username', 
        'password', 
        'nama_lengkap',
        'gender',
        'is_active',
    ];
    protected $hidden = ['password'];

    public function jadwal()
    {
        return $this->hasMany(Plot::class, 'petugas_id', 'id_petugas');
    }

}
