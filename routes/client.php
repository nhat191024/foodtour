<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\TourCalculatorController;

Route::middleware('auth')->group(function () {
    Route::get('/tour/start', [HomeController::class, 'index'])
        ->name('client.start');
    Route::get('/tour/favorite', [HomeController::class, 'favorite'])
        ->name('tour.favorite');
    Route::post('/tour/detail', [HomeController::class, 'getTourById'])
        ->name('tour.detail');
    Route::post('/tour/submit', [HomeController::class, 'tourSubmit'])
        ->name('tour.submit');
    Route::post('/tour-item/disable', [HomeController::class, 'disableTourItem'])
        ->name('tour-item.disable');
    Route::post('/tour-item/new', [HomeController::class, 'getNewTourItem'])
        ->name('tour-item.new');

    Route::prefix('api')->group(function () {
        // favorite a tour item
        Route::post('/tour/favorite', [HomeController::class, 'favoriteTourItem'])->name('api.tour-item.favorite');

        // rename a tour
        Route::post('/tour/rename', [HomeController::class, 'renameTour'])->name('api.tour-item.rename');
    });
});

Route::prefix('api')->group(function () {
    Route::get('/search-locations', [HomeController::class, 'searchLocations'])
        ->name('api.search-locations');

    Route::post('/get-current-weather', [HomeController::class, 'getCurrentWeather'])
        ->name('api.get-current-weather');

    Route::post('/get-weather-forecast', [HomeController::class, 'getCurrentWeather'])
        ->name('api.get-weather-forecast');

    Route::get('/get-all-tours', [TourCalculatorController::class, 'getAllTours'])
        ->name('api.get-all-tours');

    Route::get('/get-tour-items/{tourId}', [TourCalculatorController::class, 'getTourItemsByTourId'])
        ->name('api.get-all-tour-items');
});
