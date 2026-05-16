<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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