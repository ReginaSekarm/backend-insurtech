<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pengguna;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'Email' => 'required|email',
            'Password' => 'required'
        ]);

        $user = Pengguna::where('Email', $request->Email)->first();

        if (!$user || !Hash::check($request->Password, $user->Password)) {
            return response()->json(['message' => 'Email atau password salah'], 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'message' => 'Login berhasil',
            'user' => [
                'id' => $user->ID_Pengguna,
                'nama' => $user->Nama_Lengkap,
                'email' => $user->Email,
                'role' => $user->role,
                'no_telepon' => $user->No_Telepon, // Tambahan agar data session login langsung lengkap
            ],
            'token' => $token
        ]);
    }

    public function register(Request $request)
    {
        $request->validate([
            'Nama_Lengkap'   => 'required|string|max:100',
            'Email'          => 'required|email|unique:pengguna,Email',
            'Password'       => 'required|min:6',
            'No_Telepon'     => 'nullable|string',
            'Jenis_Kelamin'  => 'nullable|in:L,P',
            'Tanggal_Lahir'  => 'nullable|date',
            'Alamat_Lengkap' => 'nullable|string',
        ]);

        $lastUser = Pengguna::orderBy('ID_Pengguna', 'desc')->first();
        $lastId = $lastUser ? intval(substr($lastUser->ID_Pengguna, 3)) : 0;
        $newId = 'USR' . str_pad($lastId + 1, 3, '0', STR_PAD_LEFT);

        $user = Pengguna::create([
            'ID_Pengguna'       => $newId,
            'Nama_Lengkap'      => $request->Nama_Lengkap,
            'Email'             => $request->Email,
            'Password'          => Hash::make($request->Password),
            'No_Telepon'        => $request->No_Telepon,
            'Jenis_Kelamin'     => $request->Jenis_Kelamin,
            'Tanggal_Lahir'     => $request->Tanggal_Lahir,
            'Alamat_Lengkap'    => $request->Alamat_Lengkap,
            'role'              => 'user',
            'verifikasi_status' => 'pending',
        ]);

        return response()->json([
            'message' => 'Registrasi berhasil, silakan tunggu verifikasi KTP/KK',
            'user' => $user
        ], 201);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logout berhasil']);
    }

    // ====================================================================
    // TAMBAHAN BARU: Fungsi untuk Memperbarui Nomor Telepon di Database MySQL
    // ====================================================================
    public function ubahNomorTelepon(Request $request)
    {
        // 1. Validasi input: mendukung key 'No_Telepon' dari React (wajib angka, panjang 10-13 digit)
        $request->validate([
            'No_Telepon' => 'required|numeric|digits_between:10,13',
        ], [
            'No_Telepon.required' => 'Nomor telepon baru wajib diisi.',
            'No_Telepon.numeric'  => 'Nomor telepon harus berupa angka.',
            'No_Telepon.digits_between' => 'Nomor telepon harus berukuran antara 10 hingga 13 digit.',
        ]);

        // 2. Mengambil entitas data pengguna yang saat ini sedang login melalui token Sanctum
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Sesi autentikasi Anda tidak valid atau kedaluwarsa.'], 401);
        }

        // 3. Eksekusi penyimpanan data baru ke dalam database pengguna
        $user->No_Telepon = $request->No_Telepon;
        $user->save();

        // 4. Kembalikan data user terbaru dalam format JSON ke React
        return response()->json([
            'status'  => 'success',
            'message' => 'Nomor telepon Anda berhasil diperbarui di database.',
            'user'    => $user
        ], 200);
    }

    public function ubahPassword(Request $request)
    {
        $request->validate([
            'Password_Lama' => 'required',
            'Password_Baru' => 'required|min:6',
        ]);

        $user = $request->user();

        if (!Hash::check($request->Password_Lama, $user->Password)) {
            return response()->json(['message' => 'Password lama salah'], 401);
        }

        $user->update(['Password' => Hash::make($request->Password_Baru)]);

        return response()->json(['message' => 'Password berhasil diubah']);
    }

    public function user(Request $request)
    {
        $user = $request->user();
        
        return response()->json([
            'user' => [
                'id' => $user->ID_Pengguna,
                'nama' => $user->Nama_Lengkap,
                'email' => $user->Email,
                'role' => $user->role,
                'verifikasi_status' => $user->verifikasi_status,
                
                // PERBAIKAN: Mengirimkan field No_Telepon & Alamat agar terbaca dinamis oleh Profil.jsx
                'no_telepon' => $user->No_Telepon,
                'noTelepon' => $user->No_Telepon,
                'alamat' => $user->Alamat_Lengkap
            ]
        ]);
    }

    // ========== API UNTUK DASHBOARD NASABAH ==========
    public function dashboardNasabah(Request $request)
    {
        $user = $request->user();

        try {
            $polisAktif = DB::table('polis')->where('ID_Pengguna', $user->ID_Pengguna)->where('status', 'active')->count();
            $totalPolis = DB::table('polis')->where('ID_Pengguna', $user->ID_Pengguna)->count();
            $totalKlaim = DB::table('klaim')->where('ID_Pengguna', $user->ID_Pengguna)->count();
        } catch (\Exception $e) {
            $polisAktif = 0;
            $totalPolis = 0;
            $totalKlaim = 0;
        }

        return response()->json([
            'polisAktif' => $polisAktif,
            'totalPolis' => $totalPolis,
            'totalKlaim' => $totalKlaim,
            'tunggakan' => 0, 
            'aktivitas' => [] 
        ], 200);
    }
}