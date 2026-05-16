<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Polis;
use App\Models\Produk;

class PolisController extends Controller
{
    // Lihat polis milik pengguna yang login
    public function polisSaya(Request $request)
    {
        $polis = Polis::where('ID_Pengguna', auth()->user()->ID_Pengguna)->get();
        return response()->json(['data' => $polis]);
    }

    // Lihat detail polis
    public function detail($id)
    {
        $polis = Polis::where('ID_Polis', $id)
            ->where('ID_Pengguna', auth()->user()->ID_Pengguna)
            ->firstOrFail();
        return response()->json(['data' => $polis]);
    }

    // Beli produk / buat polis baru
    public function beli(Request $request)
    {
        $request->validate([
            'ID_Produk' => 'required|exists:produk,ID_Produk',
        ]);

        $user = auth()->user();

        // Cek apakah pengguna sudah terverifikasi
        if ($user->verifikasi_status !== 'verified') {
            return response()->json([
                'message' => 'Akun belum terverifikasi, silakan tunggu verifikasi KTP/KK dari admin'
            ], 403);
        }

        $produk = Produk::findOrFail($request->ID_Produk);

        // Cek apakah produk sudah dipublish
        if ($produk->status !== 'published') {
            return response()->json([
                'message' => 'Produk tidak tersedia'
            ], 400);
        }

        $count = Polis::count() + 1;
        $newId = 'POL' . str_pad($count, 5, '0', STR_PAD_LEFT);

        $polis = Polis::create([
            'ID_Polis'       => $newId,
            'ID_Pengguna'    => $user->ID_Pengguna,
            'ID_Produk'      => $request->ID_Produk,
            'Tanggal_Mulai'  => now()->toDateString(),
            'Tanggal_Selesai'=> now()->addYear()->toDateString(),
            'Status_Polis'   => 'Aktif',
            'Total_Premi'    => $produk->Harga_Premi,
        ]);

        return response()->json([
            'message' => 'Polis berhasil dibuat',
            'data'    => $polis
        ], 201);
    }
}