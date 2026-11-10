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

    $selectedPeriodeId = request()->query('periodeId');

    return view('periode-payroll', [
        'periods' => $periods,
        'selectedPeriodeId' => $selectedPeriodeId,
    ]);
});

Route::get('/komponen-gaji', function () {
    $periods = Schema::hasTable('payroll_periods')
        ? PayrollPeriod::orderBy('tanggal_mulai', 'desc')->get()
        : collect();

    $selectedPeriodeId = request()->query('periodeId');
    $selectedEmployeeId = request()->query('employeeId', 'EMP-001');

    return view('komponen-gaji', [
        'periods' => $periods,
        'selectedPeriodeId' => $selectedPeriodeId,
        'selectedEmployeeId' => $selectedEmployeeId,
    ]);
});

Route::get('/pratinjau-perhitungan', function () {
    $periods = Schema::hasTable('payroll_periods')
        ? PayrollPeriod::orderBy('tanggal_mulai', 'desc')->get()
        : collect();

    $selectedPeriodeId = request()->query('periodeId');
    $selectedEmployeeId = request()->query('employeeId', 'EMP-001');

    return view('pratinjau-perhitungan', [
        'periods' => $periods,
        'selectedPeriodeId' => $selectedPeriodeId,
        'selectedEmployeeId' => $selectedEmployeeId,
    ]);
});

Route::get('/termin-tenaga-ahli', function () {
    $periods = Schema::hasTable('payroll_periods')
        ? PayrollPeriod::orderBy('tanggal_mulai', 'desc')->get()
        : collect();

    $selectedPeriodeId = request()->query('periodeId');

    return view('termin-tenaga-ahli', [
        'periods' => $periods,
        'selectedPeriodeId' => $selectedPeriodeId,
    ]);
});

Route::get('/rekap-biaya', function () {
    $periods = Schema::hasTable('payroll_periods')
        ? PayrollPeriod::orderBy('tanggal_mulai', 'desc')->get()
        : collect();

    $selectedPeriodeId = request()->query('periodeId', '01a1193d-2500-7c04-854b-8bf03c711dde');

    return view('rekap-biaya', [
        'periods' => $periods,
        'selectedPeriodeId' => $selectedPeriodeId,
    ]);
});
