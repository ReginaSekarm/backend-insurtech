<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pengguna;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

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
}