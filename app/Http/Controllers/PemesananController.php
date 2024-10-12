<?php
namespace App\Http\Controllers;

use App\Models\Pemesanan;
use Illuminate\Http\Request;

class PemesananController extends Controller
{
    public function index()
    {
        $pemesanan = Pemesanan::all();
        return response()->json(['pemesanan' => $pemesanan], 200);
    }

    public function filter(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date',
        ]);

        $pemesanan = Pemesanan::whereBetween('tgl_check_in', [$request->start_date, $request->end_date])->get();
        return response()->json(['pemesanan' => $pemesanan], 200);
    }
}