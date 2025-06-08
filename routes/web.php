<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RedirectPaymentController;
use Illuminate\Support\Facades\Redirect;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/payment-finish', function () {
    return view('payment-finish', [RedirectPaymentController::class, 'finish']);
});
