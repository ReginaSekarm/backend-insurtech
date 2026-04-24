<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Polis;

class PolisController extends Controller
{
    public function index()
    {
        return response()->json(Polis::all());
    }

    public function show($id)
    {
        return response()->json(Polis::findOrFail($id));
    }

    public function store(Request $request)
    {
        $polis = Polis::create($request->all());
        return response()->json($polis, 201);
    }

    public function update(Request $request, $id)
    {
        $polis = Polis::findOrFail($id);
        $polis->update($request->all());
        return response()->json($polis);
    }

    public function destroy($id)
    {
        Polis::findOrFail($id)->delete();
        return response()->json(['message' => 'Deleted!']);
    }
}