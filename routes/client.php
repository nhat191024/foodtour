<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;


Route::middleware('auth')->group(function () {
    Route::get('/tour/start', [HomeController::class, 'index'])
    ->name('client.start');
    Route::post('/tour/detail', [HomeController::class, 'getTourById'])
    ->name('tour.detail');
    Route::post('/tour/submit', [HomeController::class, 'tourSubmit'])->name('tour.submit');
    Route::post('/tour-item/disable', [HomeController::class, 'disableTourItem'])
    ->name('tour-item.disable');
    Route::post('/tour-item/new', [HomeController::class, 'getNewTourItem'])
    ->name('tour-item.new');
});
