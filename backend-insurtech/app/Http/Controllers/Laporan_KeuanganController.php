<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Laporan_Keuangan;

class Laporan_KeuanganController extends Controller
{
    public function index()
    {
        return response()->json(Laporan_Keuangan::all());
    }

    public function show($id)
    {
        return response()->json(Laporan_Keuangan::findOrFail($id));
    }

    public function store(Request $request)
    {
        $laporan = Laporan_Keuangan::create($request->all());
        return response()->json($laporan, 201);
    }

    public function update(Request $request, $id)
    {
        $laporan = Laporan_Keuangan::findOrFail($id);
        $laporan->update($request->all());
        return response()->json($laporan);
    }

    public function destroy($id)
    {
        Laporan_Keuangan::findOrFail($id)->delete();
        return response()->json(['message' => 'Deleted!']);
    }
}