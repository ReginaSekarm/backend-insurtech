<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Verifikasi_KTP_KK extends Model  // ← class name = nama file
{
    protected $table = 'verifikasi_ktp_kk';
    protected $primaryKey = 'ID_Verifikasi';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'ID_Verifikasi',
        'ID_Pengguna',
        'No_KTP',
        'No_KK',
        'Tanggal_Upload',
        'Status_Verifikasi',
        'Keterangan'
    ];

    public function pengguna()
    {
        return $this->belongsTo(Pengguna::class, 'ID_Pengguna', 'ID_Pengguna');
    }
}