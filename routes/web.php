<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});


// Route::get('/scan-qr', function () {
//     return view('scan-qr');
// });
