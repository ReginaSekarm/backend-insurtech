<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PenggunaController;
use App\Http\Controllers\PolisController;
use App\Http\Controllers\ProdukController;
use App\Http\Controllers\KlaimController;
use App\Http\Controllers\Laporan_KeuanganController;

Route::apiResource('pengguna', PenggunaController::class);
Route::apiResource('polis', PolisController::class);
Route::apiResource('produk', ProdukController::class);
Route::apiResource('klaim', KlaimController::class);
Route::apiResource('laporan-keuangan', Laporan_KeuanganController::class);