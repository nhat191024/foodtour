<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\History;
use App\Models\HistoryBus;
use App\Models\HistoryFood;
use App\Models\HistoryItem;
use App\Models\HistoryMotel;
use App\Models\HistorySightseeing;
use App\Models\UserFavoriteFood;
use App\Models\UserFavoriteSightseeing;
use App\Service\AIService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class HistoryController extends Controller
{
    public function detail(int $id)
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }
        if (!History::find($id)) return abort(404);
        $requestedHistoryRecord = History::with('items', 'items.sightseeing', 'items.food', 'items.sightseeing.userFavorite', 'items.food.userFavorite', 'buses', 'motels')->findOrFail($id);

        if ($requestedHistoryRecord->user_id !== Auth::id()) {
            abort(403);
        }

        return Inertia::render('history/Detail', [
            'data' => $requestedHistoryRecord,
            'history_id' => $requestedHistoryRecord->id
        ]);
    }


    public function index()
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }
        $user = Auth::user();
        if (!$user) return abort(403);
        $historyRecords = History::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->with('tripCosts')
            ->get();
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

    public function destroyFood(HistoryFood $food)
    {
        if ($food->historyItem->history->user_id !== auth()->id()) {
            abort(403);
        }
        $food->delete();
        return back()->with('success', 'Đã xóa quán ăn.');
    }

    public function destroySightseeing(HistorySightseeing $sightseeing)
    {
        if ($sightseeing->historyItem->history->user_id !== auth()->id()) {
            abort(403);
        }
        $sightseeing->delete();
        return back()->with('success', 'Đã xóa địa điểm.');
    }

    public function destroyBus($id)
    {
        // dd($id);
        $historyBus = HistoryBus::find($id);
        if ($historyBus->history->user_id !== auth()->id()) {
            abort(403);
        }
        $historyBus->delete();
        return back()->with('success', 'Đã xóa nhà xe.');
    }

    public function destroyMotel($id)
    {
        $historyMotel = HistoryMotel::find($id);
        if ($historyMotel->history->user_id !== auth()->id()) {
            abort(403);
        }
        $historyMotel->delete();
        return back()->with('success', 'Đã xóa khách sạn.');
    }

    public function addBusItem(Request $request, int $id, AIService $aiService)
    {
        // dd('bus',$request->all(), $id);
        // history item id: $id
        // user prompt: in $request
        // dd($request->all(), $id);
        $messages = [
            'prompt.required' => 'Vui lòng nhập yêu cầu thay thế.',
            'prompt.string' => 'Yêu cầu thay thế không hợp lệ.',
            'prompt.max' => 'Yêu cầu thay thế không được vượt quá 50 ký tự.',
        ];
        $request->validate(['prompt' => 'required|string|max:50'], $messages);
        $userPrompt = $request->input('prompt');
        $history = History::findOrFail($id);
        $oldItems = $history->buses;
        $context = (object)[
            'location' => $history->title,
            'interests' => $history->interests,
            'company' => $history->company,
            'current_location' => $history->current_location,
            'member_count' => $history->member_count,
        ];
        // dd($type, $userPrompt, $context, $oldItem);
        $newItemData = $aiService->getNewBusItem($userPrompt, $context, $oldItems);

        if (!$newItemData) {
            return back()->with('error', 'Không thể tìm thấy địa điểm thay thế. Vui lòng thử lại với yêu cầu khác.');
        }

        DB::transaction(function () use ($newItemData, $id) {
            $newItemData['history_id'] = $id;
            HistoryBus::create($newItemData);
        });

        return back()->with('success', 'Đã thêm nhà xe mới!');
    }

    public function addMotelItem(Request $request, int $id, AIService $aiService)
    {
        $messages = [
            'prompt.required' => 'Vui lòng nhập yêu cầu thay thế.',
            'prompt.string' => 'Yêu cầu thay thế không hợp lệ.',
            'prompt.max' => 'Yêu cầu thay thế không được vượt quá 50 ký tự.',
        ];
        $request->validate(['prompt' => 'required|string|max:50'], $messages);
        $userPrompt = $request->input('prompt');
        $history = History::findOrFail($id);
        $oldItems = $history->motels;
        $context = (object)[
            'location' => $history->title,
            'interests' => $history->interests,
            'company' => $history->company,
            'current_location' => $history->current_location,
            'member_count' => $history->member_count,
        ];
        // dd($type, $userPrompt, $context, $oldItem);
        $newItemData = $aiService->getNewMotelItem($userPrompt, $context, $oldItems);

        if (!$newItemData) {
            return back()->with('error', 'Không thể tìm thấy địa điểm thay thế. Vui lòng thử lại với yêu cầu khác.');
        }

        DB::transaction(function () use ($newItemData, $id) {
            $newItemData['history_id'] = $id;
            HistoryMotel::create($newItemData);
        });

        return back()->with('success', 'Đã thêm khách sạn mới!');
    }

    // the $id here is the HistoryItem id
    public function addItem(Request $request, int $id, AIService $aiService)
    {
        // history item id: $id
        // user prompt: in $request
        // dd($request->all(), $id);
        $messages = [
            'prompt.required' => 'Vui lòng nhập yêu cầu thay thế.',
            'prompt.string' => 'Yêu cầu thay thế không hợp lệ.',
            'prompt.max' => 'Yêu cầu thay thế không được vượt quá 50 ký tự.',
        ];
        $request->validate(['prompt' => 'required|string|max:50'], $messages);
        $userPrompt = $request->input('prompt');
        $historyItem = HistoryItem::findOrFail($id);
        $history = $historyItem->history;
        $context = (object)[
            'location' => $history->title,
            'interests' => $history->interests,
            'company' => $history->company,
            'dayTime' => $historyItem->day_time,
            'foodType' => '(decide by the newly requested item, only between food|sightseeing)',
            'member_count' => $history->member_count,
        ];
        $oldFoodItems = $historyItem->food;
        $oldSightseeingItems = $historyItem->sightseeing;
        $type = $context->foodType;
        // dd($type, $userPrompt, $context, $oldItem);
        $newItemData = $aiService->getReplacementItem($type, $userPrompt, $context, $oldFoodItems, $oldSightseeingItems);

        if (!$newItemData) {
            return back()->with('error', 'Không thể tìm thấy địa điểm thay thế. Vui lòng thử lại với yêu cầu khác.');
        }

        DB::transaction(function () use ($newItemData, $type, $id) {
            $newItemData['history_item_id'] = $id;
            if ($type === 'food') {
                HistoryFood::create($newItemData);
            } else {
                unset($newItemData['food_type']);
                HistorySightseeing::create($newItemData);
            }
        });

        return back()->with('success', 'Đã thêm địa điểm mới!');
    }

    // the $id here is the id of HistoryFood or HistorySightseeing (judge by $type)
    public function replaceItem(Request $request, string $type, int $id, AIService $aiService)
    {
        $messages = [
            'prompt.required' => 'Vui lòng nhập yêu cầu thay thế.',
            'prompt.string' => 'Yêu cầu thay thế không hợp lệ.',
            'prompt.max' => 'Yêu cầu thay thế không được vượt quá 50 ký tự.',
        ];
        $request->validate(['prompt' => 'required|string|max:50'], $messages);
        $userPrompt = $request->input('prompt');

        if ($type === 'food') {
            $oldItem = HistoryFood::findOrFail($id);
        } elseif ($type === 'sightseeing') {
            $oldItem = HistorySightseeing::findOrFail($id);
        } else {
            return back()->with('error', 'Loại địa điểm không hợp lệ.');
        }

        if ($oldItem->historyItem->history->user_id !== auth()->id()) {
            abort(403);
        }

        $historyItem = $oldItem->historyItem;
        $history = $historyItem->history;
        $context = (object)[
            'location' => $history->title,
            'interests' => $history->interests,
            'company' => $history->company,
            'dayTime' => $oldItem->historyItem->day_time,
            'foodType' => $type === 'food' ? $oldItem->food_type : null,
            'member_count' => $history->member_count,
        ];
        $oldFoodItems = $historyItem->food;
        $oldSightseeingItems = $historyItem->sightseeing;
        $historyItemId = $oldItem->history_item_id;
        $newItemData = $aiService->getReplacementItem($type, $userPrompt, $context, $oldFoodItems, $oldSightseeingItems);

        if (!$newItemData) {
            return back()->with('error', 'Không thể tìm thấy địa điểm thay thế. Vui lòng thử lại với yêu cầu khác.');
        }

        DB::transaction(function () use ($oldItem, $newItemData, $type, $historyItemId) {
            $oldItem->delete();
            $newItemData['history_item_id'] = $historyItemId;
            if ($type === 'food') {
                HistoryFood::create($newItemData);
            } else {
                unset($newItemData['food_type']);
                HistorySightseeing::create($newItemData);
            }
        });

        return back()->with('success', 'Đã cập nhật địa điểm!');
    }
}
