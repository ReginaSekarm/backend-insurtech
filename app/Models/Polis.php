<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
// Menambahkan import class secara eksplisit untuk mencegah class not found
use App\Models\Pengguna; 
use App\Models\Produk;
use App\Models\Klaim;
use App\Models\Pembayaran_Premi;

class Polis extends Model
{
    protected $table = 'polis';
    protected $primaryKey = 'ID_Polis';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'ID_Polis',
        'ID_Pengguna',
        'ID_Produk',
        'Tanggal_Mulai',
        'Tanggal_Selesai',
        'Status_Polis',
        'Total_Premi',
    ];

    public function pengguna()
    {
        // Catatan: Jika file model Anda bernama User.php (bukan Pengguna.php), 
        // silakan ubah Pengguna::class di bawah ini menjadi User::class
        return $this->belongsTo(Pengguna::class, 'ID_Pengguna', 'ID_Pengguna');
    }

    public function produk()
    {
        return $this->belongsTo(Produk::class, 'ID_Produk', 'ID_Produk');
    }

    public function klaim()
    {
        return $this->hasMany(Klaim::class, 'ID_Polis', 'ID_Polis');
    }

    public function pembayaranPremi()
    {
        return $this->hasMany(Pembayaran_Premi::class, 'ID_Polis', 'ID_Polis');
    }
}