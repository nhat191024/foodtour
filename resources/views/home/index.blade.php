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
    </div>
    </form>
    </div>
</x-app-layout>

<x-actions.loading-fullscreen></x-actions.loading-fullscreen>

<x-actions.modal id="deleteConfirmModal">
    <h3 class="font-bold text-lg mb-4">Xác nhận xóa</h3>
    <p class="mb-6">Bạn có chắc chắn muốn xóa mục này không?</p>
    <div class="modal-action">
        <button type="button" class="btn btn-error" onclick="handleDelete()">Xóa</button>
        <button class="btn" onclick="closeConfirmModal()">Hủy</button>
    </div>
</x-actions.modal>

<link rel="stylesheet" href="{{ asset('css/home.css') }}">

<script src="{{ asset('js/home.js') }}"></script>
<script src="{{ asset('js/detail-tab.js') }}"></script>

<script>
    // Confirm delete for tour item details page
    function handleDelete() {
        // console.log('selected to delete tour_item ID: ' + $selectedTourItemId);
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

    function showDetail(id) {
        // console.log(id);
        $('#final-tab-btn').click();
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
                // console.error("Error fetching data:", error);
            }
        });
    }
</script>
