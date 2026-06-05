<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pengguna;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

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
                'no_telepon' => $user->No_Telepon,
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
            'ktp_verified'      => false,
            'kk_verified'       => false,
        ]);

        return response()->json([
            'message' => 'Registrasi berhasil, silakan tunggu verifikasi KTP/KK',
            'user' => $user
        ], 201);
    }

    // ✅ METHOD BARU — Upload dokumen KTP & KK setelah register
    public function uploadDokumen(Request $request)
    {
        $request->validate([
            'foto_ktp' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'foto_kk'  => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ], [
            'foto_ktp.required' => 'Foto KTP wajib diupload',
            'foto_kk.required'  => 'Foto KK wajib diupload',
            'foto_ktp.mimes'    => 'Format KTP harus JPG, PNG, atau PDF',
            'foto_kk.mimes'     => 'Format KK harus JPG, PNG, atau PDF',
            'foto_ktp.max'      => 'Ukuran KTP maksimal 5MB',
            'foto_kk.max'       => 'Ukuran KK maksimal 5MB',
        ]);

        $user = $request->user();

        // Hapus file lama jika ada
        if ($user->foto_ktp) {
            Storage::disk('public')->delete($user->foto_ktp);
        }
        if ($user->foto_kk) {
            Storage::disk('public')->delete($user->foto_kk);
        }

        // Simpan file baru
        $namaKtp = 'KTP_' . strtoupper(str_replace(' ', '_', $user->Nama_Lengkap)) . '.' . $request->file('foto_ktp')->getClientOriginalExtension();
        $namaKk  = 'KK_'  . strtoupper(str_replace(' ', '_', $user->Nama_Lengkap)) . '.' . $request->file('foto_kk')->getClientOriginalExtension();

        $pathKtp = $request->file('foto_ktp')->storeAs('dokumen_pengguna', $namaKtp, 'public');
        $pathKk  = $request->file('foto_kk')->storeAs('dokumen_pengguna', $namaKk, 'public');

        // Simpan path ke database
        $user->foto_ktp = $pathKtp;
        $user->foto_kk  = $pathKk;
        $user->verifikasi_status = 'pending';
        $user->save();

        return response()->json([
            'status'  => 'success',
            'message' => 'Dokumen berhasil diupload, menunggu verifikasi admin',
            'data' => [
                'foto_ktp' => $pathKtp,
                'foto_kk'  => $pathKk,
            ]
        ], 200);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logout berhasil']);
    }

    public function ubahNomorTelepon(Request $request)
    {
        $request->validate([
            'No_Telepon' => 'required|numeric|digits_between:10,13',
        ], [
            'No_Telepon.required' => 'Nomor telepon baru wajib diisi.',
            'No_Telepon.numeric'  => 'Nomor telepon harus berupa angka.',
            'No_Telepon.digits_between' => 'Nomor telepon harus berukuran antara 10 hingga 13 digit.',
        ]);

        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Sesi autentikasi Anda tidak valid atau kedaluwarsa.'], 401);
        }

        $user->No_Telepon = $request->No_Telepon;
        $user->save();

        return response()->json([
            'status'  => 'success',
            'message' => 'Nomor telepon Anda berhasil diperbarui di database.',
            'user'    => $user
        ], 200);
    }

    public function ubahPassword(Request $request)
    {
        try {
            $user = $request->user();
            
            $validator = Validator::make($request->all(), [
                'current_password' => 'required|string',
                'new_password' => 'required|string|min:8|regex:/^(?=.*[0-9])(?=.*[!@#$%^&*])/',
                'confirm_password' => 'required|same:new_password'
            ], [
                'new_password.min' => 'Password minimal 8 karakter',
                'new_password.regex' => 'Password harus mengandung angka (0-9) dan karakter unik (!@#$%^&*)',
                'confirm_password.same' => 'Konfirmasi password tidak sesuai',
                'current_password.required' => 'Password lama wajib diisi'
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }
            
            if (!Hash::check($request->current_password, $user->Password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Password lama tidak sesuai'
                ], 401);
            }
            
            $user->Password = Hash::make($request->new_password);
            $user->save();
            
            return response()->json([
                'success' => true,
                'message' => 'Password berhasil diubah'
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
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
                'no_telepon' => $user->No_Telepon,
                'noTelepon' => $user->No_Telepon,
                'alamat' => $user->Alamat_Lengkap,
                'ktp_verified' => $user->ktp_verified ?? false,
                'kk_verified' => $user->kk_verified ?? false,
                'foto_ktp' => $user->foto_ktp ?? null,
                'foto_kk' => $user->foto_kk ?? null,
                'ktp_path' => $user->foto_ktp ?? null,
                'kk_path' => $user->foto_kk ?? null,
            ]
        ]);
    }

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