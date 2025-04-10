<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;


Route::middleware('auth')->group(function () {
    Route::get('/tour/start', [HomeController::class, 'index'])
    ->name('client.start');
    Route::post('/tour/detail', [HomeController::class, 'getTourById'])
    ->name('tour.detail');
    Route::post('/tour/submit', [HomeController::class, 'tourSubmit'])->name('tour.submit');
});
