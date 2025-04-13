<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\FavoriteTourItem;
use App\Models\Tour;
use App\Models\TourItem;
use App\Service\AIService;
use App\Service\OpenWeatherService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

use function Pest\Laravel\json;

class HomeController extends Controller
{
    private $aiService;
    public $weatherService;
    public $foodTypes;
    public function __construct()
    {
        $this->aiService = app(AIService::class);
        $this->weatherService = app(OpenWeatherService::class);

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

    public function favorite()
    {
        if (!auth()->check()) {
            return response()->json(['status' => 'error', 'message' => 'Vui lòng đăng nhập để sử dụng tính năng này.']);
        }

        $tourItems = auth()->user()->favoriteItems()
            ->with('tour')
            ->whereHas('tour', function ($query) {
                $query->where('status', 1);
            })
            ->get();

        $formattedResponse = [
            'Yêu thích' => [
                'Danh sách yêu thích gần đây' => $tourItems->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'tour_id' => $item->tour_id,
                        'day' => $item->day,
                        'name' => $item->name,
                        'address' => $item->address,
                        'latitude' => (float)$item->latitude,
                        'longitude' => (float)$item->longitude,
                        'description' => $item->description,
                        'suggested_time' => $item->suggested_time,
                        'food_type' => $item->food_type ?? null,
                        'notes' => $item->notes ?? null
                    ];
                })->toArray()
            ]
        ];

        $tourList = $formattedResponse;
        return response()->json(['status' => 'success', 'data' => $tourList, 'is_favorite' => true]);
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

    public function disableTourItem(Request $request)
    {
        if (!auth()->check()) {
            return response()->json(['status' => 'error', 'message' => 'Vui lòng đăng nhập để sử dụng tính năng này.']);
        }
        $tourItemId = $request->input('tour_item_id');
        if (empty($tourItemId)) {
            return response()->json(['status' => 'error', 'message' => 'Vui lòng chọn tour để xem chi tiết.']);
        }
        try {
            TourItem::where('id', $tourItemId)->update(['status' => 0]);
        } catch (\Throwable $th) {
            return response()->json(['status' => 'error', 'message' => 'Có lỗi xảy ra khi xóa tour item.']);
        }
        return response()->json(['status' => 'success', 'message' => 'Đã loại bỏ địa điểm.']);
    }

    public function getNewTourItem(Request $request)
    {
        if (!auth()->check()) {
            return response()->json(['status' => 'error', 'message' => 'Vui lòng đăng nhập để sử dụng tính năng này.']);
        }

        $tourItemId = $request->input('tour_item_id');
        if (empty($tourItemId)) {
            return response()->json(['status' => 'error', 'message' => 'Vui lòng thử lại sau.']);
        }

        try {
            $tourItem = TourItem::find($tourItemId);
            if (!$tourItem) {
                return response()->json(['status' => 'error', 'message' => 'Tour đó không tồn tại.']);
            }
            $newTourItem = $this->aiService->getNewTourItem($tourItem);
        } catch (\Throwable $th) {
            return response()->json(['status' => 'error', 'message' => 'Có lỗi xảy ra khi lấy tour item mới: ' . $th->getMessage()]);
        }
        return response()->json(['status' => 'success', 'message' => 'Đã thêm địa điểm mới', 'data' => $newTourItem]);
    }

    public function searchLocations(Request $request)
    {
        $query = $request->input('query');
        $locations = $this->weatherService->getAvailableLocations($query);
        return response()->json(['status' => 'success', 'data' => $locations]);
    }

    public function getCurrentWeather(Request $request)
    {
        // admin: null
        // ​
        // end_date: "2025-04-13"
        // ​
        // location: "Hải Phòng, Vietnam"
        // ​
        // start_date: "2025-04-12"

        $location = $request->input('admin') . ' ' . $request->input('location');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        // return $location;
        // return request all
        // return $request->all();
        if (empty($startDate) || empty($endDate)) {
            $currentWeather = $this->weatherService->getWeatherInVietnam($location);
        } else {
            $currentWeather = $this->weatherService->getWeatherInVietnam($location, $startDate, $endDate);
        }

        return response()->json(['status' => 'success', 'data' => $currentWeather]);
    }

    public function favoriteTourItem(Request $request)
    {
        $id = $request->input('tour_item_id'); 
        $isFavorite = $request->input('is_favorite'); 

        // return 'got id: '.$id . '; Got boolean: '. $isFavorite;

        if (!auth()->check()) {
            return response()->json(['status' => 'error', 'message' => 'Vui lòng đăng nhập để sử dụng tính năng này.']);
        }

        if (empty($id) || empty($isFavorite)) {
            return response()->json(['status' => 'error', 'message' => 'Vui lòng thử lại sau.']);
        }

        $currentFavoriteItem = FavoriteTourItem::where('user_id', auth()->user()->id)
            ->where('tour_item_id', $id)
            ->first();

        if ($isFavorite === 'true') {
            if ($currentFavoriteItem) {
                return response()->json(
                    [
                        'status' => 'success',
                        'message' => 'Địa điểm này đã được yêu thích rồi'
                    ]
                );
            }
            FavoriteTourItem::create(
                [
                    'user_id' => auth()->user()->id,
                    'tour_item_id' => $id,
                ]
            );
        } else {
            if ($currentFavoriteItem) {
                $currentFavoriteItem->delete();
                return response()->json(
                    [
                        'status' => 'success',
                        'message' => 'Đã bỏ yêu thích địa điểm này'
                    ]
                );
            }

            return response()->json(
                [
                    'status' => 'success',
                    'message' => 'Địa điểm đã không còn là yêu thích'
                ]
            );
            
        }

        return response()->json(
            [
                'status' => 'success',
                'message' => 'Đã thêm địa điểm vào danh sách yêu thích'
            ]
        );
    }

    public function renameTour(Request $request)
    {
        $tourId = $request->input('tour_id');
        $newName = $request->input('new_name');

        if (empty($tourId) || empty($newName)) {
            return response()->json(['status' => 'error', 'message' => 'Vui lòng thử lại sau.']);
        }

        try {
            Tour::where('id', $tourId)->update(['name' => $newName]);
        } catch (\Throwable $th) {
            return response()->json(['status' => 'error', 'message' => 'Có lỗi xảy ra khi đổi tên tour item.']);
        }
        return response()->json(['status' => 'success', 'message' => 'Đã đổi tên tour item.']);
    }
}
