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
    $location = $request->query('location', 'Hà Nội');
    $foodType = $request->query('foodType', 'everything');
    $time = $request->query('time', 'full day');
    $numberOfDays = (int) $request->query('numberOfDays', 2);
    $weather = $request->query('weather', [
        'day 1' => [
            'temp' => 30,
            'weather' => 'sunny',
        ],
        'day 2' => [
            'temp' => 28,
            'weather' => 'rainy',
        ]
    ]);

    $aiService = new AIService();
    $result = $aiService->getTour($location, $foodType, $time, $numberOfDays, (array)$weather);

    return response()->json($result);
});
