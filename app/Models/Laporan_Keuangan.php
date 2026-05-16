<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Laporan_Keuangan extends Model  // ← class name = nama file
{
    protected $table = 'laporan_keuangan';
    protected $primaryKey = null;
    public $incrementing = false;
    protected $keyType = null;

    protected $fillable = [
        'ID_Polis',
        'ID_Klaim',
        'Jenis_Dana',
        'Total_Pemasukan_Premi',
        'Total_Pengeluaran_Klaim',
        'Periode_Laporan'
    ];

    public function polis()
    {
        return $this->belongsTo(Polis::class, 'ID_Polis', 'ID_Polis');
    }

    public function klaim()
    {
        return $this->belongsTo(Klaim::class, 'ID_Klaim', 'ID_Klaim');
    }
}