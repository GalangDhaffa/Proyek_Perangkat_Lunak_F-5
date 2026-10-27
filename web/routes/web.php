<?php

use App\Models\PayrollPeriod;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

Route::get('/', function () {
    return redirect('/rekap-biaya');
});

Route::get('/periode-payroll', function () {
    $periods = Schema::hasTable('payroll_periods')
        ? PayrollPeriod::orderBy('tanggal_mulai', 'desc')->get()
        : collect();

    return view('periode-payroll', ['periods' => $periods]);
});

Route::get('/rekap-biaya', function () {
    return view('rekap-biaya');
});
