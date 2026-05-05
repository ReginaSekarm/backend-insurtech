<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Klaim;

class KlaimController extends Controller
{
    public function index()
    {
        return response()->json(Klaim::all());
    }

    public function show($id)
    {
        return response()->json(Klaim::findOrFail($id));
    }

    public function store(Request $request)
    {
        $klaim = Klaim::create($request->all());
        return response()->json($klaim, 201);
    }

    public function update(Request $request, $id)
    {
        $klaim = Klaim::findOrFail($id);
        $klaim->update($request->all());
        return response()->json($klaim);
    }

    public function destroy($id)
    {
        Klaim::findOrFail($id)->delete();
        return response()->json(['message' => 'Deleted!']);
    }
}