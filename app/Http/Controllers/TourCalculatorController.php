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

class TourCalculatorController extends Controller
{

    public function __construct()
    {

    }

    public function getAllTours()
    {
        $allTours = Tour::all();
        return response()->json(['status' => 'success', 'data' => $allTours]);
    }

    public function getTourItemsByTourId($tourId)
    {
        $allTourItems = TourItem::where('tour_id',$tourId)->get();
        return response()->json(['status' => 'success', 'data' => $allTourItems]);
    }
}
