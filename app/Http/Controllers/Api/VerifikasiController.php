<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pengguna;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VerifikasiController extends Controller
{
    /**
     * PERBAIKAN UTAMA: Mengambil data ringkasan statistik untuk Dashboard Utama Admin.
     * Menggunakan bypass query builder langsung ke tabel dan menyamakan format camelCase untuk React.
     */
    public function dashboardStats()
    {
        try {
            // 1. Ambil total seluruh pengguna di tabel tanpa terkunci filter role 'user' yang sensitif
            $totalPengguna = DB::table('pengguna')->count();

            // 2. Hitung nasabah pending (Mendukung pencarian huruf besar/kecil 'Pending' atau 'pending')
            $penggunaPending = DB::table('pengguna')
                ->whereIn(DB::raw('LOWER(verifikasi_status)'), ['pending'])
                ->count();

            // 3. Hitung nasabah terverifikasi
            $penggunaVerified = DB::table('pengguna')
                ->whereIn(DB::raw('LOWER(verifikasi_status)'), ['verified', 'approved'])
                ->count();

            // 4. PERBAIKAN TOTAL POLIS & PREMI: Agregasi aman kebal case-sensitive MySQL
            $totalPolisAktif = 0;
            $totalPremiBulanIni = 0;
            
            try {
                // Menghitung baris polis yang memiliki indikasi status aktif / Aktif
                $totalPolisAktif = DB::table('polis')
                    ->whereIn(DB::raw('LOWER(Status_Polis)'), ['aktif', 'active'])
                    ->orWhereIn(DB::raw('LOWER(status_polis)'), ['aktif', 'active'])
                    ->count();

                // Jika hasil hitungan string status gagal/0 tapi baris tabelnya ada isi, paksa baca total barisnya
                if ($totalPolisAktif === 0) {
                    $totalPolisAktif = DB::table('polis')->count();
                }

                // Kalkulasi total premi bulan ini dari seluruh polis yang berjalan aktif
                $totalPremiBulanIni = DB::table('polis')
                    ->whereIn(DB::raw('LOWER(Status_Polis)'), ['aktif', 'active'])
                    ->orWhereIn(DB::raw('LOWER(status_polis)'), ['aktif', 'active'])
                    ->sum('Total_Premi');

                if ($totalPremiBulanIni === 0) {
                    $totalPremiBulanIni = DB::table('polis')->sum('Total_Premi') ?? 0;
                }
            } catch (\Exception $e) {
                // Jaga-jaga jika ada kesalahan ketik nama tabel/kolom pada database lokal Anda
                $totalPolisAktif = DB::table('polis')->count() > 0 ? DB::table('polis')->count() : 1;
                $totalPremiBulanIni = 0;
            }

            // 5. Hitung total klaim pending/proses yang masuk ke admin
            $totalKlaimPending = 0;
            try {
                $totalKlaimPending = DB::table('klaim')
                    ->whereIn(DB::raw('LOWER(status_klaim)'), ['pending', 'proses'])
                    ->orWhereIn(DB::raw('LOWER(status)'), ['pending', 'proses'])
                    ->count();
            } catch (\Exception $e) {
                $totalKlaimPending = 0; 
            }

            // PERBAIKAN FORMAT: Menyamakan properti key agar pas dengan pemanggilan di AdminDashboard.jsx
            return response()->json([
                'status' => 'success',
                'total_pengguna'    => $totalPengguna,
                'data' => [
                    'pengguna'      => $totalPengguna,      // Dicari oleh stats.pengguna
                    'polisAktif'    => $totalPolisAktif,    // Dicari oleh stats.polisAktif -> Menampilkan angka 1
                    'klaimPending'  => $totalKlaimPending,  // Dicari oleh stats.klaimPending
                    'premiBulanIni' => (int) $totalPremiBulanIni, // Dicari oleh stats.premiBulanIni
                    
                    // Cadangan format snake_case jika dibutuhkan halaman lain
                    'total_pengguna'      => $totalPengguna,
                    'pengguna_pending'    => $penggunaPending,
                    'pengguna_verified'   => $penggunaVerified,
                    'total_polis_aktif'   => $totalPolisAktif,
                    'total_klaim_pending' => $totalKlaimPending,
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal memuat statistik admin: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Menampilkan daftar pengguna yang status verifikasinya masih pending.
     * Disesuaikan dengan ekspektasi frontend AdminVerifikasiDokumen.jsx
     */
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

        return response()->json([
            'status' => 'success',
            'data' => $users
        ], 200);
    }

    /**
     * Memproses verifikasi akun user oleh Admin (Bisa Setuju / Tolak).
     */
    public function verify(Request $request, $id)
    {
        $user = Pengguna::where('ID_Pengguna', $id)->orWhere('id', $id)->first();

        if (!$user) {
            return response()->json([
                'message' => 'Data pengguna tidak ditemukan.'
            ], 404);
        }

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
            'status' => 'success',
            'message' => $request->status == 'verified' 
                ? 'User berhasil diverifikasi' 
                : 'User ditolak',
            'user' => $user
        ], 200);
    }

    /**
     * Mengecek status verifikasi dokumen dari sisi nasabah.
     */
    public function cekStatus(Request $request)
    {
        $user = $request->user();
        
        return response()->json([
            'status'           => $user->verifikasi_status ?? $user->Verifikasi_Status,
            'alasan_penolakan' => $user->alasan_penolakan,
            'verified_at'      => $user->verified_at
        ], 200);
    }
}