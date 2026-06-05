<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PenggunaController;
use App\Http\Controllers\PolisController;
use App\Http\Controllers\ProdukController;
use App\Http\Controllers\KlaimController;
use App\Http\Controllers\Laporan_KeuanganController;
use App\Http\Controllers\Api\VerifikasiController;
use App\Http\Controllers\Api\AuthController;

// ========== ROUTE LOGIN & REGISTER (PUBLIC) ==========
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

// ========== ROUTE PRODUK (PUBLIC - SEMUA BISA LIHAT) ==========
Route::get('/produk', [ProdukController::class, 'index']);
Route::get('/produk/{id}', [ProdukController::class, 'show']);

// ========== ROUTE YANG BUTUH TOKEN (USER & NASABAH LOGIN) ==========
Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
    Route::get('/nasabah/dashboard', [AuthController::class, 'dashboardNasabah']);
    Route::put('/nasabah/ubah-nomor-telepon', [AuthController::class, 'ubahNomorTelepon']);
    
    // ========== UBAH PASSWORD ==========
    Route::put('/ubah-password', [AuthController::class, 'ubahPassword']);

    // ========== UPLOAD DOKUMEN KTP & KK ==========
    Route::post('/upload-dokumen', [AuthController::class, 'uploadDokumen']);
    
    // Dashboard & Lainnya
    Route::get('/dashboard/stats', [PolisController::class, 'dashboardStats']);
    Route::get('/nasabah/tunggakan', [PolisController::class, 'tunggakanNasabah']);
    Route::get('/nasabah/riwayat-transaksi', [PolisController::class, 'riwayatTransaksi']);
    
    // ========== NOTIFIKASI ==========
    Route::get('/nasabah/notifikasi', [PolisController::class, 'notifikasiNasabah']);
    Route::post('/notifications/mark-read', [PolisController::class, 'markNotificationAsRead']);
    Route::get('/notifications/unread-count', [PolisController::class, 'unreadNotificationCount']);

    // Polis
    Route::post('/polis/beli', [PolisController::class, 'beli']);
    Route::get('/polis/saya', [PolisController::class, 'polisSaya']);
    Route::get('/polis/{id}', [PolisController::class, 'detail']);
    Route::put('/polis/{id}/bayar', [PolisController::class, 'bayar']);
    Route::post('/polis/{id}/bayar-premi', [PolisController::class, 'bayarPremiRutin']);
    Route::get('/pembayaran-premi/{transactionId}', [PolisController::class, 'detailPembayaranPremi']);

    // Klaim User
    Route::post('/klaim/ajukan', [KlaimController::class, 'ajukan']);
    Route::get('/klaim/saya', [KlaimController::class, 'klaimSaya']);
    Route::get('/klaim/status/{id}', [KlaimController::class, 'status']);
    Route::get('/klaim/unduh/{id}', [KlaimController::class, 'unduh']);
    
    // SEMUA KLAIM (untuk admin dan testing)
    Route::get('/semua-klaim', [KlaimController::class, 'index']);
});

// ========== ROUTE KHUSUS ADMIN (PREFIX: ADMIN) ==========
Route::middleware(['auth:sanctum'])->prefix('admin')->group(function () {
    // Dashboard Stats
    Route::get('/dashboard/stats', [VerifikasiController::class, 'dashboardStats']);
    Route::get('/stats', [KlaimController::class, 'stats']);

    // Manajemen Produk
    Route::post('/produk', [ProdukController::class, 'store']);
    Route::put('/produk/{id}', [ProdukController::class, 'update']);
    Route::delete('/produk/{id}', [ProdukController::class, 'destroy']);
    Route::put('/produk/{id}/publish', [ProdukController::class, 'publish']);

    // Verifikasi Dokumen
    Route::get('/verifikasi/pending', [VerifikasiController::class, 'pendingUsers']);
    Route::get('/verifikasi-dokumen', [VerifikasiController::class, 'pendingUsers']);
    Route::get('/verifikasi-dokumen/{id}', [VerifikasiController::class, 'getDokumen']);
    // ✅ SATU-SATUNYA YANG BERUBAH — verify() → verifyFull()
    Route::put('/verifikasi/{id}', [VerifikasiController::class, 'verifyFull']);

    // ========== ROUTE KLAIM UNTUK ADMIN ==========
    Route::get('/klaim', [KlaimController::class, 'index']);
    Route::get('/klaim/pending', [KlaimController::class, 'pendingKlaim']);
    Route::put('/klaim/{id}/review', [KlaimController::class, 'review']);
    
    // ========== ADMIN UPDATE STATUS KLAIM (DENGAN NOTIFIKASI) ==========
    Route::put('/klaim/{id}/status', [PolisController::class, 'updateStatusKlaim']);
});

// ========== ROUTE API RESOURCE YANG SUDAH ADA ==========
Route::apiResource('pengguna', PenggunaController::class);
Route::apiResource('polis', PolisController::class);
Route::apiResource('laporan-keuangan', Laporan_KeuanganController::class);
Route::apiResource('klaim', KlaimController::class);

// ========== ROUTE TESTING ==========
Route::get('/test', function () {
    return response()->json([
        'message' => 'API is working', 
        'time' => now()->toDateTimeString(),
        'status' => 'success'
    ]);
});

// ========== ROUTE UNTUK ADMIN DASHBOARD (tanpa prefix admin) ==========
Route::middleware('auth:sanctum')->prefix('admin-dashboard')->group(function () {
    Route::get('/klaim', [KlaimController::class, 'index']);
    Route::get('/stats', [KlaimController::class, 'stats']);
});