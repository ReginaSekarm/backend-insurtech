<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Produk;
use Illuminate\Http\Request;

class ProdukController extends Controller
{
    public function index()
    {
        $produk = Produk::all();
        return response()->json($produk);
    }

    public function show($id)
    {
        $produk = Produk::findOrFail($id);
        return response()->json($produk);
    }

    public function store(Request $request)
    {
        $request->validate([
            'Nama_Produk'      => 'required|string|max:100',
            'Deskripsi_Produk' => 'nullable|string',
            'Harga_Premi'      => 'required|numeric|min:0',
        ]);

        $count = Produk::count() + 1;
        $newId = 'PRD' . str_pad($count, 5, '0', STR_PAD_LEFT);

        dd($newId, auth()->user());

        $produk = Produk::create([
            'ID_Produk'        => $newId,
            'Nama_Produk'      => $request->Nama_Produk,
            'Deskripsi_Produk' => $request->Deskripsi_Produk,
            'Harga_Premi'      => $request->Harga_Premi,
            'status'           => 'draft',
            'created_by'       => auth()->user()->ID_Pengguna,
        ]);

        return response()->json([
            'message' => 'Produk berhasil ditambahkan',
            'data'    => $produk
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $produk = Produk::findOrFail($id);

        $request->validate([
            'Nama_Produk'      => 'sometimes|string|max:100',
            'Deskripsi_Produk' => 'nullable|string',
            'Harga_Premi'      => 'sometimes|numeric|min:0',
            'status'           => 'sometimes|in:draft,published,archived'
        ]);

        $produk->update($request->only([
            'Nama_Produk', 'Deskripsi_Produk', 'Harga_Premi', 'status'
        ]));

        return response()->json([
            'message' => 'Produk berhasil diupdate',
            'data'    => $produk
        ]);
    }

    public function destroy($id)
    {
        $produk = Produk::findOrFail($id);
        $produk->delete();

        return response()->json(['message' => 'Produk berhasil dihapus']);
    }

    public function publish($id)
    {
        $produk = Produk::findOrFail($id);
        $produk->update([
            'status'       => 'published',
            'published_at' => now()
        ]);

        return response()->json([
            'message' => 'Produk berhasil dipublikasikan',
            'data'    => $produk
        ]);
    }
}