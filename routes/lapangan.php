<?php

use App\Http\Controllers\Lapangan\OperasionalController;
use Illuminate\Support\Facades\Route;

Route::prefix('lapangan')
    ->name('lapangan.')
    ->group(function () {
        Route::get('buka', [OperasionalController::class, 'showUnlock'])->name('unlock');
        Route::post('buka', [OperasionalController::class, 'storeUnlock'])
            ->middleware('throttle:10,1')
            ->name('unlock.store');
        Route::post('kunci', [OperasionalController::class, 'lock'])->name('lock');

        Route::middleware('lapangan.access')->group(function () {
            Route::get('/', [OperasionalController::class, 'index'])->name('index');
            Route::get('pemeliharaan/{slug}', [OperasionalController::class, 'createPemeliharaan'])->name('pemeliharaan.create');
            Route::post('pemeliharaan/{slug}', [OperasionalController::class, 'storePemeliharaan'])->name('pemeliharaan.store');
            Route::get('permohonan/baru', fn () => redirect()->route('lapangan.permohonan.index'))->name('permohonan.create');
            Route::get('permohonan', [OperasionalController::class, 'indexPermohonan'])->name('permohonan.index');
            Route::get('permohonan/{pemangkasan}/progres/{pemangkasanProgres}/pdf', [OperasionalController::class, 'exportProgresPdf'])
                ->name('permohonan.progres.pdf');
            Route::get('permohonan/{pemangkasan}', [OperasionalController::class, 'editPermohonan'])->name('permohonan.edit');
            Route::post('permohonan/{pemangkasan}', [OperasionalController::class, 'updatePermohonan'])->name('permohonan.update');
        });
    });
