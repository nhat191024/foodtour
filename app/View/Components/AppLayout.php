<?php

namespace App\View\Components;

use Illuminate\Support\Facades\Auth;
use Illuminate\View\Component;
use Illuminate\View\View;

class AppLayout extends Component
{
    /**
     * Get the view / contents that represents the component.
     */
    public function render(): View
    {
        if (Auth::check()) {
            $historyList = Auth::user()->tours;
            if (!$historyList->isEmpty()) {
                // dd($historyList);
                $listNavBarItems = $historyList->map(function ($tour) {
                    return [
                        'id' => $tour->id,
                        'name' => $tour->name,
                        'created_at' => $tour->created_at->format('d/m/Y H:i:s'),
                    ];
                })->sortBy('created_at')->reverse();
                // sneaky add 1 more item into the list onto the TOP
                $listNavBarItems->prepend([
                    'name' => '⭮ Làm mới',
                    'route' => 'client.start',
                ]);
                return view('layouts.app', compact('listNavBarItems'));
            }
        }
        $listNavBarItems = [
            ['name' => 'Đăng nhập để xem', 'route' => 'login'],
            ['name' => 'Chưa có tài khoản?', 'route' => 'register'],
            ['name' => '⭮ Làm mới', 'route' => 'client.home'],
        ];

        if (auth()->check()) {
            $listNavBarItems = [
                ['name' => '⭮ Làm mới', 'route' => 'client.home'],
            ];
        }
        return view('layouts.app', compact('listNavBarItems'));
    }
}
