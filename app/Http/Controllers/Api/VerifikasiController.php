<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pengguna;
use Illuminate\Http\Request;

class VerifikasiController extends Controller
{
    public function pendingUsers()
    {
        $users = Pengguna::where('verifikasi_status', 'pending')
            ->where('role', 'user')
            ->get([
                'ID_Pengguna', 
                'Nama_Lengkap', 
                'Email', 
                'No_Telepon',
                'foto_ktp',
                'foto_kk'
            ]);

        return response()->json($users);
    }

    public function verify(Request $request, $id)
    {
        $user = Pengguna::findOrFail($id);

        $request->validate([
            'status' => 'required|in:verified,rejected',
            'alasan_penolakan' => 'required_if:status,rejected|nullable|string'
        ]);

        $user->update([
            'verifikasi_status' => $request->status,
            'alasan_penolakan'  => $request->alasan_penolakan,
            'verified_at'       => now(),
            'verified_by'       => auth()->id()
        ]);

        return response()->json([
            'message' => $request->status == 'verified' 
                ? 'User berhasil diverifikasi' 
                : 'User ditolak',
            'user' => $user
        ]);
    }

    public function cekStatus(Request $request)
    {
        $user = $request->user();
        
        return response()->json([
            'status'           => $user->verifikasi_status,
            'alasan_penolakan' => $user->alasan_penolakan,
            'verified_at'      => $user->verified_at
        ]);
    }
}