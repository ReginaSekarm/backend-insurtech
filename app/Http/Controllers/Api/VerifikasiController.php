<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pengguna;
use App\Models\Polis;
use App\Models\Klaim;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class VerifikasiController extends Controller
{
    public function dashboardStats()
    {
        try {
            $totalPengguna = Pengguna::count();

            $semuaPolis = Polis::with('produk')->get();
            $totalPolisAktif = 0;
            $totalPremiBulanIni = 0;

            foreach ($semuaPolis as $p) {
                $status = strtolower($p->Status_Polis ?? $p->status ?? '');
                
                if ($status !== 'batal' && $status !== 'nonaktif' && $status !== 'ditolak' && $status !== 'expired') {
                    $totalPolisAktif++;
                    $premi = $p->Total_Premi ?? $p->Harga_Premi ?? ($p->produk ? $p->produk->Harga_Premi : 0);
                    $totalPremiBulanIni += (int) $premi;
                }
            }

            $totalKlaimPending = Klaim::whereIn('Status_Klaim', ['Proses', 'Pending', 'proses', 'pending'])->count();

            return response()->json([
                'status' => 'success',
                'data' => [
                    'pengguna'      => $totalPengguna,
                    'polisAktif'    => $totalPolisAktif,
                    'klaimPending'  => $totalKlaimPending,
                    'premiBulanIni' => $totalPremiBulanIni
                ]
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error dashboardStats: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal memuat statistik admin: ' . $e->getMessage()
            ], 500);
        }
    }

    public function pendingUsers()
    {
        try {
            $users = DB::select("
                SELECT ID_Pengguna, Nama_Lengkap, Email, No_Telepon, foto_ktp, foto_kk, NIK, No_KK, Tanggal_Lahir, verifikasi_status, alasan_penolakan
                FROM pengguna 
                WHERE verifikasi_status = 'pending' AND role = 'user'
            ");

            Log::info('Jumlah pending users: ' . count($users));

            return response()->json([
                'status' => 'success',
                'data' => $users
            ], 200);
            
        } catch (\Exception $e) {
            Log::error('Error pendingUsers: ' . $e->getMessage());
            
            try {
                $users = DB::select("SELECT * FROM pengguna WHERE verifikasi_status = 'pending'");
                return response()->json([
                    'status' => 'success',
                    'data' => $users
                ], 200);
            } catch (\Exception $e2) {
                return response()->json([
                    'status' => 'error',
                    'message' => $e2->getMessage(),
                    'data' => []
                ], 500);
            }
        }
    }

    public function verify(Request $request, $id)
    {
        try {
            Log::info('Verifikasi user ID: ' . $id);
            Log::info('Request data: ' . json_encode($request->all()));
            
            $user = Pengguna::where('ID_Pengguna', $id)->first();

            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Data pengguna tidak ditemukan.'
                ], 404);
            }

            $request->validate([
                'dokumen_type' => 'required|in:ktp,kk',
                'status' => 'required|in:verified,rejected',
                'alasan_penolakan' => 'nullable|string'
            ]);

            if ($request->status === 'rejected' && $request->filled('alasan_penolakan')) {
                $user->alasan_penolakan = $request->alasan_penolakan;
            }

            $user->verifikasi_status = $request->status;
            $user->verified_at = now();
            $user->verified_by = auth()->id();
            $user->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Dokumen ' . $request->dokumen_type . ' berhasil diverifikasi',
                'data' => [
                    'user' => [
                        'id' => $user->ID_Pengguna,
                        'nama' => $user->Nama_Lengkap,
                        'email' => $user->Email,
                        'verifikasi_status' => $user->verifikasi_status
                    ]
                ]
            ], 200);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error verify: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    // ✅ DIPERBAIKI — hapus ktp_verified & kk_verified yang tidak ada di database
    public function verifyFull(Request $request, $id)
    {
        try {
            $user = Pengguna::where('ID_Pengguna', $id)->first();

            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Data pengguna tidak ditemukan.'
                ], 404);
            }

            $request->validate([
                'status' => 'required|in:verified,rejected',
                'alasan_penolakan' => 'nullable|string'
            ]);

            $user->verifikasi_status = $request->status;
            $user->verified_at = now();
            $user->verified_by = auth()->id();

            if ($request->status === 'rejected' && $request->filled('alasan_penolakan')) {
                $user->alasan_penolakan = $request->alasan_penolakan;
            }

            $user->save();

            return response()->json([
                'status' => 'success',
                'message' => $request->status === 'verified' ? 'User berhasil diverifikasi' : 'User ditolak',
                'data' => [
                    'id' => $user->ID_Pengguna,
                    'nama' => $user->Nama_Lengkap,
                    'email' => $user->Email,
                    'verifikasi_status' => $user->verifikasi_status
                ]
            ], 200);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error verifyFull: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getDokumen($id)
    {
        try {
            $user = Pengguna::where('ID_Pengguna', $id)->first();

            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Data pengguna tidak ditemukan.'
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'data' => [
                    'user' => [
                        'id' => $user->ID_Pengguna,
                        'nama' => $user->Nama_Lengkap,
                        'email' => $user->Email,
                        'no_telepon' => $user->No_Telepon,
                        'nik' => $user->NIK,
                        'no_kk' => $user->No_KK,
                        'tanggal_lahir' => $user->Tanggal_Lahir,
                        'alamat' => $user->Alamat_Lengkap,
                        'foto_ktp' => $user->foto_ktp,
                        'foto_kk' => $user->foto_kk,
                        'verifikasi_status' => $user->verifikasi_status
                    ]
                ]
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function cekStatus(Request $request)
    {
        try {
            $user = $request->user();
            return response()->json([
                'status' => 'success',
                'data' => [
                    'verifikasi_status' => $user->verifikasi_status ?? $user->Verifikasi_Status,
                    'alasan_penolakan' => $user->alasan_penolakan,
                    'verified_at' => $user->verified_at
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}