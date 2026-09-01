<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApiController;
use App\Http\Controllers\Analyst\AnalystController;

Route::post('/login',    [ApiController::class, 'login']);
Route::post('/register', [ApiController::class, 'register']);

Route::get('/products',      [ApiController::class, 'products']);
Route::get('/products/{id}', [ApiController::class, 'productShow']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout',           [ApiController::class, 'logout']);
    Route::get('/cart',              [ApiController::class, 'cart']);
    Route::post('/cart',             [ApiController::class, 'cartAdd']);
    Route::delete('/cart/{item}',    [ApiController::class, 'cartRemove']);
    Route::get('/orders',            [ApiController::class, 'orders']);
    Route::get('/analytics',         [AnalystController::class, 'analyticsData']);
});
