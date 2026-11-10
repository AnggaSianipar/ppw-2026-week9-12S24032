<?php

use App\Http\Controllers\Api\V1\AuthApiController;
use App\Http\Controllers\Api\V1\BukuApiController;
use App\Http\Controllers\Api\V1\ExternalBookController;
use App\Http\Controllers\Api\V1\PeminjamanApiController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->name('api.v1.')->group(function (): void {
    Route::post('/auth/login', [AuthApiController::class, 'login'])
        ->name('auth.login');

    Route::get('/bukus', [BukuApiController::class, 'index'])
        ->name('bukus.index');

    Route::get('/bukus/{buku}/referensi-eksternal', [
        ExternalBookController::class,
        'show',
    ])->name('bukus.external');

    Route::get('/bukus/{buku}', [BukuApiController::class, 'show'])
        ->name('bukus.show');

    Route::middleware('auth:sanctum')->group(function (): void {
        Route::get('/auth/me', [AuthApiController::class, 'me'])
            ->name('auth.me');

        Route::post('/auth/logout', [AuthApiController::class, 'logout'])
            ->name('auth.logout');

        Route::post('/bukus', [BukuApiController::class, 'store'])
            ->name('bukus.store');

        Route::put('/bukus/{buku}', [BukuApiController::class, 'update'])
            ->name('bukus.update');

        Route::patch('/bukus/{buku}', [BukuApiController::class, 'partialUpdate'])
            ->name('bukus.partial-update');

        Route::delete('/bukus/{buku}', [BukuApiController::class, 'destroy'])
            ->name('bukus.destroy');

        Route::get('/peminjamans', [PeminjamanApiController::class, 'index'])
            ->name('peminjamans.index');

        Route::get('/peminjamans/{peminjaman}', [PeminjamanApiController::class, 'show'])
            ->name('peminjamans.show');

        Route::post('/peminjamans', [PeminjamanApiController::class, 'store'])
            ->name('peminjamans.store');

        Route::patch('/peminjamans/{peminjaman}/kembalikan', [
            PeminjamanApiController::class,
            'return',
        ])->name('peminjamans.return');
    });
});