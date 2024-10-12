<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Middleware\RoleMiddleware;
use App\Http\Controllers\TipeKamarController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\KamarController;
use App\Http\Controllers\PemesananController;
use App\Http\Controllers\TransaksiController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
Route::post('login', [AuthController::class, 'login']);
Route::post('register', [AuthController::class, 'register']);

Route::middleware(['auth:api', RoleMiddleware::class . ':admin'])->group(function () {
    Route::prefix('tipe-kamar')->group(function () {
        Route::get('/', [TipeKamarController::class, 'index']); // Read
        Route::post('/', [TipeKamarController::class, 'store']); // Create
        Route::put('/{id}', [TipeKamarController::class, 'update']); // Update
        Route::delete('/{id}', [TipeKamarController::class, 'destroy']); // Delete
    });
    Route::prefix('user')->group(function () {
        Route::get('/', [UserController::class, 'index']); // Read
        Route::get('/{id}', [UserController::class, 'show']); // Read by id
        Route::post('/', [UserController::class, 'store']); // Create
        Route::put('/{id}', [UserController::class, 'update']); // Update
        Route::delete('/{id}', [UserController::class, 'destroy']); // Delete
    });
    Route::prefix('kamar')->group(function () {
        Route::get('/', [KamarController::class, 'index']); // Read
        Route::get('/{id}', [KamarController::class, 'show']); // Read by id
        Route::post('/', [KamarController::class, 'store']); // Create
        Route::put('/{id}', [KamarController::class, 'update']); // Update
        Route::delete('/{id}', [KamarController::class, 'destroy']); // Delete
    });
});

// Route untuk resepsionis
Route::middleware(['auth:api', RoleMiddleware::class . ':resepsionis'])->group(function () {
    Route::get('/resepsionis/dashboard', function () {
        return response()->json(['message' => 'Welcome, resepsionis!']);
    });
    Route::get('/pemesanan', [PemesananController::class, 'index']);
    Route::get('/pemesanan/filter', [PemesananController::class, 'filter']);
});

Route::get('/tipe-kamar', [TipeKamarController::class, 'index']); // Read
Route::get('/user', [UserController::class, 'index']); // Read
Route::get('/kamar', [KamarController::class, 'index']); // Read
Route::get('/datakamar',[TransaksiController::class, 'index']);//read data kamar
Route::get('/datakamar/{id}',[TransaksiController::class, 'show']);//read data kamar bedasarkan yang tersedia
Route::get('cekketersediaan',[TransaksiController::class, 'cekKetersediaanKamar']); //red kamar tersedia bedasarkan rentang tanggal
Route::post('/pesankamar',[TransaksiController::class, 'store']); //pesan kamar
Route::get('/pemesanandetail/{id_pemesanan}', [TransaksiController::class, 'getDetailPemesanan']);//get detail
Route::post('/pemesanandetail/{id_pemesanan}', [TransaksiController::class, 'storeDetailPemesanan']);//post detail
Route::post('/check-in/{id_pemesanan}',[TransaksiController::class, 'checkIn']);
Route::post('/check-out/{id_pemesanan}',[TransaksiController::class, 'checkOut']);