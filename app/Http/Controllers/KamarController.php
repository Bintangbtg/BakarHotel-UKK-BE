<?php

namespace App\Http\Controllers;

use App\Models\Kamar;
use Illuminate\Http\Request;

class KamarController extends Controller 
{

    public function store(Request $request)
    {
        try{
        $request->validate([
            'nomor_kamar' => 'required|numeric',
            'id_tipe_kamar' => 'required|numeric'
        ]);
        $kamar = Kamar::create($request->all());

        return response()->json($kamar, 201);
    }catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
    }

    // Read
    public function index()
    {
        $kamar = Kamar::all();
        return response()->json($kamar);
    }

    public function show($id)
    {
        $kamar = Kamar::findOrFail($id);
        return response()->json($kamar);
    }

    // Update
    public function update(Request $request, $id)
    {
        $request->validate([
            'nomor_kamar' => 'sometimes|numeric',
            'id_tipe_kamar' => 'sometimes|numeric'
        ]);

        $kamar = Kamar::findOrFail($id);
        $kamar->update($request->all());

        return response()->json($kamar);
    }

    // Delete
    public function destroy($id)
    {
        $kamar = Kamar::findOrFail($id);
        $kamar->delete();

        return response()->json([
            'message' => 'Kamar deleted successfully.'
        ]);
    }
}