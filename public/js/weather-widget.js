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
            if ($('#selected-location').hasClass('hidden')) {
                $('#get-current-btn').trigger('click');
            }
            return;
        }

        getCurrentWeather(selectedLocation);
    });

    // Get weather for date range button
    $('#get-weather-btn').on('click', function() {
        if (!selectedLocation) {
            let locationInput = $('#location-search').val().trim();
            if (locationInput.length < 1) {
                showToast('Vui lòng ghi địa điểm', 'danger');
                return;
            }
            if (locationInput.length < 4) {
                showToast('Địa điểm quá ngắn', 'danger');
                return;
            }

            // showToast('Vui lòng chọn một địa điểm', 'danger');
            getCurrentWeather(locationInput);
            return;
        }

        const startDate = $('#start-date').val();
        const endDate = $('#end-date').val();

        if (!startDate || !endDate) {
            // alert('Please select start and end dates');
            showToast('Vui lòng chọn ngày bắt đầu và ngày kết thúc', 'danger');
            return;
        }

        if (new Date(startDate) > new Date(endDate)) {
            // alert('Start date cannot be after end date');
            showToast('Ngày bắt đầu không thể sau ngày kết thúc', 'danger');
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
