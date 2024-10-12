<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pemesanan extends Model
{
    use HasFactory;

    protected $table = 'pemesanan';
    protected $primaryKey = 'id_pemesanan';
    public $timestamps = false;

    protected $fillable = [
        'nomor_pemesanan',
        'nama_pemesan',
        'email_pemesan',
        'tgl_pemesanan',
        'tgl_check_in',
        'tgl_check_out',
        'nama_tamu',
        'jumlah_kamar',
        'id_tipe_kamar',
        'status_pemesanan',
        'id_user',
    ];

    public function detailPemesanan()
    {
        return $this->hasMany(DetailPemesanan::class, 'id_pemesanan');
    }
}