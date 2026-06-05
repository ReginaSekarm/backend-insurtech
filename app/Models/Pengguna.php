<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class Pengguna extends Authenticatable
{
    use HasApiTokens;

    protected $table = 'pengguna';
    protected $primaryKey = 'ID_Pengguna';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'ID_Pengguna',
        'Nama_Lengkap',
        'Email',
        'Password',
        'No_Telepon',
        'Jenis_Kelamin',
        'Tanggal_Lahir',
        'Alamat_Lengkap',
        'role',
        'verifikasi_status',
        'Verifikasi_Status',
        'alasan_penolakan',
        'verified_at',
        'verified_by',
        'foto_ktp',
        'foto_kk',
        'remember_token',
    ];

    protected $hidden = [
        'Password',
        'remember_token',
    ];

    public function polis()
    {
        return $this->hasMany(Polis::class, 'ID_Pengguna', 'ID_Pengguna');
    }

    public function klaim()
    {
        return $this->hasManyThrough(
            Klaim::class,
            Polis::class,
            'ID_Pengguna',
            'ID_Polis',
            'ID_Pengguna',
            'ID_Polis'
        );
    }
}