<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Petugas extends Authenticatable
{
    use HasFactory;

    protected $table = 'petugas';
    protected $primaryKey = 'id_petugas';

    protected $fillable = [
        'username',
        'password',
        'nama_lengkap',
        'no_hp',
        'alamat',
        'gender',
        'role',
        'is_active',
    ];

    protected $hidden = ['password'];

    public function jadwal()
    {
        return $this->hasMany(Plot::class, 'petugas_id', 'id_petugas');
    }

    public function laporan()
    {
        return $this->hasMany(Laporan::class, 'petugas_id', 'id_petugas');
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    public function isAdmin(): bool
    {
        return in_array($this->role, ['admin', 'super_admin'], true);
    }

    public function waUrl(?string $pesan = null): ?string
    {
        if (empty($this->no_hp)) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $this->no_hp);
        if ($digits === '') {
            return null;
        }

        if (str_starts_with($digits, '0')) {
            $digits = '62' . substr($digits, 1);
        } elseif (!str_starts_with($digits, '62')) {
            $digits = '62' . $digits;
        }

        $url = 'https://wa.me/' . $digits;
        if ($pesan !== null && $pesan !== '') {
            $url .= '?text=' . rawurlencode($pesan);
        }

        return $url;
    }
}
