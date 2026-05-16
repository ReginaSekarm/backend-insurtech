<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Polis;
use App\Models\Produk;
use App\Models\Riwayat_Transaksi;
use Illuminate\Http\Request;
use Carbon\Carbon;

class PolisController extends Controller
{
    // Beli polis
    public function beli(Request $request)
    {
        $user = $request->user();

        // Cek apakah user sudah diverifikasi
        if ($user->verifikasi_status !== 'verified') {
            return response()->json([
                'message' => 'Akun belum diverifikasi admin. Harap tunggu verifikasi KTP/KK.'
            ], 403);
        }

        $request->validate([
            'ID_Produk' => 'required|exists:produk,ID_Produk',
        ]);

        $produk = Produk::where('ID_Produk', $request->ID_Produk)
            ->where('status', 'published')
            ->first();

        if (!$produk) {
            return response()->json(['message' => 'Produk tidak tersedia'], 404);
        }

        // Generate ID_Polis
        $lastPolis = Polis::orderBy('ID_Polis', 'desc')->first();
        $lastId = $lastPolis ? intval(substr($lastPolis->ID_Polis, 4)) : 0;
        $newId = 'POLI' . str_pad($lastId + 1, 5, '0', STR_PAD_LEFT);

        $polis = Polis::create([
            'ID_Polis' => $newId,
            'ID_Pengguna' => $user->ID_Pengguna,
            'ID_Produk' => $produk->ID_Produk,
            'Tanggal_Mulai' => Carbon::now(),
            'Status_Polis' => 'Aktif',
            'Total_Premi' => $produk->Harga_Premi
        ]);

        // Catat riwayat transaksi
        Riwayat_Transaksi::create([
            'ID_Transaksi' => 'TRX' . time(),
            'ID_Pengguna' => $user->ID_Pengguna,
            'ID_Polis' => $newId,
            'Jenis_Transaksi' => 'Pembayaran Premi',
            'Tanggal_Transaksi' => now(),
            'Jumlah' => $produk->Harga_Premi,
            'Keterangan' => 'Pembelian polis ' . $produk->Nama_Produk
        ]);

        return response()->json([
            'message' => 'Pembelian polis berhasil',
            'data' => $polis
        ], 201);
    }

    // Lihat polis saya
    public function polisSaya(Request $request)
    {
        $polis = Polis::with('produk')
            ->where('ID_Pengguna', $request->user()->ID_Pengguna)
            ->get();

        return response()->json($polis);
    }

    // Detail polis
    public function detail($id, Request $request)
    {
        $polis = Polis::with('produk')
            ->where('ID_Polis', $id)
            ->where('ID_Pengguna', $request->user()->ID_Pengguna)
            ->firstOrFail();

        return response()->json($polis);
    }
}