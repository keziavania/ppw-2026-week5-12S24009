<?php

use App\Http\Controllers\BukuController;
use App\Http\Controllers\KategoriController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PeminjamanController;

Route::get('/', fn () => redirect()->route('buku.index'));

Route::resource('buku', BukuController::class);
Route::resource('kategori', KategoriController::class);
Route::resource('peminjaman', PeminjamanController::class)->only(['index', 'create', 'store']);
Route::patch('peminjaman/{peminjaman}/kembali', [PeminjamanController::class, 'kembali'])->name('peminjaman.kembali');