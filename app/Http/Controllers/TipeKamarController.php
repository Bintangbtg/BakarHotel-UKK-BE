<?php

namespace App\Http\Controllers;

use App\Models\TipeKamar;
use Illuminate\Http\Request;

class TipeKamarController extends Controller
{
    // Create
    // public function store(Request $request)
    // {
    //     try{
    //     $request->validate([
    //         'nama_tipe_kamar' => 'required|string|max:255',
    //         'harga' => 'required|numeric',
    //         'deskripsi' => 'required|string',
    //         'foto' => 'required|string',
    //     ]);
    //     $tipeKamar = TipeKamar::create($request->all());

    //     return response()->json($tipeKamar, 201);
    // }catch (\Exception $e) {
    //     return response()->json(['error' => $e->getMessage()], 500);
    // }
    // }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'nama_tipe_kamar' => 'required|string|max:255',
                'harga' => 'required|numeric',
                'deskripsi' => 'required|string',
                'foto' => 'required|file|image|max:2048', // Ensure the uploaded file is an image
            ]);

            // Handle the file upload
            if ($request->hasFile('foto')) {
                $file = $request->file('foto');
                $filename = time() . '_' . $file->getClientOriginalName();
                $file->move(public_path('uploads'), $filename);
            }

            // Create a new record with only the filename for 'foto'
            $tipeKamar = TipeKamar::create([
                'nama_tipe_kamar' => $request->nama_tipe_kamar,
                'harga' => $request->harga,
                'deskripsi' => $request->deskripsi,
                'foto' => $filename,
            ]);

            return response()->json($tipeKamar, 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // Read
    public function index()
    {
        $tipeKamar = TipeKamar::all();
        return response()->json($tipeKamar);
    }

    // Update
    // public function update(Request $request, $id)
    // {
    //     $request->validate([
    //         'nama_tipe_kamar' => 'sometimes|string|max:255',
    //         'harga' => 'sometimes|numeric',
    //         'deskripsi' => 'sometimes|string',
    //         'foto' => 'sometimes|string',
    //     ]);

    //     $tipeKamar = TipeKamar::findOrFail($id);
    //     $tipeKamar->update($request->all());

    //     return response()->json($tipeKamar);
    // }

    public function update(Request $request, $id)
    {
        try {
            $request->validate([
                'nama_tipe_kamar' => 'sometimes|string|max:255',
                'harga' => 'sometimes|numeric',
                'deskripsi' => 'sometimes|string',
                'foto' => 'sometimes|file|image|max:2048', // Ensure the uploaded file is an image
            ]);

            $tipeKamar = TipeKamar::findOrFail($id);

            if ($request->hasFile('foto')) {
                // Handle the new file upload
                $file = $request->file('foto');
                $filename = time() . '_' . $file->getClientOriginalName();
                $file->move(public_path('uploads'), $filename);

                // Optionally, delete the old file if exists
                if ($tipeKamar->foto && file_exists(public_path('uploads/' . $tipeKamar->foto))) {
                    unlink(public_path('uploads/' . $tipeKamar->foto));
                }

                // Update 'foto' field with the new filename
                $tipeKamar->foto = $filename;
            }

            // Update other fields
            $tipeKamar->update($request->except('foto')); // Use except to avoid overriding 'foto' with null if no new file

            return response()->json($tipeKamar);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // Delete
    public function destroy($id)
    {
        $tipeKamar = TipeKamar::findOrFail($id);
        $tipeKamar->delete();

        return response()->json(null, 204);
    }
}