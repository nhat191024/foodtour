// global variable to store the select-delete tour item ID
$selectedTourItemId = null;

//* this is to show the delete confirm modal
function confirmDelete(tourItemId) {
    $selectedTourItemId = tourItemId;
    const modal = document.getElementById('deleteConfirmModal');
    modal.show();
}

//* this is to show the favorite confirm modal
function confirmFavorite(tourItemId) {
    $selectedTourItemId = tourItemId;
    const modal = document.getElementById('favoriteConfirmModal');
    modal.show();
}

//* this is to show the unfavorite confirm modal
function confirmUnfavorite(tourItemId) {
    $selectedTourItemId = tourItemId;
    const modal = document.getElementById('unfavoriteConfirmModal');
    modal.show();
}

//* this is to show/hide the 'Add Tour Item' button when a tour item is deleted
function toggleAddTourItemButton(tourItemId, isShow) {
    const button = $(`#btn-add-tour-item-${tourItemId}`);
    if (isShow) {
        button.removeClass('hidden');
    }
    else {
        button.addClass('hidden');
    }

}

//* this is to show/hide the tour item when a tour item is deleted
function toggleTourItemVisibility(tourItemId, isShow) {
    const item = $(`#tour-item-${tourItemId}`);
    if (isShow) {
        item.removeClass('hidden');
    }
    else {
        item.addClass('hidden');
    }
}

//* this will push new tour item to the selected *empty* tour item slot
//* more specifically, under the "Add Item" button
function appendNewTourItemBtnClicked(tourItemId) {
    // console.log('appendNewTourItemBtnClicked', tourItemId);

    // make ajax call to
    $.ajax({
        url: baseAppUrl + '/tour-item/new',
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        data: {
            tour_item_id: tourItemId,
        },
        success: function (response) {
            if (response.status === 'success') {
                // console.log('response from get new tour items: ', response.data);

                //* this tour item is for saving the *checkpoint*
                //* so we know where to put the new item in
                const item = $(`#tour-item-${tourItemId}`);

                //* new items will be appended here
                const itemsContainer = $('#new-tour-item-list-' + tourItemId);

                const itemElement = document.createElement('div');
                itemElement.className =
                    'bg-base-100 rounded-xl p-5 shadow-md border border-base-300 hover:shadow-lg transition-all duration-300';
                itemElement.id = `tour-item-${response.data.id}`;

                itemElement.innerHTML = getTourItemContent(
                    response.data.id,
                    response.data.name,
                    response.data.address,
                    response.data.description,
                    response.data.latitude,
                    response.data.longitude
                );
                lastTourId = item.id;
                itemsContainer.append(itemElement);
                window.setLoading(false);
                showToast(response.message, 'success');
            }
            else {
                window.setLoading(false);
                showToast(response.message, 'error');
            }
        },
        beforeSend: function () {
            window.setLoading(true, 'Đang tìm địa điểm phù hợp');
            setTimeout(() => {
                window.setLoading(false);
            }, 120000);
        },
        complete: function () {
            setTimeout(() => {
                window.setLoading(false);
                showToast('Có lỗi xảy ra, vui lòng thử lại sau.', 'error');
            }, 120000);
        },
        error: function (xhr, status, error) {
            // console.log('Error:', error, xhr, status);

            // if get 401 error, redirect to login page
            window.setLoading(false);
            if (xhr.status === 401 || xhr.status === 403) {
                showToast('Vui lòng đăng nhập để tiếp tục.', 'error');
                setTimeout(() => {
                    window.location.href = '/login';
                }, 2000);
            } else {
                showToast('Có lỗi xảy ra, vui lòng thử lại sau.', 'error');
            }
        }
    });
}

function getHeartIcon(isFavorite, itemId) {
    if (isFavorite) {
        return `
            <button type="button"
                class="btn btn-error hover:btn-outline btn-sm"
                onclick="confirmUnfavorite(${itemId})">
            <svg xmlns="http://www.w3.org/2000/svg"
                class="h-4 w-4" fill="currentColor"
                viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round"
                stroke-width="2"
                d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
            </svg>
            </button>
        `;
    } else {
        return `
            <button type="button"
                    class="btn btn-outline btn-error btn-sm"
                    onclick="confirmFavorite(${itemId})">
                <svg xmlns="http://www.w3.org/2000/svg"
                    class="h-4 w-4" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        stroke-width="2"
                        d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                </svg>
            </button>
        `;
    }
}

//* returns HTML content for the tour item
function getTourItemContent(itemId, itemName, itemAddress, itemDescription, itemLatitude, itemLongitude, isFavorite = false) {
    return `
        <h4 class="font-bold text-lg mb-2">${itemName}</h4>

        <div class="flex items-start mb-3">
            <svg xmlns="http://www.w3.org/2000/svg"
                class="h-5 w-5 mr-2 text-accent flex-shrink-0 mt-0.5"
                fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round"
                    stroke-width="2"
                    d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                <path stroke-linecap="round" stroke-linejoin="round"
                    stroke-width="2"
                    d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            <p class="text-sm text-base-content/70">${itemAddress}</p>
        </div>

        <div class="mb-4 pl-7">
            <p class="text-sm text-base-content">${itemDescription}</p>
        </div>

        <div class="flex justify-end gap-3">
            ${getHeartIcon(isFavorite, itemId)}
            <button type="button"
                class="btn btn-outline btn-accent btn-sm"
                onclick="openMap(${itemLatitude}, ${itemLongitude})">
                <svg xmlns="http://www.w3.org/2000/svg"
                    class="h-4 w-4 mr-1" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        stroke-width="2"
                        d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                </svg>
                Map
            </button>
            <button type="button"
                class="btn btn-outline btn-error btn-sm ${isFavorite ? 'hidden' : ''}"
                onclick="confirmDelete(${itemId})">
                <svg xmlns="http://www.w3.org/2000/svg"
                    class="h-4 w-4 mr-1" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        stroke-width="2"
                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
                Hủy
            </button>
        </div>
        `;
}

function closeConfirmDeleteModal() {
    const modal = document.getElementById('deleteConfirmModal');
    modal.close();
}

function closeConfirmFavoriteModal() {
    const modal = document.getElementById('favoriteConfirmModal');
    modal.close();
}

function openMap(lat, lng) {
    window.open(`https://www.google.com/maps?q=${lat},${lng}`, '_blank');
}

function pushDataToDetail(data, isFavorite = false) {

    // console.log('is favorite', isFavorite);

    const tab4 = document.getElementById('tab4');

    tab4.innerHTML = '';

    tab4.innerHTML = `
        <div class="flex items-center justify-center gap-4 mb-4">
            <button type="button" onclick="reload();" class="mt-2 btn btn-outline btn-primary w-1/3 mb-3 rounded-2xl">
                Quay lại
            </button>
            <button type="button" onclick="screenshot('screenshot-area');" class="mt-2 btn btn-outline btn-primary w-1/3 mb-3 rounded-2xl">
                Lưu ảnh
            </button>
        </div>
    `;

    const header = document.createElement('h1');
    header.className = 'text-3xl font-bold mb-0 text-primary';
    tab4.appendChild(header);

    Object.entries(data).forEach(([day, timeGroups]) => {
        const dayContainer = document.createElement('div');
        dayContainer.className = 'mb-8 overflow-hidden shadow-lg border border-base-300';
        dayContainer.id = `screenshot-area`;

        dayContainer.innerHTML = `
            <div class="bg-base-200 p-4 border-b border-base-300">
                <h2 class="text-xl font-bold flex items-center">
                    <span>${day.toUpperCase()}</span>
                </h2>
            </div>
        `;

        Object.entries(timeGroups).forEach(([time, items]) => {
            const timeContainer = document.createElement('div');
            timeContainer.className = 'p-4 bg-base-100 border-b border-base-300';

            timeContainer.innerHTML = `
                <h3 class="text-lg font-medium mb-3 flex items-center text-secondary">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2"
                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    ${time.toUpperCase()}
                </h3>
                <div class="space-y-4"></div>
            `;

            const itemsContainer = timeContainer.querySelector('.space-y-4');
            let lastTourId = null;
            items.forEach(item => {
                // itemsContainer.innerHTML += `

                //     `
                const itemElement = document.createElement('div');
                itemElement.className =
                    'bg-base-100 rounded-xl p-5 shadow-md border border-base-300 hover:shadow-lg transition-all duration-300';
                itemElement.id = `tour-item-${item.id}`;
                itemElement.innerHTML = getTourItemContent(
                    item.id,
                    item.name,
                    item.address,
                    item.description,
                    item.latitude,
                    item.longitude,
                    isFavorite
                );
                lastTourId = item.id;
                itemsContainer.appendChild(itemElement);
            });

            // console.log('isFavorite', isFavorite);

            if (isFavorite == false) {
                itemsContainer.innerHTML += `
                <div id="new-tour-item-list-${lastTourId}" class="space-y-4"></div>
                <button onclick="appendNewTourItemBtnClicked(${lastTourId})" type="button" id="btn-add-tour-item-${lastTourId}" class="btn btn-outline btn-primary w-full mb-3">
                Thêm mới
                </button>
                `;
            } else if (items.length === 0) {
                itemsContainer.innerHTML += `
                    <div class="alert alert-error">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 stroke-current shrink-0" fill="none" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>Thử tìm kiếm bất kỳ thứ gì rồi bấm vào <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 inline stroke-error" fill="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                        </svg> để yêu thích quán</span>
                    </div>
                    <div id="new-tour-item-list-${lastTourId}" class="space-y-4"></div>
                `;

            }
            // itemsContainer.id = `tour - item - list - ${ lastTourId } `;
            dayContainer.appendChild(timeContainer);
        });

        tab4.appendChild(dayContainer);
    });
}
