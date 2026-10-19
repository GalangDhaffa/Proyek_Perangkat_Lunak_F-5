<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/rekap-biaya');
});

Route::get('/rekap-biaya', function () {
    return view('rekap-biaya');
});
