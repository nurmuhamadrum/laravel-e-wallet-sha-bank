<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TopUpController;
use App\Http\Controllers\Api\WebhookController;

Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);
Route::post('webhook', [WebhookController::class, 'update']);

Route::group(['middleware' => 'jwt.verify'], function ($router) {
    Route::post('top-up', [TopUpController::class, 'store']);
});

// Route::middleware(['jwt.verify'])->get('test',function () {
//     return 'success';
// });