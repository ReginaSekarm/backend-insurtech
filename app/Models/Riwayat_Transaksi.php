<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Riwayat_Transaksi extends Model
{
    protected $table = 'riwayat_transaksi';
    protected $primaryKey = 'ID_Transaksi';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'ID_Transaksi',
        'ID_Pengguna',
        'ID_Polis',
        'Jenis_Transaksi',
        'Tanggal_Transaksi',
        'Jumlah',
        'Keterangan'
    ];

    public function pengguna()
    {
        return $this->belongsTo(Pengguna::class, 'ID_Pengguna', 'ID_Pengguna');
    }

    public function polis()
    {
        return $this->belongsTo(Polis::class, 'ID_Polis', 'ID_Polis');
    }
}