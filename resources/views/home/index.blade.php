<x-app-layout>
    <div class="container mx-auto px-4 py-8 max-w-md">
        <form id="foodTourForm" action="{{ route('tour.submit') }}" method="POST"
            class="bg-base-100 rounded-xl shadow-lg overflow-hidden">
            @csrf
            <input type="hidden" name="tour_id" id="tour_id" value="0">
            <!-- Tab Container -->
            <div class="tab-container">
                <!-- Tab 1: Location -->
                <div class="tab-panel" id="tab1">
                    <div class="bg-base-200 px-6 py-4">
                        <div class="flex justify-between items-center">
                            <button type="button" class="back-button invisible btn btn-circle btn-ghost btn-sm">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round">
                                    <path d="m15 18-6-6 6-6" />
                                </svg>
                            </button>
                            <div class="flex items-center gap-1">
                                <span class="badge badge-primary">1</span>
                                <span class="text-sm text-base-content/70">of 3</span>
                            </div>
                            <button type="button" class="next-tab forward-button btn btn-circle btn-ghost btn-sm"
                                data-target="tab2">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round">
                                    <path d="m9 18 6-6-6-6" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div class="p-6">
                        <div class="flex justify-center mb-8">
                            <div class="text-center">
                                <div class="flex justify-center items-center gap-3 mb-3 bg-base-200 p-3 rounded-full">
                                    <img src="{{ asset('images/dish.svg') }}" alt="Dish" class="w-6 h-6">
                                    <img src="{{ asset('images/drink.svg') }}" alt="Drink" class="w-6 h-6">
                                    <img src="{{ asset('images/brain.svg') }}" alt="Brain" class="w-6 h-6">
                                    <img src="{{ asset('images/bell.svg') }}" alt="Bell" class="w-6 h-6">
                                    <img src="{{ asset('images/location.svg') }}" alt="Location" class="w-6 h-6">
                                </div>
                                <h2 class="text-2xl font-bold text-primary">FOOD TOUR FINDING</h2>
                            </div>
                        </div>

                        <div class="space-y-6">
                            <div class="form-control">
                                <label for="location" class="label">
                                    <span class="label-text text-base font-medium">Điền địa điểm</span>
                                </label>
                                <input type="text" id="location" name="location"
                                    class="input input-bordered w-full focus:input-primary" placeholder="Nhập địa điểm">
                            </div>

                            <div class="form-control">
                                <label for="days" class="label">
                                    <span class="label-text text-base font-medium">Số ngày đi</span>
                                </label>
                                <input type="text" id="days" name="days"
                                    class="input input-bordered w-full focus:input-primary" placeholder="Nhập số ngày">
                            </div>

                            <button type="button" class="next-tab btn btn-primary w-full mt-4" data-target="tab2">
                                Tiếp theo
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Tab 2: Food Selection -->
                <div class="tab-panel hidden" id="tab2">
                    <div class="bg-base-200 px-6 py-4">
                        <div class="flex justify-between items-center">
                            <button type="button" id="first-tab-btn"
                                class="back-button btn btn-circle btn-ghost btn-sm" data-target="tab1">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round">
                                    <path d="m15 18-6-6 6-6" />
                                </svg>
                            </button>
                            <div class="flex items-center gap-1">
                                <span class="badge badge-primary">2</span>
                                <span class="text-sm text-base-content/70">of 3</span>
                            </div>
                            <button type="button" class="next-tab forward-button btn btn-circle btn-ghost btn-sm"
                                data-target="tab3">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round">
                                    <path d="m9 18 6-6-6-6" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div class="p-6">
                        <div class="text-center mb-6">
                            <h2 class="text-2xl font-bold text-primary">Chọn món</h2>
                            <p class="text-base-content/70 mt-1">Chọn các loại món ăn bạn muốn thưởng thức</p>
                        </div>

                        <div class="grid grid-cols-2 gap-4 mb-6">
                            <!-- Food Options -->
                            @foreach ($foodTypes as $foodType)
                                <label class="food-option">
                                    <input type="checkbox" name="food_types[]" value="{{ $foodType['value'] }}"
                                        class="hidden">
                                    <div
                                        class="option-box h-full flex items-center justify-center bg-base-200 hover:bg-base-300 p-4 rounded-lg text-center cursor-pointer transition-all border border-base-300 hover:border-primary">
                                        <span class="font-medium">{{ $foodType['name'] }}</span>
                                    </div>
                                </label>
                            @endforeach

                            {{-- <div class="bg-transparent p-4 rounded-lg opacity-0">
                                <!-- Empty space to maintain grid -->
                            </div> --}}
                        </div>

                        <button type="button" class="next-tab btn btn-primary w-full" data-target="tab3">
                            Tiếp theo
                        </button>
                    </div>
                </div>

                <!-- Tab 3: Time Selection -->
                <div class="tab-panel hidden" id="tab3">
                    <div class="bg-base-200 px-6 py-4">
                        <div class="flex justify-between items-center">
                            <button type="button" class="back-button btn btn-circle btn-ghost btn-sm"
                                data-target="tab2">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round">
                                    <path d="m15 18-6-6 6-6" />
                                </svg>
                            </button>
                            <div class="flex items-center gap-1">
                                <span class="badge badge-primary">3</span>
                                <span class="text-sm text-base-content/70">of 3</span>
                            </div>
                            <button type="button"
                                class="next-tab invisible forward-button btn btn-circle btn-ghost btn-sm"
                                data-target="tab4" id="final-tab-btn">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round">
                                    <path d="m9 18 6-6-6-6" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div class="p-6">
                        <div class="text-center mb-6">
                            <h2 class="text-2xl font-bold text-primary">Chọn thời gian đi</h2>
                            <p class="text-base-content/70 mt-1">Chọn thời gian bạn muốn đi ăn</p>
                        </div>

                        <div class="space-y-3 mb-6">
                            <label class="time-option block">
                                <input type="checkbox" name="time_preference[]" value="sáng" class="hidden">
                                <div
                                    class="bg-base-200 hover:bg-base-300 py-3 px-4 rounded-lg flex items-center cursor-pointer transition-all border border-base-300 hover:border-primary">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                        stroke-linecap="round" stroke-linejoin="round" class="mr-3 text-warning">
                                        <circle cx="12" cy="12" r="4" />
                                        <path d="M12 2v2" />
                                        <path d="M12 20v2" />
                                        <path d="m4.93 4.93 1.41 1.41" />
                                        <path d="m17.66 17.66 1.41 1.41" />
                                        <path d="M2 12h2" />
                                        <path d="M20 12h2" />
                                        <path d="m6.34 17.66-1.41 1.41" />
                                        <path d="m19.07 4.93-1.41 1.41" />
                                    </svg>
                                    <span class="font-medium">Sáng</span>
                                </div>
                            </label>
                            <label class="time-option block">
                                <input type="checkbox" name="time_preference[]" value="trưa" class="hidden">
                                <div
                                    class="bg-base-200 hover:bg-base-300 py-3 px-4 rounded-lg flex items-center cursor-pointer transition-all border border-base-300 hover:border-primary">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                        stroke-linecap="round" stroke-linejoin="round" class="mr-3 text-error">
                                        <circle cx="12" cy="12" r="5" />
                                        <path d="M12 1v2" />
                                        <path d="M12 21v2" />
                                        <path d="M4.2 4.2l1.4 1.4" />
                                        <path d="M18.4 18.4l1.4 1.4" />
                                        <path d="M1 12h2" />
                                        <path d="M21 12h2" />
                                        <path d="M4.2 19.8l1.4-1.4" />
                                        <path d="M18.4 5.6l1.4-1.4" />
                                    </svg>
                                    <span class="font-medium">Trưa</span>
                                </div>
                            </label>
                            <label class="time-option block">
                                <input type="checkbox" name="time_preference[]" value="chiều" class="hidden">
                                <div
                                    class="bg-base-200 hover:bg-base-300 py-3 px-4 rounded-lg flex items-center cursor-pointer transition-all border border-base-300 hover:border-primary">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                        stroke-linecap="round" stroke-linejoin="round" class="mr-3 text-secondary">
                                        <path d="M12 12a4 4 0 0 0 0-8 4.5 4.5 0 0 0-1.8 9 7 7 0 0 0-2.2 5" />
                                        <path d="M8 20h8" />
                                        <path d="M12 12v8" />
                                    </svg>
                                    <span class="font-medium">Chiều</span>
                                </div>
                            </label>
                            <label class="time-option block">
                                <input type="checkbox" name="time_preference[]" value="tối" class="hidden">
                                <div
                                    class="bg-base-200 hover:bg-base-300 py-3 px-4 rounded-lg flex items-center cursor-pointer transition-all border border-base-300 hover:border-primary">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                        stroke-linecap="round" stroke-linejoin="round" class="mr-3 text-info">
                                        <path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z" />
                                    </svg>
                                    <span class="font-medium">Tối</span>
                                </div>
                            </label>
                            <label class="time-option block">
                                <input type="checkbox" name="time_preference[]" value="cả-ngày" class="hidden">
                                <div
                                    class="bg-base-200 hover:bg-base-300 py-3 px-4 rounded-lg flex items-center cursor-pointer transition-all border border-base-300 hover:border-primary">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                        stroke-linecap="round" stroke-linejoin="round" class="mr-3 text-accent">
                                        <circle cx="12" cy="12" r="10" />
                                        <path d="m8 12 3 3 5-5" />
                                    </svg>
                                    <span class="font-medium">Cả ngày</span>
                                </div>
                            </label>
                        </div>

                        <button type="submit" class="btn btn-primary w-full">
                            Hoàn thành
                        </button>
                    </div>
                </div>

                <!-- Tab 4: Calculator Selection -->
                <div class="tab-panel hidden" id="calculator-tab">
                    <div class="p-6">
                        <div class="text-center mb-6">
                            <h2 class="text-2xl font-bold text-primary">Tính chi tiêu</h2>
                            <p class="text-base-content/70 mt-1">Ghi tên các món, thành viên để tính~</p>
                        </div>

                        <!-- Tour Selection -->
                        <div class="form-control mb-4">
                            <label class="label">
                                <span class="label-text font-medium">Chọn Tour</span>
                            </label>
                            <select id="tour-select" class="select select-bordered w-full">
                                <option value="" disabled selected>Chọn tour của bạn</option>
                                <!-- Tour options will be populated by JavaScript -->
                            </select>
                        </div>

                        <!-- Tour Item Selection -->
                        <div class="form-control mb-4">
                            <label class="label">
                                <span class="label-text font-medium">Chọn quán</span>
                            </label>
                            <select id="tour-item-select" class="select select-bordered w-full">
                                <option value="" disabled selected>Chọn quán của tour đó</option>
                                <!-- Tour options will be populated by JavaScript -->
                            </select>
                        </div>

                        <!-- Trip Name Input -->
                        <div class="form-control mb-6">
                            <label class="label">
                                <span class="label-text font-medium">Tên chuyến đi</span>
                                <span class="label-text-alt text-base-content/60">(Để trống sẽ dùng tên tour)</span>
                            </label>
                            <input type="text" id="trip-name" placeholder="Nhập tên chuyến đi"
                                class="input input-bordered w-full" />
                        </div>

                        <!-- Food Items Section -->
                        <div class="mb-6">
                            <div class="flex justify-between items-center mb-3">
                                <h3 class="font-semibold text-lg">Danh sách món ăn</h3>
                                <button type="button" id="add-food-item" class="btn btn-sm btn-outline">
                                    + Thêm món
                                </button>
                            </div>

                            <div id="food-items-container" class="space-y-3">
                                <div class="food-item grid grid-cols-12 gap-2">
                                    <div class="col-span-8">
                                        <input type="text" placeholder="Tên món"
                                            class="input input-bordered w-full food-name" />
                                    </div>
                                    <div class="col-span-3">
                                        <input type="number" placeholder="Giá"
                                            class="input input-bordered w-full food-price" />
                                    </div>
                                    <div class="col-span-1 flex items-center justify-center">
                                        <button type="button"
                                            class="btn btn-square btn-sm btn-error remove-food-item" disabled>
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Members Section -->
                        <div class="mb-6">
                            <div class="flex justify-between items-center mb-3">
                                <h3 class="font-semibold text-lg">Thành viên tham gia</h3>
                                <button type="button" id="add-member" class="btn btn-sm btn-outline">
                                    + Thêm thành viên
                                </button>
                            </div>

                            <div id="members-container" class="space-y-3">
                                <div class="member-item grid grid-cols-12 gap-2">
                                    <div class="col-span-11">
                                        <input type="text" placeholder="Tên thành viên"
                                            class="input input-bordered w-full member-name" />
                                    </div>
                                    <div class="col-span-1 flex items-center justify-center">
                                        <button type="button" class="btn btn-square btn-sm btn-error remove-member"
                                            disabled>
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Calculate Button -->
                        <button type="button" id="calculate-btn" class="btn btn-primary w-full mb-6">
                            Tính toán
                        </button>

                        <!-- Calculation History -->
                        <div class="mb-6">
                            <div class="flex justify-between items-center mb-3">
                                <h3 class="font-semibold text-lg">Lịch sử tính toán</h3>
                                <button type="button" id="view-history" class="btn btn-sm btn-outline">
                                    Xem lịch sử
                                </button>
                            </div>

                            <div id="calculation-history" class="space-y-3 hidden">
                                <!-- History items will be populated by JavaScript -->
                            </div>
                        </div>

                        <!-- Results Section (initially hidden) -->
                        <div id="results-section" class="mt-6 border border-base-300 rounded-lg p-4 hidden">
                            <h3 class="font-semibold text-lg mb-3">Kết quả tính toán</h3>
                            <div id="calculation-results" class="space-y-2">
                                <!-- Results will be populated by JavaScript -->
                            </div>
                        </div>
                    </div>
                </div>



                <div class="tab-panel hidden justify-center" id="tab4" style="text-align-last: center;">
                    {{-- ! do not put any code here, this is handled by JS in detail-tab.js, function pushDataToDetail(data) --}}
                </div>
            </div>
        </form>
    </div>
    {{-- Weather section - independent from tab system --}}
    <div id="weather-section" class="container mx-auto px-4 py-0 max-w-md pb-8">
        <div class="bg-base-100 rounded-xl shadow-lg overflow-hidden">
            <div class="weather-container p-4">
                <div class="flex flex-col gap-4">
                    <h2 class="text-xl font-bold text-center">Xem dự báo thời tiết</h2>

                    <div class="relative">
                        <input type="text" id="location-search" class="input input-bordered w-full"
                            placeholder="Xem ở địa điểm...">
                    </div>

                    <div id="search-results" class="hidden max-h-40 overflow-y-auto bg-base-200 rounded-lg"></div>

                    <div id="selected-location" class="hidden">
                        <div class="badge badge-lg">
                            <span id="location-name"></span>
                            <button id="clear-location" class="ml-2">×</button>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="label">
                                <span class="label-text">Từ ngày</span>
                            </label>
                            <input type="date" id="start-date" class="input input-bordered w-full">
                        </div>
                        <div>
                            <label class="label">
                                <span class="label-text">Đến ngày</span>
                            </label>
                            <input type="date" id="end-date" class="input input-bordered w-full">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-2 pb-3">
                        {{-- <button id="get-current-btn" class="btn btn-primary">Chỉ hôm nay</button> --}}
                        <button id="get-weather-btn" class="btn btn-info">Xem dự báo</button>
                    </div>

                    <div id="weather-info" class="hidden">
                        <div class="flex justify-between items-center mb-4">
                            <div>
                                <h3 id="weather-location" class="text-lg font-semibold"></h3>
                                <p id="weather-date" class="text-sm text-base-content/70"></p>
                            </div>
                            <div class="text-right">
                                <p id="current-temp" class="text-3xl font-bold"></p>
                            </div>
                        </div>

                        <div class="divider my-2"></div>

                        <!-- Hourly forecast section (for current weather) -->
                        <div id="hourly-forecast-section" class="mb-4 hidden">
                            <h4 class="font-medium mb-2">Today's Forecast</h4>
                            <div id="hourly-forecast" class="grid grid-cols-4 gap-2 overflow-x-auto pb-2"></div>

                            <div class="mt-4">
                                <h4 class="font-medium mb-2">Wind Speed</h4>
                                <div id="wind-info" class="flex items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                    </svg>
                                    <span id="current-wind" class="text-lg"></span>
                                </div>
                            </div>
                        </div>

                        <!-- Daily forecast section (for date range) -->
                        <div id="daily-forecast-section" class="mb-4 hidden">
                            <h4 class="font-medium mb-2">Daily Forecast</h4>
                            <div id="daily-forecast" class="space-y-2"></div>
                        </div>
                    </div>

                    <div id="weather-loading" class="hidden text-center py-4">
                        <span class="loading loading-spinner loading-md"></span>
                        <p>Loading weather data...</p>
                    </div>

                    <div id="weather-error" class="hidden alert alert-error">
                        <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6"
                            fill="none" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>Error loading weather data. Please try again.</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

<x-actions.loading-fullscreen></x-actions.loading-fullscreen>

<x-actions.modal id="deleteConfirmModal">
    <h3 class="font-bold text-lg mb-4">Xác nhận xóa</h3>
    <p class="mb-6">Bạn có chắc chắn muốn xóa mục này không?</p>
    <div class="modal-action">
        <form method="dialog">
            <button class="btn btn-error" onclick="handleDelete()">Xóa</button>
            <button class="btn">Hủy</button>
        </form>
    </div>
</x-actions.modal>

<x-actions.modal id="favoriteConfirmModal">
    <h3 class="font-bold text-lg mb-4">Đánh dấu là yêu thích?</h3>
    <p class="mb-6">Mục yêu thích sẽ hiển thị trong <a onclick="goToFavorite()" class="link">trang yêu
            thích</a></p>
    <div class="modal-action">
        <form method="dialog">
            <button class="btn btn-error" onclick="handleConfirmFavoriteModal(true)">Đồng ý</button>
            <button class="btn">Hủy</button>
        </form>
    </div>
</x-actions.modal>

<x-actions.modal id="unfavoriteConfirmModal">
    <h3 class="font-bold text-lg mb-4">Bỏ đánh dấu yêu thích?</h3>
    <p class="mb-6">Hành động này sẽ loại bỏ mục yêu thích</a></p>
    <div class="modal-action">
        <form method="dialog">
            <button class="btn btn-error" onclick="handleConfirmFavoriteModal(false)">Đồng ý</button>
            <button class="btn">Hủy</button>
        </form>
    </div>
</x-actions.modal>

<x-actions.modal id="editTourModal">
    <h3 class="font-bold text-lg mb-4">Sửa tên lịch trình</h3>
    <p class="mb-6">Nhập tên mới cho lịch trình</a></p>
    <input type="hidden" id="edit-tour-id" value="0" />
    <input type="text" id="edit-tour-name" placeholder="Tên lịch trình mới"
        class="input input-bordered w-full mb-4" />
    <div class="modal-action">
        <button type="button" class="btn btn-error" onclick="handleConfirmEditTourModal()">Đồng ý</button>
        <form method="dialog">
            <button class="btn">Hủy</button>
        </form>
    </div>
</x-actions.modal>

<link rel="stylesheet" href="{{ asset('css/home.css') }}">
<script>
    window.baseAppUrl = "{{ env('APP_URL', 'https://food-tour.taiyo.space') }}";
</script>
<script src="{{ asset('js/money-calculator.js') }}?v={{ time() }}"></script>
<script src="{{ asset('js/home.js') }}?v={{ time() }}"></script>
<script src="{{ asset('js/detail-tab.js') }}?v={{ time() }}"></script>
<script src="{{ asset('js/weather-widget.js') }}?v={{ time() }}"></script>
<script src="{{ asset('js/screenshot.js') }}?v={{ time() }}"></script>

@include('home.home-scripts')
