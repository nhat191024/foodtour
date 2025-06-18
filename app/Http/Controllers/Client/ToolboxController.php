<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\History;
use App\Models\TripHistoryCost;
use App\Service\OpenWeatherService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ToolboxController extends Controller
{
    public function calculator(int $id = 0)
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }
        $history = History::where('user_id', auth()->id())->with('tripCosts')->find($id);
        if (!$history) return redirect()->route('history.index');
        return Inertia::render('toolbox/TripCalculator',[
            'historyItem' => $history
        ]);
    }

    public function storeTripCost(Request $request, History $history)
    {
        if ($history->user_id !== auth()->id()) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'value' => 'required|numeric|min:0',
        ]);

        $history->tripCosts()->create($validated);

        return back()->with('success', 'Đã thêm chi phí thành công!');
    }

    public function destroyTripCost(TripHistoryCost $cost)
    {

        if ($cost->history->user_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

        $cost->delete();

        return back()->with('success', 'Đã xóa chi phí.');
    }

    public function weather()
    {
        return Inertia::render('toolbox/WeatherCast', [
            'data' => [],
            'locations' => [],
        ]);
    }

    public function searchLocations(Request $request)
    {
        $location = $request->get('q');

        $openWeatherService = new OpenWeatherService();

        $locations = $openWeatherService->getAvailableLocations($location);


        return Inertia::render('toolbox/WeatherCast', [
            'locations' => $locations,
            'data' => $request->session()->get('weatherData', [])
        ]);
    }

    public function getWeather(Request $request)
    {
        if (!$request->filled(['location', 'start_date', 'end_date'])) {
            return back()->with('error', 'Vui lòng điền đầy đủ thông tin.');
        }

        $location = $request->input('location');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $openWeatherService = new OpenWeatherService();
        $weatherData = $openWeatherService->getWeatherInVietnam($location, $startDate, $endDate);


        return Inertia::render('toolbox/WeatherCast', [
            'data' => $weatherData,
            'locations' => [],
            'summary_location' => $location
        ]);
    }
}
