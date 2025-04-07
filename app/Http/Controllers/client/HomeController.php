<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class HomeController extends Controller
{
    /**
     * Display the home page view.
     */
    public function index(): View
    {
        return view('client.home');
    }

    public function categories($slug)
    {
        $categories = [
            ['name' => 'Món Mặn', 'slug' => 'man'],
            ['name' => 'Món ngọt', 'slug' => 'ngot'],
            ['name' => 'Món tráng miệng', 'slug' => 'trang-mieng'],
            ['name' => 'Món ăn vặt', 'slug' => 'an-vat'],
            ['name' => 'Món ăn vặt', 'slug' => 'an-vat-2'],
            ['name' => 'Xem thêm', 'slug' => 'more'],
        ];

        return view('client.home.select-food-type', compact('categories'));
    }

    public function category($slug)
    {
        // Logic to fetch foods by category
        return view('foods.category', ['slug' => $slug]);
    }

    public function more()
    {
        // Logic to show more categories
        return view('foods.more');
    }
}

