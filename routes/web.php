<?php

use App\Http\Controllers\TransaksiController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/pemesanan/{id_pemesanan}/print', [TransaksiController::class, 'printNota']);
Route::get('/printnota/{id_pemesanan}', [TransaksiController::class, 'downloadNota']);