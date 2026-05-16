<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pembayaran_Premi extends Model  // ← class name = nama file
{
    protected $table = 'pembayaran_premi';
    protected $primaryKey = 'ID_Pembayaran';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'ID_Pembayaran',
        'ID_Polis',
        'ID_Pengguna',
        'Tanggal_Bayar',
        'Jumlah_Bayar',
        'Metode_Bayar',
        'Status_Bayar'
    ];

    public function polis()
    {
        return $this->belongsTo(Polis::class, 'ID_Polis', 'ID_Polis');
    }

    public function pengguna()
    {
        return $this->belongsTo(Pengguna::class, 'ID_Pengguna', 'ID_Pengguna');
    }
}