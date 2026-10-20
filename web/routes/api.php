<?php

use App\Http\Controllers\Api\RekapBiayaController;
use Illuminate\Support\Facades\Route;

Route::prefix('payroll/v1')->group(function () {
    Route::get('/rekap-biaya', [RekapBiayaController::class, 'index']);
});
