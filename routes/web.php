<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

use App\Service\AIService;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

// Route::get('dashboard', function () {
//     return Inertia::render('Dashboard');
// })->middleware(['auth', 'verified'])->name('dashboard');

// require __DIR__.'/settings.php';
// require __DIR__.'/auth.php';

//for testing AI service
Route::get('/test-ai-tour', function (\Illuminate\Http\Request $request) {
    $location = $request->query('location', 'Hải Phòng');
    $foodType = $request->query('foodType', 'everything');
    $time = $request->query('time', 'full day');
    $numberOfDays = (int) $request->query('numberOfDays', 2);

    $aiService = new AIService();
    $result = $aiService->getTour($location, $foodType, $time);

    return response()->json($result);
});
