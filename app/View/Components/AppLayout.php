<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class AppLayout extends Component
{
    /**
     * Get the view / contents that represents the component.
     */
    public function render(): View
    {
        $listNavBarItems = [
            ['name' => 'Trang chủ', 'route' => 'client.home'],
            ['name' => 'Đặt món', 'route' => 'client.home'],
            ['name' => 'Chọn thời gian', 'route' => 'client.home'],
        ];
        return view('layouts.app', compact('listNavBarItems'));
    }
}
