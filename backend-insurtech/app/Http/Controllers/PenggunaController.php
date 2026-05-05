<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pengguna;

class PenggunaController extends Controller
{
    public function index()
    {
        return response()->json(Pengguna::all());
    }

    public function show($id)
    {
        return response()->json(Pengguna::findOrFail($id));
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