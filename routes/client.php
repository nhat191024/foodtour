<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('client.home');
})->name('client.home');

Route::get('/select-food/{slug}', [App\Http\Controllers\Client\HomeController::class, 'categories'])
    ->name('client.select-food');

Route::get('/select-time', function () {
    return view('client.select-time');
})->name('client.select-time');
