<?php
namespace App\Http\Controllers;

use App\Models\Kamar;
use App\Models\Pemesanan;
use App\Models\DetailPemesanan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Dompdf\Dompdf;
use Carbon\Carbon;
use App\Models\TipeKamar;

class TransaksiController extends Controller
{
    public function index()
    {
        // Mengambil data kamar beserta tipe kamarnya
        $kamar = Kamar::with('tipeKamar')->get();

        return response()->json($kamar);
    }

    public function show($id)
    {
        try {
            // Ambil data kamar berdasarkan id_tipe_kamar yang diberikan dan periksa ketersediaannya
            $kamar = Kamar::with('tipeKamar')
                ->where('id_tipe_kamar', $id)
                ->where(function($query) {
                    // Kamar tersedia jika tgl_checkin dan tgl_checkout kosong atau check-out sudah berlalu
                    $query->whereNull('tgl_checkin')
                        ->orWhere('tgl_checkout', '<', now());
                })
                ->get();

            // Jika data kamar ditemukan
            if ($kamar->isNotEmpty()) {
                return response()->json($kamar, 200);
            }

            // Jika tidak ada kamar yang ditemukan untuk id_tipe_kamar tersebut
            return response()->json([
                'message' => 'Kamar tidak ditemukan atau tidak tersedia untuk tipe kamar ini'
            ], 404);

        } catch (\Exception $e) {
            // Jika terjadi error
            return response()->json([
                'message' => 'Terjadi kesalahan!',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try{
            $request->validate([
            'nomor_pemesanan' => 'required|integer',
            'nama_pemesan' => 'required|string|max:100',
            'email_pemesan' => 'required|email|max:100',
            'tgl_check_in' => 'required|date',
            'tgl_check_out' => 'required|date',
            'nama_tamu' => 'required|string|max:100',
            'jumlah_kamar' => 'required|integer',
            'id_tipe_kamar' => 'required|integer|exists:tipe_kamar,id_tipe_kamar',
            'status_pemesanan' => 'required|in:baru,check_in,check_out',
            'id_user' => 'required|integer|exists:users,id_user', // Assuming you have a users table
        ]);
            $pemesanan = Pemesanan::create($request->all());
                return response()->json($pemesanan, 201);
            }catch (\Exception $e) {
                return response()->json(['error' => $e->getMessage()], 500);
            }
        }

        public function getDetailPemesanan($id_pemesanan)
        {
            try {
                // Mengambil data detail_pemesanan dengan informasi kamar dan tipe kamar
                $details = DetailPemesanan::select('detail_pemesanan.*', 'kamar.nomor_kamar', 'tipe_kamar.nama_tipe_kamar')
                    ->join('kamar', 'detail_pemesanan.id_kamar', '=', 'kamar.id_kamar')
                    ->join('tipe_kamar', 'kamar.id_tipe_kamar', '=', 'tipe_kamar.id_tipe_kamar')
                    ->where('detail_pemesanan.id_pemesanan', $id_pemesanan)
                    ->get();

                // Jika data ditemukan, kembalikan respon sukses
                if ($details->isNotEmpty()) {
                    return response()->json([
                        'message' => 'Detail pemesanan ditemukan',
                        'data' => $details
                    ], 200);
                }

                // Jika tidak ada data yang ditemukan
                return response()->json([
                    'message' => 'Detail pemesanan tidak ditemukan'
                ], 404);

            } catch (\Exception $e) {
                // Jika ada error, log error dan kembalikan respon error
                Log::error('Error saat mengambil detail pemesanan: ' . $e->getMessage());
                return response()->json([
                    'message' => 'Terjadi kesalahan saat mengambil detail pemesanan!',
                    'error' => $e->getMessage(),
                ], 500);
            }
        }

        public function storeDetailPemesanan(Request $request, $id_pemesanan)
        {
            try {
                $request->validate([
                    'details' => 'required|array',
                    'details.*.id_kamar' => 'required|integer|exists:kamar,id_kamar',
                ]);

                // Ambil data pemesanan berdasarkan id_pemesanan
                $pemesanan = Pemesanan::findOrFail($id_pemesanan);

                // Hitung jumlah kamar yang akan dipesan
                $jumlahKamarDipesan = count($request->details);

                // Cek apakah jumlah kamar yang akan dipesan sesuai dengan jumlah_kamar di pemesanan
                if ($jumlahKamarDipesan != $pemesanan->jumlah_kamar) {
                    return response()->json([
                        'error' => "Anda memesan {$pemesanan->jumlah_kamar} kamar. Mohon sesuaikan dengan input."
                    ], 400);
                }

                // Hitung jumlah hari antara tanggal check-in dan check-out
                $checkIn = Carbon::parse($pemesanan->tgl_check_in);
                $checkOut = Carbon::parse($pemesanan->tgl_check_out);
                $jumlahHari = $checkIn->diffInDays($checkOut);

                foreach ($request->details as $detail) {
                    // Ambil data kamar berdasarkan id_kamar
                    $kamar = Kamar::find($detail['id_kamar']);

                    if (!$kamar) {
                        return response()->json(['error' => "Kamar dengan ID {$detail['id_kamar']} tidak ditemukan."], 404);
                    }

                    // Pastikan id_tipe_kamar dari kamar sesuai dengan pemesanan
                    if ($kamar->id_tipe_kamar != $pemesanan->id_tipe_kamar) {
                        return response()->json(['error' => 'Tipe kamar tidak sesuai dengan pemesanan'], 400);
                    }

                    // Cek ketersediaan kamar
                    if ($kamar->tgl_checkin == $pemesanan->tgl_check_in && $kamar->tgl_checkout == $pemesanan->tgl_check_out && $pemesanan->tgl_check_in >= now()) {
                        return response()->json(['error' => 'Kamar tidak tersedia karena sedang dipesan'], 400);
                    }

                    $tipeKamar = TipeKamar::find($kamar->id_tipe_kamar);
                    $hargaPerMalam = $tipeKamar->harga;

                    // Hitung total harga
                    $totalHarga = $hargaPerMalam * $jumlahHari;

                    // Simpan detail pemesanan
                    DetailPemesanan::create([
                        'id_pemesanan' => $id_pemesanan,
                        'id_kamar' => $detail['id_kamar'],
                        'tgl_akses' => $pemesanan->tgl_check_in,
                        'harga' => $totalHarga,
                    ]);
                    // Update tgl_checkin dan tgl_checkout untuk kamar yang dipesan
                    $kamar->update([
                        'tgl_checkin' => $pemesanan->tgl_check_in,
                        'tgl_checkout' => $pemesanan->tgl_check_out
                    ]);
                }

                return response()->json(['message' => 'Detail pemesanan berhasil disimpan'], 201);

            } catch (\Exception $e) {
                return response()->json(['error' => $e->getMessage()], 500);
            }
        }

        public function checkIn($id_pemesanan)
        {
            try {
                // Temukan pemesanan berdasarkan ID
                $pemesanan = Pemesanan::findOrFail($id_pemesanan);

                // Perbarui status pemesanan menjadi 'check_in'
                $pemesanan->update(['status_pemesanan' => 'check_in']);

                return response()->json([
                    'message' => 'Pemesanan berhasil di-check-in',
                    'data' => $pemesanan
                ], 200);

            } catch (\Exception $e) {
                return response()->json(['error' => $e->getMessage()], 500);
            }
        }

        public function checkOut($id_pemesanan)
        {
            try {
                // Temukan pemesanan berdasarkan ID
                $pemesanan = Pemesanan::findOrFail($id_pemesanan);

                // Perbarui status pemesanan menjadi 'check_out'
                $pemesanan->update(['status_pemesanan' => 'check_out']);

                // Ambil detail pemesanan untuk mengupdate kamar
                $detailPemesanan = DetailPemesanan::where('id_pemesanan', $id_pemesanan)->get();

                foreach ($detailPemesanan as $detail) {
                    // Temukan kamar yang terkait
                    $kamar = Kamar::findOrFail($detail->id_kamar);

                    // Update status kamar menjadi 'iya' (tersedia)
                    $kamar->update(['tersedia' => 'iya']);
                }

                return response()->json([
                    'message' => 'Pemesanan berhasil di-check-out dan kamar diperbarui',
                    'data' => $pemesanan
                ], 200);

            } catch (\Exception $e) {
                return response()->json(['error' => $e->getMessage()], 500);
            }
        }

        public function printNota($id_pemesanan)
        {
            try {
                $pemesanan = Pemesanan::with(['detailPemesanan.kamar'])->findOrFail($id_pemesanan);
                
                if (request()->wantsJson()) {
                    return response()->json($pemesanan, 200);
                }
        
                $dataNota = [
                    'nama_hotel' => 'Bakar Hotel',
                    'tgl_transaksi' => $pemesanan->tgl_check_in, // Format sesuai database
                    'nama_pemesan' => $pemesanan->nama_pemesan,
                    'email_pemesan' => $pemesanan->email_pemesan,
                    'tgl_check_in' => $pemesanan->tgl_check_in,
                    'tgl_check_out' => $pemesanan->tgl_check_out,
                    'nama_tamu' => $pemesanan->nama_tamu,
                    'jumlah_kamar' => $pemesanan->jumlah_kamar,
                    'detail_kamar' => $pemesanan->detailPemesanan->map(function ($detail) {
                        return [
                            'id_kamar' => $detail->kamar->id_kamar,
                            'tgl_akses' => $detail->tgl_akses,
                            'harga' => $detail->harga,
                        ];
                    }),
                ];

                // if (request()->has('download') && request()->get('download') === 'pdf') {
                //     $pdf = \PDF::loadView('nota', compact('dataNota'));
        
                //     // Unduh PDF dengan nama file 'nota-pemesanan.pdf'
                //     return $pdf->download('nota-pemesanan.pdf');
                // }
        
                return view('nota', compact('dataNota'));
        
            } catch (ModelNotFoundException $e) {
                return response()->json(['error' => 'Pemesanan tidak ditemukan.'], 404);
            } catch (\Exception $e) {
                return response()->json(['error' => $e->getMessage()], 500);
            }
        }

        public function downloadNota($id_pemesanan)
        {
            try {
                $pemesanan = Pemesanan::with(['detailPemesanan.kamar'])->findOrFail($id_pemesanan);
                
                $dataNota = [
                    'nama_hotel' => 'Bakar Hotel',
                    'tgl_transaksi' => $pemesanan->tgl_check_in, // Format sesuai database
                    'nama_pemesan' => $pemesanan->nama_pemesan,
                    'email_pemesan' => $pemesanan->email_pemesan,
                    'tgl_check_in' => $pemesanan->tgl_check_in,
                    'tgl_check_out' => $pemesanan->tgl_check_out,
                    'nama_tamu' => $pemesanan->nama_tamu,
                    'jumlah_kamar' => $pemesanan->jumlah_kamar,
                    'detail_kamar' => $pemesanan->detailPemesanan->map(function ($detail) {
                        return [
                            'id_kamar' => $detail->kamar->id_kamar,
                            'tgl_akses' => $detail->tgl_akses,
                            'harga' => $detail->harga,
                        ];
                    }),
                ];

                // Generate PDF and download it
                $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('nota', compact('dataNota'));

                // Return the PDF file as a download with the given filename
                return $pdf->download('nota-pemesanan.pdf');

            } catch (ModelNotFoundException $e) {
                return response()->json(['error' => 'Pemesanan tidak ditemukan.'], 404);
            } catch (\Exception $e) {
                return response()->json(['error' => $e->getMessage()], 500);
            }
        }

        public function cekKetersediaanKamar(Request $request)
        {
            $tglCheckIn = Carbon::parse($request->query('tgl_check_in'));
            $tglCheckOut = Carbon::parse($request->query('tgl_check_out'));
            $today = Carbon::now();

            // Validasi bahwa check-in yang diminta harus lebih besar dari hari ini
            if ($tglCheckIn->lessThanOrEqualTo($today) || $tglCheckOut->lessThanOrEqualTo($today)) {
                return response()->json([
                    'message' => 'Tanggal check-in dan check-out harus lebih besar dari hari ini'
                ], 400);
            }

            // Query untuk mengecek ketersediaan kamar tanpa overlap dan tanggal lebih besar dari hari ini
            $availableKamar = Kamar::where(function ($query) use ($tglCheckIn, $tglCheckOut) {
                // Pastikan kamar hanya tersedia jika tidak ada bentrok
                $query->where(function ($q) use ($tglCheckIn, $tglCheckOut) {
                    // Cek tidak ada overlapping antara check-in dan check-out yang diminta
                    $q->where('tgl_checkout', '<', $tglCheckIn) // Check-out selesai sebelum check-in baru
                    ->orWhere('tgl_checkin', '>', $tglCheckOut); // Check-in dimulai setelah check-out baru selesai
                });
            })->get();

            if ($availableKamar->isNotEmpty()) {
                return response()->json([
                    'message' => 'Kamar tersedia',
                    'kamar' => $availableKamar
                ], 200);
            }

            return response()->json(['message' => 'Tidak ada kamar yang tersedia pada rentang waktu ini'], 404);
        }
    }