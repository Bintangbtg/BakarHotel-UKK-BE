<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipeKamar extends Model
{
    protected $table = 'tipe_kamar';

    protected $primaryKey = 'id_tipe_kamar';

    protected $fillable = [
        'nama_tipe_kamar',
        'harga',
        'deskripsi',
        'foto',
    ];

    public $timestamps = false;
}