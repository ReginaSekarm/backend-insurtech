<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Klaim extends Model
{
    protected $table = 'klaim';
    protected $primaryKey = 'ID_Klaim';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'ID_Klaim',
        'ID_Polis',
        'Jenis_Klaim',
        'Tanggal_Pengajuan',
        'Status_Klaim',
        'alasan_penolakan'
    ];

    public function polis()
    {
        return $this->belongsTo(Polis::class, 'ID_Polis', 'ID_Polis');
    }
}