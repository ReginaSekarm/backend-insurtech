<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Klaim;
use App\Models\Polis;

class KlaimController extends Controller
{
    // Lihat klaim milik pengguna yang login
    public function klaimSaya(Request $request)
    {
        $klaim = Klaim::whereHas('polis', function($q) {
            $q->where('ID_Pengguna', auth()->user()->ID_Pengguna);
        })->get();

        return response()->json(['data' => $klaim]);
    }

    // Lihat status klaim
    public function status($id)
    {
        $klaim = Klaim::findOrFail($id);
        return response()->json([
            'data' => [
                'ID_Klaim'     => $klaim->ID_Klaim,
                'Jenis_Klaim'  => $klaim->Jenis_Klaim,
                'Tanggal_Pengajuan' => $klaim->Tanggal_Pengajuan,
                'Status_Klaim' => $klaim->Status_Klaim,
            ]
        ]);
    }

    // Ajukan klaim (pengguna)
    public function ajukan(Request $request)
    {
        $request->validate([
            'ID_Polis'    => 'required|exists:polis,ID_Polis',
            'Jenis_Klaim' => 'required|string',
        ]);

        $user = auth()->user();

        // Cek polis milik pengguna
        $polis = Polis::where('ID_Polis', $request->ID_Polis)
            ->where('ID_Pengguna', $user->ID_Pengguna)
            ->first();

        if (!$polis) {
            return response()->json(['message' => 'Polis tidak ditemukan'], 404);
        }

        // Cek polis aktif
        if ($polis->Status_Polis !== 'Aktif') {
            return response()->json(['message' => 'Polis tidak aktif'], 400);
        }

        $count = Klaim::count() + 1;
        $newId = 'KLM' . str_pad($count, 5, '0', STR_PAD_LEFT);

        $klaim = Klaim::create([
            'ID_Klaim'          => $newId,
            'ID_Polis'          => $request->ID_Polis,
            'Jenis_Klaim'       => $request->Jenis_Klaim,
            'Tanggal_Pengajuan' => now()->toDateString(),
            'Status_Klaim'      => 'Proses',
        ]);

        return response()->json([
            'message' => 'Klaim berhasil diajukan',
            'data'    => $klaim
        ], 201);
    }

    // Update status klaim (admin)
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'Status_Klaim' => 'required|in:Proses,Selesai,Ditolak',
        ]);

        $klaim = Klaim::findOrFail($id);
        $klaim->update(['Status_Klaim' => $request->Status_Klaim]);

        return response()->json([
            'message' => 'Status klaim berhasil diupdate',
            'data'    => $klaim
        ]);
    }
}