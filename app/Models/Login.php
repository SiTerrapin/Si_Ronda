<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Login extends Model
{
    protected $table = "anggota";
    protected $primaryKey = "id_petugas";
    protected $fillable = [
        "id_petugas",
        "nama",
        "alamat",
        "no_hp",
        "is_active",
        "nama_lengkap",
        "gender"
    ];
}
