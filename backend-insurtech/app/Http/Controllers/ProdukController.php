<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Produk;

class ProdukController extends Controller
{
    public function index()
    {
        return response()->json(Produk::all());
    }

    public function show($id)
    {
        return response()->json(Produk::findOrFail($id));
    }

    public function store(Request $request)
    {
        $produk = Produk::create($request->all());
        return response()->json($produk, 201);
    }

    public function update(Request $request, $id)
    {
        $produk = Produk::findOrFail($id);
        $produk->update($request->all());
        return response()->json($produk);
    }

    public function destroy($id)
    {
        Produk::findOrFail($id)->delete();
        return response()->json(['message' => 'Deleted!']);
    }
}