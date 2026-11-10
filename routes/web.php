<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BukuController;
use App\Http\Controllers\KategoriController;
use App\Http\Controllers\PeminjamanController;
use App\Http\Controllers\Soap\LibrarySoapController;
use Illuminate\Support\Facades\Route;

// ==========================================
// 1. ROUTE LOGIN (GUEST)
// ==========================================

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])
        ->name('login');

    Route::post('/login', [AuthController::class, 'login'])
        ->name('login.attempt');
});

// ==========================================
// 2. ROUTE PERPUSTAKAAN (WEEK 6)
// ==========================================

Route::middleware('auth')->group(function () {

    // Logout
    Route::post('/logout', [AuthController::class, 'logout'])
        ->name('logout');

    // Halaman utama
    Route::get('/', fn () => redirect()->route('buku.index'));

    // CRUD Buku
    Route::resource('buku', BukuController::class);

    // CRUD Kategori
    Route::resource('kategori', KategoriController::class);

    // Peminjaman Buku
    Route::resource('peminjaman', PeminjamanController::class)
        ->only(['index', 'create', 'store']);

    // Pengembalian Buku
    Route::patch(
        'peminjaman/{peminjaman}/kembali',
        [PeminjamanController::class, 'kembali']
    )->name('peminjaman.kembali');

});

// ==========================================
// 3. ROUTE SOAP (WEEK 9)
// ==========================================

// Menampilkan kontrak WSDL
Route::get('/soap/sipus.wsdl', [
    LibrarySoapController::class,
    'wsdl'
]);

// Memproses request SOAP
Route::post('/soap/sipus', [
    LibrarySoapController::class,
    'handle'
]);

