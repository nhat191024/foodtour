<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\CalculateHistory;
use App\Models\FavoriteTourItem;
use App\Models\Food;
use App\Models\Member;
use App\Models\Tour;
use App\Models\TourItem;
use App\Service\AIService;
use App\Service\OpenWeatherService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

use function Pest\Laravel\json;

class TourCalculatorController extends Controller
{
    public function getAllTours()
    {
        $allTours = Tour::where('user_id', auth()->id())->get();
        return response()->json(['status' => 'success', 'data' => $allTours]);
    }

    public function getTourItemsByTourId($tourId)
    {
        $tour = Tour::where('id', $tourId)
            ->where('user_id', auth()->id())
            ->firstOrFail();
        $allTourItems = TourItem::where('tour_id', $tourId)->get();
        return response()->json(['status' => 'success', 'data' => $allTourItems]);
    }

    public function saveCalculation(Request $request)
    {
        $record = CalculateHistory::create([
            'trip_name' => $request->input('tripName') ?? $request->input('tourName'),
            'total_per_person' => $request->input('perPerson'),
            'total' => $request->input('total'),
            'user_id' => auth()->id(),
            'tour_item_id' => $request->input('tourItemId'),
        ]);

        $foodItems = $request->input('foodItems');
        $memberItems = $request->input('members');

        foreach ($foodItems as $item) {
            Food::create([
                'name' => $item['name'],
                'price' => $item['price'],
                'calculate_history_id' => $record->id,
            ]);
        }

        foreach ($memberItems as $item) {
            Member::create([
                'name' => $item['name'],
                'calculate_history_id' => $record->id,
            ]);
        }

        return response()->json(['status' => 'success', 'data' => 'ok']);
    }

    public function getCalculation()
    {
        $results = CalculateHistory::with('members', 'foods', 'tourItem')->where('user_id', auth()->id())->get();
        return response()->json(['status' => 'success', 'data' => $results]);
    }

    public function deleteCalculation($id)
    {
        CalculateHistory::find($id)->delete();
        return response()->json(['status' => 'success']);
    }
}
