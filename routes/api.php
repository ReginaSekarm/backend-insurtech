<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PenggunaController;
use App\Http\Controllers\PolisController;
use App\Http\Controllers\ProdukController;
use App\Http\Controllers\KlaimController;
use App\Http\Controllers\Laporan_KeuanganController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\VerifikasiController;

// ========== ROUTE LOGIN & REGISTER (PUBLIC) ==========
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

// ========== ROUTE YANG BUTUH TOKEN ==========
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
});

// ========== ROUTE PRODUK (SEMUA BISA LIHAT) ==========
Route::get('/produk', [ProdukController::class, 'index']);
Route::get('/produk/{id}', [ProdukController::class, 'show']);

// ========== ROUTE PRODUK (HANYA ADMIN) ==========
Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->group(function () {
    Route::post('/produk', [ProdukController::class, 'store']);
    Route::put('/produk/{id}', [ProdukController::class, 'update']);
    Route::delete('/produk/{id}', [ProdukController::class, 'destroy']);
    Route::put('/produk/{id}/publish', [ProdukController::class, 'publish']);
});

// ========== ROUTE POLIS (USER LOGIN) ==========
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/polis/beli', [PolisController::class, 'beli']);
    Route::get('/polis/saya', [PolisController::class, 'polisSaya']);
    Route::get('/polis/{id}', [PolisController::class, 'detail']);
});

// ========== ROUTE KLAIM (USER LOGIN) ==========
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/klaim/ajukan', [KlaimController::class, 'ajukan']);
    Route::get('/klaim/saya', [KlaimController::class, 'klaimSaya']);
    Route::get('/klaim/{id}/status', [KlaimController::class, 'status']);
});

// ========== ROUTE VERIFIKASI (ADMIN ONLY) ==========
Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->group(function () {
    Route::get('/verifikasi/pending', [VerifikasiController::class, 'pendingUsers']);
    Route::put('/verifikasi/{id}', [VerifikasiController::class, 'verify']);
});

// ========== ROUTE API RESOURCE YANG SUDAH ADA ==========
Route::apiResource('pengguna', PenggunaController::class);
Route::apiResource('laporan-keuangan', Laporan_KeuanganController::class);