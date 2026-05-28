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
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
    Route::get('/nasabah/dashboard', [AuthController::class, 'dashboardNasabah']);
    
    // ====================================================================
    // TAMBAHAN: Rute Mengubah Nomor Telepon Nasabah (Dipanggil oleh React)
    // ====================================================================
    Route::put('/nasabah/ubah-nomor-telepon', [AuthController::class, 'ubahNomorTelepon']);
    
    // ========== DATA STATISTIK DASHBOARD & TUNGGAKAN ==========
    Route::get('/dashboard/stats', [PolisController::class, 'dashboardStats']);
    Route::get('/nasabah/tunggakan', [PolisController::class, 'tunggakanNasabah']);
    
    // PERBAIKAN: Menghubungkan endpoint riwayat transaksi nasabah asli ke controller
    Route::get('/nasabah/riwayat-transaksi', [PolisController::class, 'riwayatTransaksi']);
    
    // ========== NOTIFIKASI NASABAH ==========
    Route::get('/nasabah/notifikasi', [PolisController::class, 'notifikasiNasabah']);

    // ========== ROUTE POLIS (USER LOGIN) ==========
    Route::post('/polis/beli', [PolisController::class, 'beli']);
    Route::get('/polis/saya', [PolisController::class, 'polisSaya']);
    Route::get('/polis/{id}', [PolisController::class, 'detail']);
    Route::put('/polis/{id}/bayar', [PolisController::class, 'bayar']);
    
    // Rute untuk memproses inisiasi pembayaran iuran/premi berkala dari nasabah
    Route::post('/polis/{id}/bayar-premi', [PolisController::class, 'bayarPremiRutin']);
    
    // Rute untuk mengambil detail data iuran premi untuk discan di halaman QRIS frontend
    Route::get('/pembayaran-premi/{transactionId}', [PolisController::class, 'detailPembayaranPremi']);

    // ========== ROUTE KLAIM (USER LOGIN) ==========
    Route::post('/klaim/ajukan', [KlaimController::class, 'ajukan']);
    Route::get('/klaim/saya', [KlaimController::class, 'klaimSaya']);
    Route::get('/klaim/{id}/status', [KlaimController::class, 'status']);
});

// ========== ROUTE KHUSUS ADMIN (PREFIX: ADMIN) ==========
Route::middleware(['auth:sanctum'])->prefix('admin')->group(function () {
    // PERBAIKAN UTAMA: Menambahkan endpoint statistik untuk Dashboard Utama Admin
    Route::get('/dashboard/stats', [VerifikasiController::class, 'dashboardStats']);

    // Manajemen Produk oleh Admin
    Route::post('/produk', [ProdukController::class, 'store']);
    Route::put('/produk/{id}', [ProdukController::class, 'update']);
    Route::delete('/produk/{id}', [ProdukController::class, 'destroy']);
    Route::put('/produk/{id}/publish', [ProdukController::class, 'publish']);

    // PERBAIKAN: Membuka rute agar bisa diakses sebagai /api/admin/verifikasi-dokumen jika dicari oleh frontend
    Route::get('/verifikasi/pending', [VerifikasiController::class, 'pendingUsers']);
    Route::get('/verifikasi-dokumen', [VerifikasiController::class, 'pendingUsers']); 
    Route::put('/verifikasi/{id}', [VerifikasiController::class, 'verify']);

    Route::get('/klaim/pending', [KlaimController::class, 'pendingKlaim']);
});

// ========== ROUTE API RESOURCE YANG SUDAH ADA ==========
Route::apiResource('pengguna', PenggunaController::class);
Route::apiResource('polis', PolisController::class);
Route::apiResource('laporan-keuangan', Laporan_KeuanganController::class);