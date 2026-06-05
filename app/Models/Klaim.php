<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
        'jumlah_klaim',           // sudah ada
        'Tanggal_Pengajuan',
        'Tanggal_Kejadian',       // TAMBAHKAN
        'Deskripsi',              // TAMBAHKAN
        'Dokumen',                // TAMBAHKAN
        'Tanggal_Pencairan',
        'Status_Klaim',
        'alasan_penolakan',
    ];

    protected $casts = [
        'Tanggal_Pengajuan' => 'date',
        'Tanggal_Kejadian' => 'date',     // TAMBAHKAN
        'Tanggal_Pencairan' => 'date',
        'jumlah_klaim' => 'decimal:2',
    ];

    public function polis(): BelongsTo
    {
        return $this->belongsTo(Polis::class, 'ID_Polis', 'ID_Polis');
    }

    public function getStatusFormattedAttribute(): string
    {
        return match($this->Status_Klaim) {
            'Selesai' => 'DISETUJUI',
            'Ditolak' => 'DITOLAK',
            'Proses' => 'DIPROSES',
            default => $this->Status_Klaim,
        };
    }

    public function getIsCairAttribute(): bool
    {
        return $this->Status_Klaim === 'Selesai' && !is_null($this->Tanggal_Pencairan);
    }

    public function scopePending($query)
    {
        return $query->where('Status_Klaim', 'Proses');
    }

    public function scopeCompleted($query)
    {
        return $query->where('Status_Klaim', 'Selesai');
    }

    public function scopeRejected($query)
    {
        return $query->where('Status_Klaim', 'Ditolak');
    }
}