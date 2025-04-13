<x-app-layout>
    <div class="container mx-auto px-4 py-8 max-w-md">
        <form id="foodTourForm" action="{{ route('tour.submit') }}" method="POST"
            class="bg-base-100 rounded-xl shadow-lg overflow-hidden">
            @csrf
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
                    <h2 class="text-xl font-bold text-center">Weather Forecast</h2>

                    <div class="relative">
                        <input type="text" id="location-search" class="input input-bordered w-full"
                            placeholder="Search location...">
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
                                <span class="label-text">Start Date</span>
                            </label>
                            <input type="date" id="start-date" class="input input-bordered w-full">
                        </div>
                        <div>
                            <label class="label">
                                <span class="label-text">End Date</span>
                            </label>
                            <input type="date" id="end-date" class="input input-bordered w-full">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-2 pb-3">
                        <button id="get-current-btn" class="btn btn-primary">Chỉ hôm nay</button>
                        <button id="get-weather-btn" class="btn btn-secondary">Khoảng ngày trên</button>
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
        <button type="button" class="btn btn-error" onclick="handleDelete()">Xóa</button>
        <form method="dialog">
            <button class="btn">Hủy</button>
        </form>
    </div>
</x-actions.modal>

<x-actions.modal id="favoriteConfirmModal">
    <h3 class="font-bold text-lg mb-4">Đánh dấu là yêu thích?</h3>
    <p class="mb-6">Mục yêu thích sẽ hiển thị trong <a href="{{ route('tour.favorite') }}" class="link">trang yêu
            thích</a></p>
    <div class="modal-action">
        <button type="button" class="btn btn-error" onclick="handleConfirmFavoriteModal(true)">Đồng ý</button>
        <form method="dialog">
            <button class="btn">Hủy</button>
        </form>
    </div>
</x-actions.modal>

<x-actions.modal id="unfavoriteConfirmModal">
    <h3 class="font-bold text-lg mb-4">Bỏ đánh dấu yêu thích?</h3>
    <p class="mb-6">Hành động này sẽ loại bỏ mục yêu thích</a></p>
    <div class="modal-action">
        <button type="button" class="btn btn-error" onclick="handleConfirmFavoriteModal(false)">Đồng ý</button>
        <form method="dialog">
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

<script src="{{ asset('js/home.js') }}"></script>
<script src="{{ asset('js/detail-tab.js') }}"></script>

<script>
    function handleConfirmEditTourModal() {
        $selectedTourId = $('#edit-tour-id').attr('value');
        $selectedTourName = $('#edit-tour-name').val().trim();
        // console.log('selected to edit tour_item ID: ' + $selectedTourId);
        // console.log('selected to edit tour_item name: ' + $selectedTourName);

        if ($selectedTourId == undefined || $selectedTourName == undefined) {
            return;
        }

        let route = "{{ route('api.tour-item.rename') }}";

        $.ajax({
            url: route,
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: {
                tour_id: $selectedTourId,
                new_name: $selectedTourName
            },
            success: function(response) {
                window.setLoading(false);
                // console.log('rename route response:' + response);
                if (response.status === 'success') {
                    // Update the tour name in the sidebar
                    $('#tour-name-' + $selectedTourId).text($selectedTourName);
                    document.getElementById('editTourModal').close();
                    // console.log('rename route response:' + JSON.stringify(response));
                    showToast(response.message, 'success');


                } else {
                    showToast(response.message, 'error');
                }
            },
            beforeSend: function() {
                window.setLoading(true, 'Đợi một lát');
            },
            error: function(xhr) {
                window.setLoading(false);
                // console.log('favorite rouite error:' + xhr);
                showToast('Vui lòng thử lại sau', 'error');
            }
        });
    }
    // Confirm favorite for tour item details page
    function handleConfirmFavoriteModal(isFavorite) {
        closeConfirmUnfavoriteModal();
        closeConfirmFavoriteModal();
        // console.log('selected to delete tour_item ID: ' + $selectedTourItemId);
        let route = "{{ route('api.tour-item.favorite') }}";
        $.ajax({
            url: route,
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: {
                tour_item_id: $selectedTourItemId,
                is_favorite: isFavorite
            },
            success: function(response) {
                // console.log('favorite rouite response:' + response);
                // console.log('favorite rouite status:' + response.status);
                // console.log('favorite rouite message:' + response.message);
                if (response.status === 'success') {
                    showToast(response.message, 'success');
                } else {
                    showToast(response.message, 'error');
                }
                if (!isFavorite) {
                    toggleTourItemVisibility($selectedTourItemId, false);
                }
            },
            error: function(xhr) {
                // console.log('favorite rouite error:' + xhr);
                showToast('Error toggling favorite status', 'error');
            }
        });
    }
    // Confirm delete for tour item details page
    function handleDelete() {
        // toggleAddTourItemButton($selectedTourItemId, true);
        toggleTourItemVisibility($selectedTourItemId, false);
        closeConfirmModal();

        // showToast('database not yet implemented. token: '+ csrfToken, 'error');
        let route = "{{ route('tour-item.disable') }}";
        $.ajax({
            url: route,
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: {
                tour_item_id: $selectedTourItemId
            },
            success: function(response) {
                showToast(response.message, 'success');
            },
            error: function(xhr) {
                showToast('Error disabling tour item', 'error');
            }
        });
    }
</script>

{{-- this 'defer' script is for drawer.blade.php line 25 --}}
<script defer>
    function reload() {
        $('#first-tab-btn').click();
        $('#location').val('');
        $('#days').val('');
        $('input[name="food_types[]"]').prop('checked', false);
        $('input[name="time_preference[]"]').prop('checked', false);
        window.setLoading(true, 'Đang làm mới');
        location.reload();
        setTimeout(() => {
            window.setLoading(false);
        }, 300);
    }

    function setWeatherVisible(boolean) {
        $('#weather-section').toggleClass('hidden', !boolean);
    }

    function showFavorite() {
        setWeatherVisible(false);
        $.ajax({
            url: "{{ route('tour.favorite') }}",
            type: "GET",
            success: function(response) {
                // console.log(response);
                pushDataToDetail(response.data, true);
                window.setLoading(false);
                setWeatherVisible(true);
            },
            beforeSend: function() {
                $('#my-drawer').click();
                window.setLoading(true, 'Đang tải');
            },
            error: function(xhr, status, error) {
                console.error("Error fetching data:", error);
            }
        })
    }

    function showDetail(id) {
        // console.log(id);
        $('#final-tab-btn').click();
        if (id == -1) {
            showFavorite();
            return;
        }
        $.ajax({
            url: "{{ route('tour.detail') }}",
            type: "POST",
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            data: {
                id: id
            },
            success: function(response) {
                // console.log(response);
                pushDataToDetail(response.data);
                window.setLoading(false);
            },
            beforeSend: function() {
                $('#my-drawer').click();
                window.setLoading(true, (id ? 'Đợi một lát' : 'Đang tải'));
                if (!id) {
                    setTimeout(() => {
                        window.setLoading(false);
                    }, 300);
                }
            },
            error: function(xhr, status, error) {
                console.error("Error fetching data:", error);
            }
        });
    }

    function openEditTourModal(tour_id) {
        // console.log(tour_id);
        let navLink = $('#tour-name-' + tour_id);
        // console.log('nav link content: ' + navLink.html());
        if (tour_id == undefined) {
            return;
        }
        $('#edit-tour-name').val(navLink.html().replace(/\s/g, ''));
        $('#edit-tour-id').attr('value', `${tour_id}`);
        const modal = document.getElementById('editTourModal');
        modal.show();
    }
</script>

<script>
    $(document).ready(function() {
        let searchTimeout;
        let selectedLocation = null;

        // Set default dates 
        const today = new Date();
        const tomorrow = new Date(today);
        tomorrow.setDate(tomorrow.getDate() + 1);

        $('#start-date').val(formatDate(today));
        $('#end-date').val(formatDate(tomorrow));

        // Format date as YYYY-MM-DD
        function formatDate(date) {
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');
            return `${year}-${month}-${day}`;
        }

        // Location search with debounce
        $('#location-search').on('input', function() {
            const query = $(this).val().trim();

            clearTimeout(searchTimeout);

            if (query.length < 2) {
                $('#search-results').addClass('hidden').html('');
                return;
            }

            searchTimeout = setTimeout(function() {
                searchLocations(query);
            }, 500); // 500ms debounce
        });

        // Search for locations
        function searchLocations(query) {
            $.ajax({
                url: '/api/search-locations',
                method: 'GET',
                data: {
                    query: query
                },
                beforeSend: function() {
                    // Show loading indicator if needed
                },
                success: function(response) {
                    // console.log('Search results:', response);
                    displaySearchResults(response);
                },
                error: function(xhr) {
                    console.error('Error searching locations:', xhr);
                    $('#search-results').addClass('hidden');
                }
            });
        }

        // Display search results
        function displaySearchResults(response) {
            const $resultsContainer = $('#search-results');

            if (!response || !response.status === 'success' || !response.data || response.data.length === 0) {
                $resultsContainer.addClass('hidden');
                return;
            }

            let html = '<ul class="menu p-2">';

            response.data.forEach(function(location) {
                const locationText = location.admin1 ?
                    `${location.name}, ${location.admin1}` :
                    location.name;

                html +=
                    `<li><a href="#" class="location-item" data-name="${location.name}" data-admin="${location.admin1 || ''}">${locationText}</a></li>`;
            });

            html += '</ul>';

            $resultsContainer.html(html).removeClass('hidden');
        }


        // Handle location selection
        $(document).on('click', '.location-item', function(e) {
            e.preventDefault();

            const name = $(this).data('name');
            const admin = $(this).data('admin');

            selectedLocation = {
                name: name,
                admin: admin
            };

            // Update UI
            const displayName = admin ? `${name}, ${admin}` : name;
            $('#location-name').text('Đã chọn: ' + displayName);
            $('#selected-location').removeClass('hidden');
            $('#location-search').val('');
            $('#search-results').addClass('hidden');
        });

        // Clear selected location
        $('#clear-location').on('click', function() {
            selectedLocation = null;
            $('#selected-location').addClass('hidden');
            $('#weather-info').addClass('hidden');
        });

        // Get current weather button
        $('#get-current-btn').on('click', function() {
            if (!selectedLocation) {
                alert('Please select a location first');
                return;
            }

            getCurrentWeather(selectedLocation);
        });

        // Get weather for date range button
        $('#get-weather-btn').on('click', function() {
            if (!selectedLocation) {
                alert('Please select a location first');
                return;
            }

            const startDate = $('#start-date').val();
            const endDate = $('#end-date').val();

            if (!startDate || !endDate) {
                alert('Please select start and end dates');
                return;
            }

            if (new Date(startDate) > new Date(endDate)) {
                alert('Start date cannot be after end date');
                return;
            }

            getWeatherForecast(selectedLocation, startDate, endDate);
        });

        // Get current weather data
        function getCurrentWeather(location) {
            $('#weather-loading').removeClass('hidden');
            $('#weather-info').addClass('hidden');
            $('#weather-error').addClass('hidden');

            $.ajax({
                url: '/api/get-current-weather',
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: {
                    location: location.name,
                    admin: location.admin
                },
                success: function(response) {
                    // console.log('Current weather response:', response);
                    $('#weather-loading').addClass('hidden');
                    displayCurrentWeather(response, location);
                },
                error: function(xhr) {
                    // console.log('ẻreror curent weather response:', xhr);
                    $('#weather-loading').addClass('hidden');
                    $('#weather-error').removeClass('hidden');
                    console.error('Error fetching current weather data:', xhr);
                }
            });
        }

        // Get weather forecast for date range
        function getWeatherForecast(location, startDate, endDate) {
            $('#weather-loading').removeClass('hidden');
            $('#weather-info').addClass('hidden');
            $('#weather-error').addClass('hidden');

            $.ajax({
                url: '/api/get-weather-forecast',
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: {
                    location: location.name,
                    admin: location.admin,
                    start_date: startDate,
                    end_date: endDate
                },
                success: function(response) {
                    // console.log('Weather forecast response:', response);

                    $('#weather-loading').addClass('hidden');
                    displayWeatherForecast(response, location, startDate, endDate);
                },
                error: function(xhr) {
                    // console.log('Error weather forecast response:', xhr);
                    $('#weather-loading').addClass('hidden');
                    $('#weather-error').removeClass('hidden');
                    console.error('Error fetching weather forecast:', xhr);
                }
            });
        }

        // Display current weather data (hourly)
        function displayCurrentWeather(response, location) {
            if (!response || !response.status === 'success' || !response.data || !response.data.hourly) {
                $('#weather-error').removeClass('hidden');
                return;
            }

            const data = response.data;
            const hourlyData = data.hourly;
            const units = data.hourly_units;

            // Set location name
            const displayName = location.admin ? `${location.name}, ${location.admin}` : location.name;
            $('#weather-location').text(displayName);

            // Set current date
            const currentDate = new Date();
            $('#weather-date').text(currentDate.toLocaleDateString('en-US', {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            }));

            // Set current temperature (first entry in the hourly data)
            $('#current-temp').text(`${hourlyData.temperature_2m[0]}${units.temperature_2m}`);

            // Set current wind speed
            $('#current-wind').text(`${hourlyData.wind_speed_10m[0]} ${units.wind_speed_10m}`);

            // Display hourly forecast (next 24 hours)
            displayHourlyForecast(data);

            // Show hourly section, hide daily section
            $('#hourly-forecast-section').removeClass('hidden');
            $('#daily-forecast-section').addClass('hidden');

            // Show weather info
            $('#weather-info').removeClass('hidden');
        }

        // Display weather forecast for date range
        function displayWeatherForecast(response, location, startDate, endDate) {
            if (!response || !response.data || !response.data.hourly) {
                $('#weather-error').removeClass('hidden');
                return;
            }

            const data = response.data;

            // Set location name
            const displayName = location.admin ? `${location.name}, ${location.admin}` : location.name;
            $('#weather-location').text(displayName);

            // Set date range
            $('#weather-date').text(`${formatDateForDisplay(startDate)} to ${formatDateForDisplay(endDate)}`);

            // Group hourly data by date
            const dailyGroups = groupHourlyDataByDate(data.hourly);
            displayDailyForecast(dailyGroups, data.hourly_units);

            // Hide hourly section, show daily section
            $('#hourly-forecast-section').addClass('hidden');
            $('#daily-forecast-section').removeClass('hidden');

            // Show weather info
            $('#weather-info').removeClass('hidden');
        }

        // Helper function to group hourly data by date
        function groupHourlyDataByDate(hourlyData) {
            const groups = {};

            hourlyData.time.forEach((time, index) => {
                const date = time.split('T')[0];
                if (!groups[date]) {
                    groups[date] = {
                        temperatures: [],
                        windSpeeds: []
                    };
                }
                groups[date].temperatures.push(hourlyData.temperature_2m[index]);
                groups[date].windSpeeds.push(hourlyData.wind_speed_10m[index]);
            });

            return groups;
        }

        // Display daily forecast using grouped hourly data
        function displayDailyForecast(dailyGroups, units) {
            const $dailyContainer = $('#daily-forecast');
            $dailyContainer.empty();

            Object.entries(dailyGroups).forEach(([date, data]) => {
                const maxTemp = Math.max(...data.temperatures);
                const minTemp = Math.min(...data.temperatures);
                const avgWind = (data.windSpeeds.reduce((a, b) => a + b, 0) / data.windSpeeds.length)
                    .toFixed(1);

                const dateObj = new Date(date);
                const dayName = dateObj.toLocaleDateString('en-US', {
                    weekday: 'long'
                });
                const formattedDate = dateObj.toLocaleDateString('en-US', {
                    month: 'short',
                    day: 'numeric'
                });

                const dailyHtml = `
                    <div class="bg-base-200 rounded-lg p-3">
                        <div class="flex justify-between items-center">
                            <div>
                                <p class="font-medium">${dayName}</p>
                                <p class="text-sm text-base-content/70">${formattedDate}</p>
                                <p class="text-sm text-base-content/70">Wind: ${avgWind} ${units.wind_speed_10m}</p>
                            </div>
                            <div class="text-right">
                                <p class="font-bold">${maxTemp}${units.temperature_2m}</p>
                                <p class="text-sm text-base-content/70">${minTemp}${units.temperature_2m}</p>
                            </div>
                        </div>
                    </div>
                `;

                $dailyContainer.append(dailyHtml);
            });
        }

        // Format date for display (e.g., "April 15, 2025")
        function formatDateForDisplay(dateString) {
            const date = new Date(dateString);
            return date.toLocaleDateString('en-US', {
                month: 'long',
                day: 'numeric',
                year: 'numeric'
            });
        }

        // Display hourly forecast data
        function displayHourlyForecast(data) {
            const $hourlyContainer = $('#hourly-forecast');
            $hourlyContainer.empty();

            // Display next 8 hours
            const hoursToShow = Math.min(8, data.hourly.time.length);

            for (let i = 0; i < hoursToShow; i++) {
                const time = new Date(data.hourly.time[i]);
                const hour = time.getHours();
                const formattedHour = hour === 0 ? '12 AM' : hour === 12 ? '12 PM' : hour < 12 ? `${hour} AM` :
                    `${hour - 12} PM`;

                const temp = data.hourly.temperature_2m[i];

                const hourlyHtml = `
            <div class="text-center p-2 bg-base-200 rounded-lg">
                <p class="text-sm">${formattedHour}</p>
                <p class="font-bold">${temp}${data.hourly_units.temperature_2m}</p>
            </div>
        `;

                $hourlyContainer.append(hourlyHtml);
            }
        }

        // Add date restrictions to the calendar inputs
        function setDateRestrictions() {
            const today = new Date();
            const maxDate = new Date(today);
            maxDate.setDate(today.getDate() + 14); // Add 14 days to today

            // Format dates for HTML date input (YYYY-MM-DD)
            const todayStr = formatDate(today);
            const maxDateStr = formatDate(maxDate);

            // Set min and max attributes for both date inputs
            $('#start-date').attr({
                'min': todayStr,
                'max': maxDateStr
            }).val(todayStr);

            $('#end-date').attr({
                'min': todayStr,
                'max': maxDateStr
            }).val(formatDate(tomorrow));
        }

        // Call this function when document is ready
        setDateRestrictions();

        // Update end-date min value when start-date changes
        $('#start-date').on('change', function() {
            const startDate = new Date($(this).val());
            $('#end-date').attr('min', $(this).val());

            // If end date is before start date, update it
            if (new Date($('#end-date').val()) < startDate) {
                $('#end-date').val($(this).val());
            }
        });
    });
</script>
