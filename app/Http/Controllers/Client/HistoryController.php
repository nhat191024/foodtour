<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\History;
use App\Models\HistoryFood;
use App\Models\HistorySightseeing;
use App\Models\UserFavoriteFood;
use App\Models\UserFavoriteSightseeing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class HistoryController extends Controller
{
    public function detail(int $id)
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }
        if (!History::find($id)) return abort(404);
        $requestedHistoryRecord = History::with('items', 'items.sightseeing', 'items.food', 'items.sightseeing.userFavorite', 'items.food.userFavorite')->findOrFail($id);

        if ($requestedHistoryRecord->user_id !== Auth::id()) {
            abort(403);
        }

        return Inertia::render('history/Detail', [
            'data' => $requestedHistoryRecord
        ]);
    }


    public function index()
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }
        $user = Auth::user();
        if (!$user) return abort(403);
        $historyRecords = History::where('user_id', $user->id)->orderBy('created_at', 'desc')->get();
        return Inertia::render('history/Index', [
            'data' => $historyRecords
        ]);
    }

    public function toggleSightseeingFavorite(HistorySightseeing $sightseeing)
    {
        $favorite = UserFavoriteSightseeing::where('user_id', Auth::id())
            ->where('history_sightseeing_id', $sightseeing->id)
            ->first();

        if ($favorite) {
            $favorite->delete();
        } else {
            UserFavoriteSightseeing::create([
                'user_id' => Auth::id(),
                'history_sightseeing_id' => $sightseeing->id,
            ]);
        }

        return back();
    }

    public function toggleFoodFavorite(HistoryFood $food)
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $favorite = UserFavoriteFood::where('user_id', Auth::id())
            ->where('history_food_id', $food->id)
            ->first();

        if ($favorite) {
            $favorite->delete();
        } else {
            UserFavoriteFood::create([
                'user_id' => Auth::id(),
                'history_food_id' => $food->id,
            ]);
        }

        return back();
    }
}
