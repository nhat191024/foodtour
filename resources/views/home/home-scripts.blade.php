<script>
    function handleConfirmEditTourModal() {
        $selectedTourId = $('#edit-tour-id').attr('value');
        $selectedTourName = $('#edit-tour-name').val().trim();

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
                if (response.status === 'success') {
                    // Update the tour name in the sidebar
                    $('#tour-name-' + $selectedTourId).text($selectedTourName);
                    document.getElementById('editTourModal').close();
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
                showToast('Vui lòng thử lại sau', 'error');
            }
        });
    }

    // Confirm favorite for tour item details page
    function handleConfirmFavoriteModal(isFavorite) {
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
                showToast('Error toggling favorite status', 'error');
            }
        });
    }

    // Confirm delete for tour item details page
    function handleDelete() {
        // toggleAddTourItemButton($selectedTourItemId, true);
        toggleTourItemVisibility($selectedTourItemId, false);
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
        $('#tour_id').val(0);
        setWeatherVisible(false);
        $.ajax({
            url: "{{ route('tour.favorite') }}",
            type: "GET",
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
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
        $('#final-tab-btn').click();
        if (id == -1) {
            showFavorite();
            return;
        }
        $.ajax({
            url: "{{ route('tour.detail') }}",
            type: "POST",
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: {
                id: id
            },
            success: function(response) {
                pushDataToDetail(response.data);
                window.setLoading(false);
                $('#tour_id').val(getTourId(response.data));
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

    function getTourId(data) {
        let tourId = -1;
        Object.keys(data).forEach(day => {
            Object.keys(data[day]).forEach(timeOfDay => {
                data[day][timeOfDay].forEach(activity => {
                    if (activity['tour_id'] !== undefined) {
                        tourId = activity['tour_id'];
                    }
                });
            });
        });

        return tourId;
    }

    function openEditTourModal(tour_id) {
        let navLink = $('#tour-name-' + tour_id);
        if (tour_id == undefined) {
            return;
        }
        $('#edit-tour-name').val(navLink.html().replace(/\s/g, ''));
        $('#edit-tour-id').attr('value', `${tour_id}`);
        const modal = document.getElementById('editTourModal');
        modal.show();
    }
</script>

<script type="module">
    async function screenshot(elementId) {
        let tourId = $('#tour_id').val();
        let route = (tourId == 0) ? "{{ route('tour.favorite') }}" : "{{ route('tour.detail') }}";
        let method = (tourId == 0) ? "GET" : "POST";

        $.ajax({
            url: route,
            type: method,
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            data: {
                id: tourId
            },
            success: function(response) {
                window.setLoading(false);
                let tourData = response.data;

                console.log('screenshot function found data', tourData);
                // To use:
                const itineraryImage = generateFoodItineraryImage(tourData);
                console.log('img data ', itineraryImage);
                // Create a modal for showing the image
                const modal = document.createElement('dialog');
                modal.id = 'screenshotModal';
                modal.className = 'modal';
                modal.innerHTML = `
                    <div class="modal-box max-w-4xl max-h-[80vh] overflow-y-auto">
                        <form method="dialog">
                            <button class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2">✕</button>
                        </form>
                        <h3 class="font-bold text-lg mb-4">Lịch trình của bạn</h3>
                        <h6 class="text-sm text-base-content/70 mb-4">Nhấn giữ vào ảnh để lưu</h6>
                        <img id="screenshotImage" class="w-full" />
                        <div class="modal-action">
                            <form method="dialog">
                                <button class="btn btn-error">Đóng</button>
                            </form>
                        </div>
                    </div>
                    <form method="dialog" class="modal-backdrop">
                        <button>close</button>
                    </form>
                `;

                document.body.appendChild(modal);

                // Update screenshot function
                const img = document.createElement('img');
                img.src = itineraryImage.dataUrl;
                document.getElementById('screenshotImage').src = itineraryImage.dataUrl;

                // Show the modal
                document.getElementById('screenshotModal').showModal();
                // modal.classList.add('modal-open');

                // Global download function
                window.downloadScreenshot = () => {
                    // Get the base64 data from the image
                    const imgElement = document.getElementById('screenshotImage');
                    let base64Data = imgElement.src;

                    // Make sure we have proper base64 data
                    // If the src starts with "data:image" we're good, otherwise we need to handle it
                    if (!base64Data.startsWith('data:image')) {
                        console.error('Image source is not a valid base64 data URI');
                        return;
                    }

                    try {
                        // Create an anchor element
                        const downloadLink = document.createElement('a');
                        downloadLink.download = 'downloaded-image.png';
                        downloadLink.href = base64Data;
                        downloadLink.style.display = 'none'; // Hide the element

                        // Add to DOM, trigger click, and remove
                        document.body.appendChild(downloadLink);
                        downloadLink.click();

                        // Small delay before removing
                        setTimeout(() => {
                            document.body.removeChild(downloadLink);
                        }, 100);

                        console.log('Download initiated');
                    } catch (error) {
                        console.error('Error downloading image:', error);
                    }
                };
            },
            beforeSend: function() {
                window.setLoading(true, 'Đợi một lát');
            },
            error: function(xhr, status, error) {
                window.setLoading(false);
                showToast('Vui lòng thử lại sau', 'error');
                console.error("Error fetching data:", error);
                return false;
            }
        });
    }


    window.screenshot = screenshot;
</script>
