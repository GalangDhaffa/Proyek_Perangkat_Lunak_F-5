<?php

use App\Http\Controllers\Api\PayrollPeriodController;
use App\Http\Controllers\Api\RekapBiayaController;
use Illuminate\Support\Facades\Route;

Route::prefix('payroll/v1')->group(function () {
    // Rekap Biaya (API-K05-01 / FTR-K05-09)
    Route::get('/rekap-biaya', [RekapBiayaController::class, 'index']);

    // Periode Payroll (API-K05-02 s.d API-K05-05 / FTR-K05-01)
    Route::get('/periode', [PayrollPeriodController::class, 'index']);
    Route::post('/periode', [PayrollPeriodController::class, 'store']);
    Route::get('/periode/{periodeId}', [PayrollPeriodController::class, 'show']);
    Route::put('/periode/{periodeId}', [PayrollPeriodController::class, 'updateFull']);
    Route::patch('/periode/{periodeId}', [PayrollPeriodController::class, 'updatePartial']);
});
