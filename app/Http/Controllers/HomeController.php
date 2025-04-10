<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Service\AIService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class HomeController extends Controller
{
    private $aiService;
    public $foodTypes;
    public function __construct()
    {
        $this->aiService = app(AIService::class);

        $this->foodTypes = collect([
            ['name' => 'Món mặn', 'value' => 'món mặn'],
            ['name' => 'Món ngọt', 'value' => 'món ngọt'],
            ['name' => 'Món tráng miệng', 'value' => 'món tráng miệng'],
            ['name' => 'Món ăn vặt', 'value' => 'món ăn vặt'],
            ['name' => 'Đồ uống', 'value' => 'đồ uống'],
            ['name' => 'Món ăn chay', 'value' => 'món ăn chay'],
            ['name' => 'Món ăn hải sản', 'value' => 'món ăn hải sản'],
            ['name' => 'Món ăn nhanh', 'value' => 'món ăn nhanh'],
            // ['name' => 'Món ăn truyền thống', 'value' => 'món ăn truyền thống'],
        ]);
    }

    public function index()
    {
        $foodTypes = $this->foodTypes;
        return view('home.index', compact('foodTypes'));
    }

    public function getTourById(Request $request)
    {
        $id = $request->input('id');
        if (empty($id)) {
            return response()->json(['status' => 'error', 'message' => 'Vui lòng chọn tour để xem chi tiết.']);
        }

        if (!auth()->check()) {
            return response()->json(['status' => 'error', 'message' => 'Vui lòng đăng nhập để sử dụng tính năng này.']);
        }
        $tourList = $this->aiService->getTourById($id);
        return response()->json(['status' => 'success', 'data' => $tourList]);
    }

    public function tourSubmit(Request $request)
    {
        if (!auth()->check()) {
            return response()->json(['status' => 'error', 'message' => 'Vui lòng đăng nhập để sử dụng tính năng này.']);
        }
        $validator = Validator::make($request->all(), [
            'location' => 'required|string|max:255',
            'days' => 'required|integer|min:1',
            'food_types' => 'nullable|array',
            'food_types.*' => 'string',
            'time_preference' => 'required|array',
            'time_preference.*' => 'string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $validated = $validator->validated();

        $result = $this->aiService->getTour(
            $validated['location'],
            implode(', ', $validated['food_types']),
            implode(', ', $validated['time_preference']),
            $validated['days']
        );

        return response()->json(['status' => 'success', 'data' => $result]);
    }
}
