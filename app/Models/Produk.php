<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Produk extends Model
{
    protected $table = 'produk';
    protected $primaryKey = 'ID_Produk';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'ID_Produk',
        'Nama_Produk',
        'Deskripsi_Produk',
        'Harga_Premi',
        'status',
        'published_at',
        'created_by'
    ];
}