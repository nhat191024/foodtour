<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Service\AIService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class ProfileController extends Controller
{

    public function index()
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }
        $user = Auth::user();
        $favoriteFoodItems = $user->userFavoriteFood()->with('historyFood.userFavorite')->get();
        $favoriteSightseeingItems = $user->userFavoriteSightseeing()->with('historySightseeing.userFavorite')->get();
        return Inertia::render('profile/Favorite', [
            'foodItems' => $favoriteFoodItems,
            'sightseeingItems' => $favoriteSightseeingItems,
        ]);
    }
}
