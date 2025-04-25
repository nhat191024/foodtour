$(document).ready(() => {
    // ajax functions
    async function callGet(route, method, ...params) {
        return new Promise((resolve, reject) => {
            $.ajax({
                url: route,
                method: method,
                data: params,
                success: function (response) {
                    setLoading(false);
                    resolve(response);
                },
                beforeSend: function (response) {
                    setLoading(true, 'Đợi một lát');
                },
                error: function (xhr, status, error) {
                    setLoading(false);
                    showToast('Có lỗi xảy ra', 'error');
                    console.error("Error:", error);
                    reject(error);
                }
            });
        });
    }

    // Load tours into the select dropdown
    const TourUtils = {
        loadTours: async function () {
            const response = await callGet(window.baseAppUrl + '/api/get-all-tours', 'GET');

            // This would typically be an AJAX call to your backend
            const tours = response.data;

            const tourSelect = $("#tour-select");
            tours.forEach((tour) => {
                const date = new Date(tour.created_at);
                const formattedDate = `${date.getHours().toString().padStart(2, "0")}:${date.getMinutes().toString().padStart(2, "0")} ${date.getDate().toString().padStart(2, "0")}/${(date.getMonth() + 1).toString().padStart(2, "0")}`;
                tourSelect.append(
                    `<option value="${tour.id}" data-datetime="${tour.created_at}">${tour.name} (${formattedDate})</option>`,
                );
            });
        }
    };
    window.TourUtils = TourUtils;

    // Load tour items based on selected tour
    async function loadTourItems(tourId) {
        // This would typically be an AJAX call to your backend
        // For demonstration, we'll use dummy data

        const response = await callGet(window.baseAppUrl + '/api/get-tour-items/' + tourId, 'GET');

        const tourItems = response.data;

        const tourItemSelect = $("#tour-item-select")
        tourItemSelect.empty()
        tourItemSelect.append('<option value="" disabled selected>Chọn quán của tour đó</option>')

        if (tourItems) {
            tourItems.forEach((item) => {
                tourItemSelect.append(`<option value="${item.id}">${item.name}</option>`)
            })
            tourItemSelect.prop("disabled", false)
        } else {
            tourItemSelect.prop("disabled", true)
        }
    }

    // When tour is selected, load its items
    $("#tour-select").change(async function () {
        const tourId = $(this).val()
        if (tourId) {
            await loadTourItems(tourId)

            const selectedTourName = $(this).find("option:selected").text().split(" (")[0]
            const currentTripName = $("#trip-name").val()

            // If trip name is the same as a previous tour name, clear it
            const history = JSON.parse(localStorage.getItem("calculationHistory") || "[]")
            if (
                currentTripName &&
                currentTripName !== selectedTourName &&
                history.some((calc) => calc.tourName === currentTripName)
            ) {
                $("#trip-name").val("")
            }
        }
    })

    // Add a new food item
    $("#add-food-item").click(() => {
        const newItem = `
            <div class="food-item grid grid-cols-12 gap-2">
                <div class="col-span-8">
                    <input type="text" placeholder="Tên món" class="input input-bordered w-full food-name" />
                </div>
                <div class="col-span-3">
                    <input type="number" placeholder="Giá" class="input input-bordered w-full food-price" />
                </div>
                <div class="col-span-1 flex items-center justify-center">
                    <button type="button" class="btn btn-square btn-sm btn-error remove-food-item">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
        `
        $("#food-items-container").append(newItem)

        // Enable the first remove button if it was disabled
        if ($(".remove-food-item:disabled").length === 1) {
            $(".remove-food-item:disabled").prop("disabled", false)
        }
    })

    // Add a new member
    $("#add-member").click(() => {
        const newMember = `
            <div class="member-item grid grid-cols-12 gap-2">
                <div class="col-span-11">
                    <input type="text" placeholder="Tên thành viên" class="input input-bordered w-full member-name" />
                </div>
                <div class="col-span-1 flex items-center justify-center">
                    <button type="button" class="btn btn-square btn-sm btn-error remove-member">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
        `
        $("#members-container").append(newMember)

        // Enable the first remove button if it was disabled
        if ($(".remove-member:disabled").length === 1) {
            $(".remove-member:disabled").prop("disabled", false)
        }
    })

    // Remove food item (using event delegation for dynamically added elements)
    $(document).on("click", ".remove-food-item:not(:disabled)", function () {
        $(this).closest(".food-item").remove()

        // If only one food item remains, disable its remove button
        if ($(".food-item").length === 1) {
            $(".remove-food-item").prop("disabled", true)
        }
    })

    // Remove member (using event delegation for dynamically added elements)
    $(document).on("click", ".remove-member:not(:disabled)", function () {
        $(this).closest(".member-item").remove()

        // If only one member remains, disable its remove button
        if ($(".member-item").length === 1) {
            $(".remove-member").prop("disabled", true)
        }
    })

    // Calculate expenses
    $("#calculate-btn").click(() => {
        const tourId = $("#tour-select").val()
        if (!tourId) {
            alert("Vui lòng chọn tour!")
            return
        }

        // Get tour item
        const tourItemId = $("#tour-item-select").val()
        const tourItemName = tourItemId ? $("#tour-item-select option:selected").text() : ""

        // Get trip name (use tour name if empty)
        const tripName = $("#trip-name").val() || $("#tour-select option:selected").text().split(" (")[0]
        const tourDateTime = $("#tour-select option:selected").data("datetime")

        // Collect food items
        const foodItems = []
        $(".food-item").each(function () {
            const name = $(this).find(".food-name").val()
            const price = Number.parseFloat($(this).find(".food-price").val())

            if (name && !isNaN(price)) {
                foodItems.push({ name, price })
            }
        })

        // Collect members
        const members = []
        $(".member-item").each(function () {
            const name = $(this).find(".member-name").val()
            if (name) {
                members.push({ name })
            }
        })

        if (foodItems.length === 0) {
            alert("Vui lòng nhập ít nhất một món ăn!")
            return
        }

        if (members.length === 0) {
            alert("Vui lòng nhập ít nhất một thành viên!")
            return
        }

        // Calculate total and per-person amount
        const total = foodItems.reduce((sum, item) => sum + item.price, 0)
        const perPerson = total / members.length

        // Display results
        $("#results-section").removeClass("hidden")

        let resultsHTML = `
            <div class="p-3 bg-base-200 rounded-lg">
                <p class="font-medium">Tổng chi phí: <span class="font-bold text-primary">${total.toLocaleString()} VND</span></p>
                <p class="font-medium">Số thành viên: <span class="font-bold">${members.length}</span></p>
                <p class="font-medium">Mỗi người trả: <span class="font-bold text-primary">${perPerson.toLocaleString()} VND</span></p>
            </div>
        `

        if (tourItemName) {
            resultsHTML += `
            <div class="mt-3">
                <h4 class="font-medium mb-2">Địa điểm:</h4>
                <p>${tourItemName}</p>
            </div>
        `
        }

        resultsHTML += `
            <div class="mt-3">
                <h4 class="font-medium mb-2">Chi tiết món ăn:</h4>
                <ul class="list-disc pl-5">
        `

        foodItems.forEach((item) => {
            resultsHTML += `<li>${item.name}: ${item.price.toLocaleString()} VND</li>`
        })

        resultsHTML += `
                </ul>
            </div>
            <div class="mt-3">
                <h4 class="font-medium mb-2">Thành viên tham gia:</h4>
                <ul class="list-disc pl-5">
        `

        members.forEach((member) => {
            resultsHTML += `<li>${member.name}</li>`
        })

        resultsHTML += `
                </ul>
            </div>
        `

        $("#calculation-results").html(resultsHTML)

        // Save to history
        saveCalculation({
            tourId,
            tourName: $("#tour-select option:selected").text().split(" (")[0],
            tourItemId,
            tourItemName,
            tripName: tripName,
            datetime: tourDateTime,
            foodItems,
            members,
            total,
            perPerson,
        })
    })

    // Save calculation to local storage
    function saveCalculation(calculation) {
        let history = JSON.parse(localStorage.getItem("calculationHistory") || "[]")
        history.unshift(calculation) // Add to beginning of array

        // Limit history to 10 items
        if (history.length > 10) {
            history = history.slice(0, 10)
        }

        localStorage.setItem("calculationHistory", JSON.stringify(history))
    }

    // Load and display calculation history
    $("#view-history").click(() => {
        const history = JSON.parse(localStorage.getItem("calculationHistory") || "[]")

        if (history.length === 0) {
            $("#calculation-history").html('<p class="text-base-content/70">Chưa có lịch sử tính toán nào.</p>')
        } else {
            let historyHTML = ""

            history.forEach((calc, index) => {
                // Format date as hh:mm dd/mm
                const date = new Date(calc.datetime)
                const formattedDate = `${date.getHours().toString().padStart(2, "0")}:${date.getMinutes().toString().padStart(2, "0")} ${date.getDate().toString().padStart(2, "0")}/${(date.getMonth() + 1).toString().padStart(2, "0")}`

                historyHTML += `
          <div class="border border-base-300 rounded-lg p-3 relative calculation-history-item" data-index="${index}">
              <div class="flex justify-between items-center mb-1">
                  <h4 class="font-medium">${calc.tripName}</h4>
                  <button type="button" class="btn btn-outline btn-error btn-xs btn-square delete-history-item" data-index="${index}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                  </button>
              </div>
              <span class="text-sm text-base-content/70 block mb-1">${formattedDate}</span>
              <p class="text-sm">Tổng: ${calc.total.toLocaleString()} VND | Mỗi người: ${calc.perPerson.toLocaleString()} VND</p>
              ${calc.tourItemName ? `<p class="text-sm text-base-content/70 mt-1">Địa điểm: ${calc.tourItemName}</p>` : ""}
          </div>
        `
            })

            $("#calculation-history").html(historyHTML)
        }

        $("#calculation-history").removeClass("hidden")
    })

    // Load calculation from history
    $(document).on("click", ".calculation-history-item", async function (e) {
        // Only proceed if the click wasn't on the delete button
        if (!$(e.target).closest(".delete-history-item").length) {
            const index = $(this).data("index")
            const history = JSON.parse(localStorage.getItem("calculationHistory") || "[]")
            const calculation = history[index]

            if (calculation) {
                // Set tour
                $("#tour-select").val(calculation.tourId)

                // Load tour items and set the selected item
                await loadTourItems(calculation.tourId)
                if (calculation.tourItemId) {
                    setTimeout(() => {
                        $("#tour-item-select").val(calculation.tourItemId)
                    }, 100) // Small delay to ensure items are loaded
                }

                // Set trip name
                $("#trip-name").val(calculation.tripName)

                // Clear existing items
                $("#food-items-container").empty()
                $("#members-container").empty()

                // Add food items
                calculation.foodItems.forEach((item, i) => {
                    const isFirst = i === 0
                    const foodItem = `
                    <div class="food-item grid grid-cols-12 gap-2">
                        <div class="col-span-8">
                            <input type="text" placeholder="Tên món" class="input input-bordered w-full food-name" value="${item.name}" />
                        </div>
                        <div class="col-span-3">
                            <input type="number" placeholder="Giá" class="input input-bordered w-full food-price" value="${item.price}" />
                        </div>
                        <div class="col-span-1 flex items-center justify-center">
                            <button type="button" class="btn btn-square btn-sm btn-error remove-food-item" ${isFirst && calculation.foodItems.length === 1 ? "disabled" : ""}>
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>
                `
                    $("#food-items-container").append(foodItem)
                })

                // Add members
                calculation.members.forEach((member, i) => {
                    const isFirst = i === 0
                    const memberItem = `
                    <div class="member-item grid grid-cols-12 gap-2">
                        <div class="col-span-11">
                            <input type="text" placeholder="Tên thành viên" class="input input-bordered w-full member-name" value="${member.name}" />
                        </div>
                        <div class="col-span-1 flex items-center justify-center">
                            <button type="button" class="btn btn-square btn-sm btn-error remove-member" ${isFirst && calculation.members.length === 1 ? "disabled" : ""}>
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>
                `
                    $("#members-container").append(memberItem)
                })

                // Hide history
                $("#calculation-history").addClass("hidden")

                // Recalculate to show results
                $("#calculate-btn").click()
            }
        }
    })

    // Delete history item
    $(document).on("click", ".delete-history-item", function (e) {
        e.stopPropagation() // Prevent triggering the parent click event

        const index = $(this).data("index")
        const history = JSON.parse(localStorage.getItem("calculationHistory") || "[]")

        // Remove the item at the specified index
        history.splice(index, 1)

        // Save the updated history back to localStorage
        localStorage.setItem("calculationHistory", JSON.stringify(history))

        // Refresh the history display
        $("#view-history").click()
    })

    // Initialize (should only be called)
    // loadTours()
    // Initially disable tour item select until a tour is selected
    $("#tour-item-select").prop("disabled", true)
})
