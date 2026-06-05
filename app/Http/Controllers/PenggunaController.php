<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pengguna;

class PenggunaController extends Controller
{
    public function index()
    {
        $pengguna = Pengguna::withCount(['polis', 'klaim'])->get();
        return response()->json($pengguna);
    }

    public function show($id)
    {
        $pengguna = Pengguna::withCount(['polis', 'klaim'])->findOrFail($id);
        return response()->json($pengguna);
    }

    public function store(Request $request)
    {
        $pengguna = Pengguna::create($request->all());
        return response()->json($pengguna, 201);
    }

    public function update(Request $request, $id)
    {
        $pengguna = Pengguna::findOrFail($id);
        $pengguna->update($request->all());
        return response()->json($pengguna);
    }

    public function destroy($id)
    {
        Pengguna::findOrFail($id)->delete();
        return response()->json(['message' => 'Deleted!']);
    }
}