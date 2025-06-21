<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

use App\Service\AIService;
use App\Http\Controllers\Settings\ProfileController;
use App\Http\Controllers\Client\HistoryController;
use App\Http\Controllers\Client\HomeController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Client\SurveyController;
use App\Http\Controllers\Client\ProfileController as ClientProfileController;
use App\Http\Controllers\Client\ToolboxController;

// routes start here
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/home', [HomeController::class, 'index'])->name('home');
Route::get('/tour/start', [SurveyController::class, 'index'])->name('survey.start');
// avoid spamming
Route::post('/tour/complete', [SurveyController::class, 'store'])->middleware('throttle:3,1')->name('survey.store');
Route::get('/tour/result/{id}', [HistoryController::class, 'detail'])->name('survey.result');
Route::get('/tour/history', [HistoryController::class, 'index'])->name('history.index');
Route::get('/login', [LoginController::class, 'login'])->name('login');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
Route::post('/login/submit', [LoginController::class, 'loginSubmit'])->name('login.submit');
Route::get('/register', [LoginController::class, 'register'])->name('register');
Route::post('/register/submit', [LoginController::class, 'registerSubmit'])->name('register.submit');

Route::get('/profile', [ProfileController::class, 'index'])->name('profile');
Route::get('/profile/favorite', [ClientProfileController::class, 'index'])->name('profile.favorite');
Route::get('/profile/delete', [ProfileController::class, 'destroy'])->name('profile.destroy');
Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
Route::patch('/profile/update', [ProfileController::class, 'update'])->name('profile.update');
Route::get('/settings/password', [ProfileController::class, 'changePassword'])->name('profile.password');
Route::get('/settings/appearance', [ProfileController::class, 'changeAppearance'])->name('profile.appearance');
Route::get('/settings/profile', [ProfileController::class, 'index']);
Route::get('/dashboard', [ProfileController::class, 'index'])->name('dashboard');

Route::get('auth/google', [LoginController::class, 'redirectToGoogle'])->name('login.google');
Route::get('/auth/google/callback', [LoginController::class, 'handleGoogleCallback'])->name('auth.google.callback');

Route::post('/sightseeing/{sightseeing}/toggle-favorite', [HistoryController::class, 'toggleSightseeingFavorite'])
    ->name('sightseeing.toggle-favorite')
    ->middleware('auth');
Route::post('/food/{food}/toggle-favorite', [HistoryController::class, 'toggleFoodFavorite'])
    ->name('food.toggle-favorite')
    ->middleware('auth');

Route::get('/weathercast', [ToolboxController::class, 'weather'])->name('weathercast');
Route::get('/calculator/tour/{id?}', [ToolboxController::class, 'calculator'])->name('calculator');
Route::get('/search-locations', [ToolboxController::class, 'searchLocations'])->name('search-locations');
Route::get('/weathercast/get', [ToolboxController::class, 'getWeather'])->name('get-weather');

Route::post('/calculator/tour/{history}/add-cost', [ToolboxController::class, 'storeTripCost'])
    ->name('calculator.store_cost')
    ->middleware('auth');

Route::delete('/trip-costs/{cost}', [ToolboxController::class, 'destroyTripCost'])
    ->name('calculator.destroy_cost')
    ->middleware('auth');

Route::middleware(['auth'])->group(function () {
    Route::delete('/history-food/{food}', [HistoryController::class, 'destroyFood'])->name('history.food.destroy');
    Route::delete('/history-sightseeing/{sightseeing}', [HistoryController::class, 'destroySightseeing'])->name('history.sightseeing.destroy');
    Route::post('/history-items/{type}/{id}/replace', [HistoryController::class, 'replaceItem'])->name('history.item.replace');
});
