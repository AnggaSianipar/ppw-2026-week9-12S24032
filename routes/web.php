<?php

use App\Http\Controllers\BukuController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KategoriController;
use App\Http\Controllers\MahasiswaController;
use App\Http\Controllers\PeminjamanController;
use App\Http\Controllers\Soap\LibrarySoapController;
use Illuminate\Support\Facades\Route;

// Endpoint kompatibilitas untuk aplikasi Yudisium legacy berbasis SOAP.
Route::get('/soap/sipus.wsdl', [LibrarySoapController::class, 'wsdl']);
Route::post('/soap/sipus', [LibrarySoapController::class, 'handle']);

//login hanya bisa diakses oleh user yang belum login
Route::get('/', function () {
    return redirect()->route('login');
});
//login hanya bisa diakses oleh user yang belum login
Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.store');
});
//logout hanya bisa diakses oleh user yang sudah login
Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');
//dashboard hanya bisa diakses oleh user yang sudah login
Route::middleware('auth')->group(function (): void {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
});
//mahasiswa, buku, kategori hanya bisa diakses oleh user dengan role admin
Route::middleware(['auth', 'role:admin'])->group(function (): void {
    Route::resource('mahasiswa', MahasiswaController::class);
    Route::get('/mahasiswa/{mahasiswa}/peminjaman', [MahasiswaController::class, 'peminjaman'])
        ->name('mahasiswa.peminjaman');
    Route::resource('buku', BukuController::class)->except(['index', 'show']);
    Route::resource('kategori', KategoriController::class)->except(['index', 'show']);
});
//peminjaman hanya bisa diakses oleh user dengan role admin
Route::middleware(['auth', 'role:admin'])->group(function (): void {
    Route::get('/peminjaman', [PeminjamanController::class, 'index'])->name('peminjaman.index');
    Route::get('/peminjaman/create', [PeminjamanController::class, 'create'])->name('peminjaman.create');
    Route::post('/peminjaman', [PeminjamanController::class, 'store'])->name('peminjaman.store');
    Route::patch('/peminjaman/{peminjaman}/return', [PeminjamanController::class, 'return'])
        ->name('peminjaman.return');
});
//mahasiswa hanya bisa read
Route::middleware('auth')->group(function (): void {
    Route::resource('buku', BukuController::class)->only(['index', 'show']);
    Route::resource('kategori', KategoriController::class)->only(['index', 'show']);
});