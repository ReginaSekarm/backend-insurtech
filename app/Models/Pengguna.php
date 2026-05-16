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
}