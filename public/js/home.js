const nextButtons = document.querySelectorAll('.next-tab');
const backButtons = document.querySelectorAll('.back-button');
const foodOptions = document.querySelectorAll('.food-option');
const timeOptions = document.querySelectorAll('.time-option');
const form = document.getElementById('foodTourForm');

$(document).ready(function () {
    nextButtons.forEach(button => {
        button.addEventListener('click', function () {
            const targetTab = this.getAttribute('data-target');
            switchToTab(targetTab);
        });
    });

    backButtons.forEach(button => {
        button.addEventListener('click', function () {
            const targetTab = this.getAttribute('data-target');
            if (targetTab) {
                switchToTab(targetTab);
            }
        });
    });


    foodOptions.forEach(option => {
        option.addEventListener('click', function (e) {
            e.stopPropagation();

            const checkbox = this.querySelector('input[type="checkbox"]');
            const box = this.querySelector('.option-box');

            checkbox.checked = !checkbox.checked;

            if (checkbox.checked) {
                box.classList.add('bg-base-300', 'border-primary', 'border-2');
                box.classList.remove('border-base-300');
            } else {
                box.classList.remove('bg-base-300', 'border-primary', 'border-2');
                box.classList.add('border-base-300');
            }
        });
    });

    foodOptions.forEach(option => {
        const checkbox = option.querySelector('input[type="checkbox"]');
        const box = option.querySelector('.option-box');

        if (checkbox.checked) {
            box.classList.add('bg-base-300', 'border-primary', 'border-2');
            box.classList.remove('border-base-300');
        }
    });


    timeOptions.forEach(option => {
        option.addEventListener('click', function () {
            const checkbox = this.querySelector('input[type="checkbox"]');
            checkbox.checked = !checkbox.checked;

            if (checkbox.checked) {
                this.querySelector('div').classList.add('bg-base-300', 'border-primary',
                    'border-2');
                this.querySelector('div').classList.remove('border-base-300');
            } else {
                this.querySelector('div').classList.remove('bg-base-300', 'border-primary',
                    'border-2');
                this.querySelector('div').classList.add('border-base-300');
            }

            if (checkbox.value === 'cả-ngày' && checkbox.checked) {
                timeOptions.forEach(opt => {
                    const otherCheckbox = opt.querySelector(
                        'input[type="checkbox"]');
                    if (otherCheckbox.value !== 'cả-ngày') {
                        otherCheckbox.checked = false;
                        opt.querySelector('div').classList.remove('bg-base-300',
                            'border-primary', 'border-2');
                        opt.querySelector('div').classList.add('border-base-300');
                    }
                });
            } else if (checkbox.checked) {
                timeOptions.forEach(opt => {
                    const otherCheckbox = opt.querySelector(
                        'input[type="checkbox"]');
                    if (otherCheckbox.value === 'cả-ngày') {
                        otherCheckbox.checked = false;
                        opt.querySelector('div').classList.remove('bg-base-300',
                            'border-primary', 'border-2');
                        opt.querySelector('div').classList.add('border-base-300');
                    }
                });
            }
        });
    });

    timeOptions.forEach(option => {
        const checkbox = option.querySelector('input[type="checkbox"]');
        const box = option.querySelector('div');

        if (checkbox.checked) {
            box.classList.add('bg-base-300', 'border-primary', 'border-2');
            box.classList.remove('border-base-300');
        }
    });

    form.addEventListener('submit', function (event) {
        event.preventDefault();
        const location = $('#location').val().trim();
        const days = $('#days').val().trim();

        if (!location || !days || isNaN(days)) {
            switchToTab('tab1');
            showToast('Cần ghi đầy đủ thông tin.', 'error');
            return false;
        }

        if (days && !Number.isInteger(Number(days))) {
            switchToTab('tab1');
            showToast('Số ngày không hợp lệ.', 'error');
            return false;
        }

        if (days && days > 14 || days <= 0) {
            switchToTab('tab1');
            showToast('Số ngày chỉ trong khoảng 1 dến 14.', 'error');
            return false;
        }

        const foodTypes = document.querySelectorAll('input[name="food_types[]"]:checked');
        if (foodTypes.length === 0) {
            switchToTab('tab2');
            showToast('Hãy chọn ít nhất một loại món ăn!', 'error');
            return false;
        }

        const timePreferences = document.querySelectorAll(
            'input[name="time_preference[]"]:checked');
        if (timePreferences.length === 0) {
            switchToTab('tab3');
            showToast('Vui lòng chọn ít nhất một thời gian.', 'error');
            return false;
        }

        submitForm(this);
    });

});

function submitForm(form) {
    const formData = $(form).serialize();

    $.ajax({
        url: form.action,
        type: 'POST',
        data: formData,
        beforeSend: function () {
            window.setLoading(true, 'Đang tìm địa điểm phù hợp');
            setTimeout(() => {
                window.setLoading(false);
            }, 120000);
        },
        success: function (response) {
            if (response.status === 'success') {
                $('#tour_id').val(getTourId(response.data));
                switchToTab('tab4');
                pushDataToDetail(response.data);
                window.setLoading(false);
            } else {
                window.setLoading(false);
                showToast(response.message, 'error');
            }
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
}

function switchToTab(tabId) {
    const tabs = document.querySelectorAll('.tab-panel');
    tabs.forEach(tab => {
        tab.classList.add('hidden');
    });

    const targetTab = document.getElementById(tabId);
    targetTab.classList.remove('hidden');
}
