// global variable to store the select-delete tour item ID
$selectedTourItemId = null;

//* this is to show the delete confirm modal
function confirmDelete(tourItemId) {
    $selectedTourItemId = tourItemId;
    const modal = document.getElementById('deleteConfirmModal');
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
    console.log('appendNewTourItemBtnClicked', tourItemId);
    const button = $(`#btn-add-tour-item-${tourItemId}`);
    // button.addClass('hidden');
    const item = $(`#tour-item-${tourItemId}`);
    // const parentOfItem = item.parentElement;
    const itemsContainer = $('#new-tour-item-list-' + tourItemId);

    const itemElement = document.createElement('div');
    itemElement.className =
        'bg-base-100 rounded-xl p-5 shadow-md border border-base-300 hover:shadow-lg transition-all duration-300';
    itemElement.id = `tour-item-${tourItemId}`;

    // make ajax call to
    $.ajax({
        url: '/tour-item/new',
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        data: {
            tour_item_id: tourItemId,
        },
        success: function (response) {
            if (response.status === 'success') {


                console.log('response from get new tour items: ', response.data);

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
                showToast('Thêm mới địa điểm thành công.', 'success');
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


    // parentOfItem.innerHTML += getTourItemContent(
    //     tourItemId,
    //     'Tour Item Name',
    //     'Tour Item Address',
    //     'Tour Item Description',
    //     0,
    //     0
    // );

    // TODO: this is just a temporary test data, will need to implement real API call
    // item.html(getTourItemContent(
    //     tourItemId,
    //     'Tour Item Name',
    //     'Tour Item Address',
    //     'Tour Item Description',
    //     0,
    //     0
    // ));

    // item.removeClass('hidden');
}

//* returns HTML content for the tour item
function getTourItemContent(itemId, itemName, itemAddress, itemDescription, itemLatitude, itemLongitude) {
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
                class="btn btn-outline btn-error btn-sm"
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

function closeConfirmModal() {
    const modal = document.getElementById('deleteConfirmModal');
    modal.close();
}

function openMap(lat, lng) {
    window.open(`https://www.google.com/maps?q=${lat},${lng}`, '_blank');
}

function pushDataToDetail(data) {
    const tab4 = document.getElementById('tab4');

    tab4.innerHTML = '';

    tab4.innerHTML = `
        <button type="button" onclick="reload();" class="mt-2 btn btn-outline btn-primary w-1/2 mb-3 rounded-2xl">
            Bắt đầu lại
        </button>
    `;

    const header = document.createElement('h1');
    header.className = 'text-3xl font-bold mb-0 text-primary';
    tab4.appendChild(header);

    Object.entries(data).forEach(([day, timeGroups]) => {
        const dayContainer = document.createElement('div');
        dayContainer.className = 'mb-8 overflow-hidden shadow-lg border border-base-300';

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
                    item.longitude
                );
                lastTourId = item.id;
                itemsContainer.appendChild(itemElement);
            });

            itemsContainer.innerHTML += `
                <div id="new-tour-item-list-${lastTourId}" class="space-y-4"></div>
                <button onclick="appendNewTourItemBtnClicked(${lastTourId})" type="button" id="btn-add-tour-item-${lastTourId}" class="btn btn-outline btn-primary w-full mb-3">
                    Thêm mới
                </button>
                `
            // itemsContainer.id = `tour-item-list-${lastTourId}`;
            dayContainer.appendChild(timeContainer);
        });

        tab4.appendChild(dayContainer);
    });
}
