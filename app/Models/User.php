<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    // Arahkan ke tabel pengguna, bukan users
    protected $table = 'pengguna';
    
    // Primary key tabel pengguna
    protected $primaryKey = 'ID_Pengguna';
    public $incrementing = false;
    protected $keyType = 'string';

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
        'foto_ktp',
        'foto_kk'
    ];

    protected $hidden = [
        'Password',
        'remember_token',
    ];

    // Ganti nama kolom password default
    public function getAuthPassword()
    {
        return $this->Password;
    }
}