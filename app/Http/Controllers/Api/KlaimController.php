<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Klaim;
use App\Models\Polis;
use App\Models\RiwayatTransaksi;
use Illuminate\Http\Request;

class KlaimController extends Controller
{
    // Ajukan klaim
    public function ajukan(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'ID_Polis' => 'required|exists:polis,ID_Polis',
            'Jenis_Klaim' => 'required|string|max:50',
        ]);

        $polis = Polis::where('ID_Polis', $request->ID_Polis)
            ->where('ID_Pengguna', $user->ID_Pengguna)
            ->first();

        if (!$polis) {
            return response()->json(['message' => 'Polis tidak ditemukan'], 404);
        }

        // Generate ID_Klaim
        $lastKlaim = Klaim::orderBy('ID_Klaim', 'desc')->first();
        $lastId = $lastKlaim ? intval(substr($lastKlaim->ID_Klaim, 3)) : 0;
        $newId = 'KLM' . str_pad($lastId + 1, 5, '0', STR_PAD_LEFT);

        $klaim = Klaim::create([
            'ID_Klaim' => $newId,
            'ID_Polis' => $request->ID_Polis,
            'Jenis_Klaim' => $request->Jenis_Klaim,
            'Tanggal_Pengajuan' => now(),
            'Status_Klaim' => 'Proses'
        ]);

        // Update status polis
        $polis->update(['Status_Polis' => 'Klaim Proses']);

        return response()->json([
            'message' => 'Klaim berhasil diajukan',
            'data' => $klaim
        ], 201);
    }

    // Lihat klaim saya
    public function klaimSaya(Request $request)
    {
        $klaim = Klaim::with('polis.produk')
            ->whereHas('polis', function ($q) use ($request) {
                $q->where('ID_Pengguna', $request->user()->ID_Pengguna);
            })
            ->get();

        return response()->json($klaim);
    }

    // Status klaim
    public function status($id, Request $request)
    {
        $klaim = Klaim::with('polis')
            ->where('ID_Klaim', $id)
            ->whereHas('polis', function ($q) use ($request) {
                $q->where('ID_Pengguna', $request->user()->ID_Pengguna);
            })
            ->firstOrFail();

        return response()->json([
            'status' => $klaim->Status_Klaim,
            'tanggal_pengajuan' => $klaim->Tanggal_Pengajuan,
            'jenis_klaim' => $klaim->Jenis_Klaim
        ]);
    }

    // Admin: lihat klaim pending
    public function pendingKlaim(Request $request)
    {
        $klaim = Klaim::with(['polis.pengguna', 'polis.produk'])
            ->where('Status_Klaim', 'Proses')
            ->get();

        return response()->json($klaim);
    }

    // Admin: review klaim
    public function review(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:Selesai,Ditolak',
            'alasan_penolakan' => 'required_if:status,Ditolak|nullable|string'
        ]);

        $klaim = Klaim::findOrFail($id);
        $klaim->update([
            'Status_Klaim' => $request->status,
            'alasan_penolakan' => $request->status == 'Ditolak' ? $request->alasan_penolakan : null
        ]);

        // Update status polis
        $polis = Polis::find($klaim->ID_Polis);
        if ($request->status == 'Selesai') {
            $polis->update(['Status_Polis' => 'Klaim Selesai']);
        } else {
            $polis->update(['Status_Polis' => 'Aktif']);
        }

        return response()->json([
            'message' => 'Klaim telah direview',
            'data' => $klaim
        ]);
    }
}