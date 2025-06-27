<script setup>
import ClientLayout from '@/layouts/ClientAppLayout.vue';
import Button from '@/components/ui/button/Button.vue';
import { ref, computed, onUnmounted, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { Loader2 } from 'lucide-vue-next';
import DatePicker from 'vue-datepicker-next';
import 'vue-datepicker-next/index.css';
import 'vue-datepicker-next/locale/vi.es.js';
import { showToast } from '@/composables/useToasts';

import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';

const props = defineProps({
    questions: Array,
    summary_location: String,
    data: { type: Array, default: () => [] },
    locations: { type: Array, default: () => [] },
    initialLocation: { type: String, default: '' }
});

const currentStep = ref(0);

const form = useForm({
    answers: {},
});

const isLoading = ref(false);

const isCurrentAnswerValid = computed(() => {
    const questionId = currentQuestion.value.id;
    const questionType = currentQuestion.value.type;
    const currentAnswer = form.answers[questionId];
    if (currentAnswer === null || currentAnswer === undefined) return false;

    switch (questionType) {
        case 'text':
            return currentAnswer.length >= 3;
        case 'checkbox':
            if (currentAnswer.length === 0) return false;
            if (currentAnswer.includes('user_defined')) {
                const customText = form.answers[questionId + '_custom_text'];
                return !!customText && customText.trim() !== '';
            }
            return true;
        case 'radio':
            if (currentAnswer === '') return false;
            if (currentAnswer === 'user_defined') {
                const customText = form.answers[questionId + '_custom_text'];
                return !!customText && customText.trim() !== '';
            }
            return true;
        case 'number':
            return currentAnswer !== '';
        case 'date':
            return currentAnswer !== null && currentAnswer !== '';
        case 'date-range':
            if (!Array.isArray(currentAnswer)) return false;
            if (currentAnswer.length !== 2) return false;
            return currentAnswer[0] !== null && currentAnswer[1] !== null;

        default:
            return false;
    }
});

const currentQuestion = computed(() => props.questions[currentStep.value]);

function initializeAnswer() {
    const questionId = currentQuestion.value.id;
    if (form.answers[questionId] === undefined) {
        if (currentQuestion.value.type === 'checkbox') {
            form.answers[questionId] = [];
        } else {
            form.answers[questionId] = null;
        }
    }
}

function nextStep() {
    if (currentStep.value < props.questions.length - 1) {
        currentStep.value++;
        initializeAnswer();
    }
}

function prevStep() {
    if (currentStep.value > 0) {
        currentStep.value--;
    }
}

function submitSurvey() {
    isAskLocationModalOpen.value = false;
    const processedData = { ...form.data() };
    Object.keys(processedData.answers).forEach(key => {
        const value = processedData.answers[key];
        if (key.endsWith('_custom_text')) {
            return;
        }
        if (value === 'user_defined') {
            const customTextKey = key + '_custom_text';
            const customText = processedData.answers[customTextKey];
            if (customText && customText.trim() !== '') {
                processedData.answers[key] = customText.trim();
            }
        }
        if (Array.isArray(value) && value.includes('user_defined')) {
            const customTextKey = key + '_custom_text';
            const customText = processedData.answers[customTextKey];
            if (customText && customText.trim() !== '') {
                const newArray = value.map(item =>
                    item === 'user_defined' ? customText.trim() : item
                );
                processedData.answers[key] = newArray;
            }
        }
    });

    Object.keys(processedData.answers).forEach(key => {
        if (key.endsWith('_custom_text')) {
            delete processedData.answers[key];
        }
    });

    form.data(processedData);
    form.post(route('survey.store'), {
        onBefore: () => {
            isLoading.value = true;
        },
        onStart: () => {
            isLoading.value = true;
        },
        onSuccess: () => {
            isLoading.value = false;
        },
        onError: () => {
            isLoading.value = false;
        },
        onFinish: () => {
            isLoading.value = false;
        }
    });
}

function handleSelectionChange(clickedOption, event) {
    const isChecked = event.target.checked;
    const answers = form.answers[currentQuestion.value.id];
    if (clickedOption.allow_multi_select === false && isChecked) {
        selectExclusiveOption(currentQuestion.value.id, clickedOption.value);
        return;
    }

    if (clickedOption.allow_multi_select === true && isChecked) {
        const exclusiveOption = currentQuestion.value.options.find(opt => opt.allow_multi_select === false);
        if (exclusiveOption) {
            const exclusiveValue = exclusiveOption.value;
            const index = answers.indexOf(exclusiveValue);
            if (index > -1) {
                answers.splice(index, 1);
            }
        }
    }
}

const handleClickOnOption = (questionId) => {
    const question = currentQuestion.value;
    if (!question || question.id !== questionId) return;

    const exclusiveOption = question.options.find(opt => opt.value === 'user_defined');
    if (question.type === 'checkbox' && exclusiveOption && exclusiveOption.allow_multi_select === false) {
        selectExclusiveOption(questionId, 'user_defined');
    }
    else if (question.type === 'radio') {
        if (form.answers[questionId] !== 'user_defined') {
            form.answers[questionId] = 'user_defined';
        }
    }
};

const selectExclusiveOption = (questionId, exclusiveValue) => {
    form.answers[questionId] = [exclusiveValue];
};

initializeAnswer();
const hasErrors = computed(() => Object.keys(form.errors).length > 0);

function getToday() {
    const today = new Date();
    return today.toISOString().split('T')[0];
}

function getMaxDate() {
    const maxDate = new Date();
    maxDate.setFullYear(maxDate.getFullYear() + 1);
    return maxDate.toISOString().split('T')[0];
}

function getMaxEndDate(startDate) {
    const start = new Date(startDate);
    start.setFullYear(start.getFullYear() + 1);
    return start.toISOString().split('T')[0];
}

const isAskLocationModalOpen = ref(false);
// const itemToDelete = ref(null);
// const submitLocationForm = useForm({});

const openAskLocationModal = () => {
    isAskLocationModalOpen.value = true;
};

const proceedWithoutCurrentLocation = () => {
    submitSurvey();
};

const submitLocation = () => {
    // the submit function is called inside the function below
    getCurrentLocation();
};

const isFetchingLocation = ref(false);

const getCurrentLocation = () => {
    if (!navigator.geolocation) {
        showToast('Trình duyệt của bạn không hỗ trợ lấy vị trí.', 'error');
        showToast('submit 1 called.', 'success');
        return;
    }

    isFetchingLocation.value = true;

    navigator.geolocation.getCurrentPosition(
        async (position) => {
            const { latitude, longitude } = position.coords;
            try {
                const response = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${latitude}&lon=${longitude}`);
                if (!response.ok) throw new Error('Không thể chuyển đổi tọa độ.');
                const data = await response.json();
                // console.log(data);

                const address = data.address || {};
                const parts = [
                    address.road,
                    address.suburb,
                    address.town,
                    address.city,
                    address.state,
                    address.country
                ].filter(Boolean);

                if (parts.length > 0) {
                    form.answers['current_location'] = parts.join(', ');
                    // showToast('Đã lấy vị trí thành công: ' + parts.join(', '), 'success');
                    submitSurvey();
                } else {
                    // throw new Error('Không tìm thấy tên địa danh.');
                }
            } catch (error) {
                // console.error(error);
                // showToast('Không thể lấy tên địa danh từ tọa độ.', 'error');
            } finally {
                isFetchingLocation.value = false;
                submitSurvey();
            }
        },
        (error) => {
            let message = 'Không thể lấy vị trí.';
            switch (error.code) {
                case error.PERMISSION_DENIED:
                    message = "Bạn đã từ chối quyền truy cập vị trí.";
                    break;
                case error.POSITION_UNAVAILABLE:
                    message = "Thông tin vị trí không có sẵn.";
                    break;
                case error.TIMEOUT:
                    message = "Yêu cầu lấy vị trí đã hết hạn.";
                    break;
            }
            // showToast(message, 'error');
            isFetchingLocation.value = false;
            submitSurvey();
        }
    );
};

const countdownSeconds = ref(0);
const countdownInterval = ref(null);

const durationDays = computed(() => {
    const arr = form.answers['duration'];
    if (Array.isArray(arr) && arr.length === 2 && arr[0] && arr[1]) {
        const start = new Date(arr[0]);
        const end = new Date(arr[1]);
        let diff = Math.round((end - start) / (1000 * 60 * 60 * 24)) + 1;
        if (diff < 1) diff = 1;
        if (diff > 14) diff = 14;
        return diff;
    }
    return 1;
});

const estimatedSeconds = computed(() => {
    return Math.round(45 + (durationDays.value - 1) * ((180 - 45) / 13));
});

const estimatedMinutes = computed(() => Math.ceil(estimatedSeconds.value / 60));

const estimatedFinishTime = computed(() => {
    return new Date(Date.now() + countdownSeconds.value * 1000).toLocaleTimeString('vi-VN', {
        hour: '2-digit',
        minute: '2-digit'
    });
});

const countdownDisplay = computed(() => {
    const minutes = Math.floor(countdownSeconds.value / 60);
    const seconds = countdownSeconds.value % 60;
    return `${minutes}:${seconds.toString().padStart(2, '0')}`;
});

const startCountdown = () => {
    countdownSeconds.value = estimatedSeconds.value;

    countdownInterval.value = setInterval(() => {
        if (countdownSeconds.value > 0) {
            countdownSeconds.value--;
        } else {
            clearInterval(countdownInterval.value);
            countdownInterval.value = null;
        }
    }, 1000);
};

const stopCountdown = () => {
    if (countdownInterval.value) {
        clearInterval(countdownInterval.value);
        countdownInterval.value = null;
    }
    countdownSeconds.value = 0;
};

watch(isLoading, (newValue) => {
    if (newValue) {
        startCountdown();
    } else {
        stopCountdown();
    }
});

onUnmounted(() => {
    stopCountdown();
});
</script>

<template>
    <ClientLayout>
        <div class="rounded-lg border shadow-lg bg-white p-1 md:p-6 max-w-2xl mx-auto my-0 md:my-8">
            <!-- loading overlay -->
            <div v-if="isLoading" class="fixed inset-0 bg-white bg-opacity-95 flex items-center justify-center z-50">
                <div class="text-center">
                    <Loader2 class="w-12 h-12 animate-spin text-blue-500 mx-auto mb-4" />
                    <p class="text-lg font-medium text-gray-700">
                        Đang sắp xếp lịch trình cho bạn...<br>
                        Thời gian dự kiến: {{ estimatedFinishTime }} (Còn {{ countdownDisplay }})
                    </p>
                    <p class="text-sm text-gray-500 mt-2">
                        Vui lòng đợi khoảng {{ countdownDisplay }} nữa<br>
                        Bạn có thể rời màn hình này ngay và quay lại vào {{ estimatedFinishTime }}<br>
                        Kết quả sẽ được lưu ở trong phần lịch sử chuyến đi.
                    </p>
                    <a :href="route('history.index')" target="_blank" class="inline-block">
                        <Button size="sm" class="mt-3">
                            Lịch sử lịch trình
                        </Button>
                    </a>
                </div>
            </div>

            <div class="flex justify-around md:justify-between items-center w-full mb-3 md:p-0 p-3">
                <Button :class="'mr-3 disabled:opacity-50 cursor-pointer'" @click="prevStep"
                    :disabled="currentStep === 0">
                    Quay lại
                </Button>
                <div>
                    <Button v-if="currentStep === questions.length - 1" @click="openAskLocationModal"
                        :disabled="!isCurrentAnswerValid" :class="'cursor-pointer'">
                        Hoàn thành
                    </Button>
                    <Button v-else @click="nextStep" :disabled="!isCurrentAnswerValid" :class="'cursor-pointer'">
                        Tiếp theo
                    </Button>
                </div>
            </div>

            <div class="h-fit flex flex-col px-4 pb-4 mb-3">
                <div class="flex flex-col items-center text-center mb-3">
                    <!-- Error Display Card -->
                    <div v-if="hasErrors" role="alert" aria-live="assertive" class="w-full max-w-md">
                        <div class="bg-red-50 border border-red-200 text-red-800 p-4 rounded-lg shadow-sm">
                            <div class="flex items-center gap-2 mb-2">
                                <!-- Warning Icon -->
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round" class="h-5 w-5 text-red-600">
                                    <circle cx="12" cy="12" r="10" />
                                    <line x1="12" y1="8" x2="12" y2="12" />
                                    <line x1="12" y1="16" x2="12.01" y2="16" />
                                </svg>
                                <span class="font-semibold text-base md:text-lg text-left">
                                    Vui lòng kiểm tra những lỗi sau và sửa lại theo yêu cầu
                                </span>
                            </div>
                            <ul class="list-disc list-inside text-sm md:text-base text-left">
                                <li v-for="(error, key) in form.errors" :key="key">
                                    {{ error }}
                                </li>
                            </ul>
                        </div>
                    </div>
                    <!-- Step Indicator -->
                    <div class="text-sm md:text-base text-muted-foreground mt-3">
                        ({{ currentStep + 1 }}/{{ questions.length }})
                    </div>
                </div>
                <!-- <div class="flex items-center justify-between mb-4 md:mb-6">
                    <button @click="prevStep" :disabled="currentStep === 0" class="p-4 disabled:opacity-50">
                        <h6>BACK</h6>
                    </button>
                </div> -->

                <div class="flex-1 flex flex-col items-center justify-start md:justify-center">
                    <div class="w-full max-w-2xl px-4">
                        <h2 class="text-2xl md:text-3xl font-bold mb-6 md:mb-8 text-center">{{ currentQuestion.text }}
                        </h2>

                        <div class="space-y-4" :key="currentQuestion.id">
                            <input v-if="currentQuestion.type === 'text'" type="text"
                                v-model="form.answers[currentQuestion.id]" :placeholder="currentQuestion.placeholder"
                                class="p-4 border rounded-lg w-full" />
                            <div v-if="currentQuestion.type === 'text'"
                                class="flex flex-wrap gap-2 justify-center mt-3">
                                <span class="px-4 py-2 rounded-full transition-colors duration-200">Gợi ý: </span>
                                <span v-for="option in currentQuestion.options" :key="option.value"
                                    class="px-4 py-2 rounded-full border cursor-pointer transition-colors duration-200"
                                    :class="form.answers[currentQuestion.id] === option.label ? 'bg-blue-500 text-white border-blue-500' : 'hover:bg-gray-100 border-gray-300'"
                                    @click="form.answers[currentQuestion.id] = option.label">{{ option.label }}</span>
                            </div>

                            <input v-if="currentQuestion.type === 'number'" type="number"
                                v-model="form.answers[currentQuestion.id]" :placeholder="currentQuestion.placeholder"
                                class="p-4 border rounded-lg w-full" />
                            <div v-if="currentQuestion.type === 'number'"
                                class="flex flex-wrap gap-2 justify-center mt-3">
                                <span class="px-4 py-2 rounded-full transition-colors duration-200">Gợi ý: </span>
                                <span v-for="option in currentQuestion.options" :key="option.value"
                                    class="px-4 py-2 rounded-full border cursor-pointer transition-colors duration-200"
                                    :class="form.answers[currentQuestion.id] === option.label ? 'bg-blue-500 text-white border-blue-500' : 'hover:bg-gray-100 border-gray-300'"
                                    @click="form.answers[currentQuestion.id] = option.value">{{ option.label }}</span>
                            </div>

                            <div v-if="currentQuestion.type === 'radio'" class="space-y-4">
                                <label v-for="option in currentQuestion.options" :key="option.value"
                                    class="flex items-center p-4 border rounded-lg cursor-pointer"
                                    :class="{ 'bg-blue-100 border-blue-500': form.answers[currentQuestion.id] === option.value }">
                                    <input type="radio" :name="currentQuestion.id" :value="option.value"
                                        v-model="form.answers[currentQuestion.id]" class="hidden" />
                                    <span>{{ option.label }}</span>
                                    <input v-if="option.value === 'user_defined'" type="text"
                                        :value="form.answers[currentQuestion.id + '_custom_text']"
                                        @input="event => form.answers[currentQuestion.id + '_custom_text'] = event.target.value"
                                        @focus="handleClickOnOption(currentQuestion.id)"
                                        :placeholder="option.placeholder"
                                        class="ml-0 sm:ml-4 p-2 border rounded-md flex-grow focus:outline-none focus:ring-2 focus:ring-blue-500" />
                                </label>
                            </div>

                            <div v-if="currentQuestion.type === 'checkbox'" class="space-y-4">
                                <label v-for="option in currentQuestion.options" :key="option.value"
                                    class="flex items-center p-4 border rounded-lg cursor-pointer"
                                    :class="{ 'bg-blue-100 border-blue-500': form.answers[currentQuestion.id].includes(option.value) }">
                                    <input type="checkbox" :value="option.value"
                                        v-model="form.answers[currentQuestion.id]" class="mr-4"
                                        @change="handleSelectionChange(option, $event)" />
                                    <span>{{ option.label }}</span>
                                    <input v-if="option.value === 'user_defined'" type="text"
                                        :value="form.answers[currentQuestion.id + '_custom_text']"
                                        @input="event => form.answers[currentQuestion.id + '_custom_text'] = event.target.value"
                                        @focus="handleClickOnOption(currentQuestion.id)"
                                        :placeholder="option.placeholder"
                                        class="ml-0 sm:ml-4 p-2 border rounded-md flex-grow focus:outline-none focus:ring-2 focus:ring-blue-500" />
                                </label>
                            </div>

                            <div v-if="currentQuestion.type === 'date'" class="space-y-4">
                                <label class="flex flex-col items-start w-full">
                                    <input type="date" v-model="form.answers[currentQuestion.id]"
                                        :max="currentQuestion.id === 'end_date' && form.answers['start_date'] ? getMaxEndDate(form.answers['start_date']) : getMaxDate()"
                                        :min="currentQuestion.id === 'end_date' && form.answers['start_date'] ? form.answers['start_date'] : getToday()"
                                        class="p-4 border rounded-lg w-full" />
                                    <span class="text-sm text-gray-500 mt-2" v-if="currentQuestion.placeholder">
                                        {{ currentQuestion.placeholder }}
                                    </span>
                                </label>
                            </div>

                            <div v-if="currentQuestion.type === 'date-range'" class="space-y-4">
                                <div class="flex justify-center">
                                    <DatePicker v-model:value="form.answers[currentQuestion.id]" type="date" range
                                        :placeholder="currentQuestion.placeholder" lang="vi" format="DD/MM/YYYY"
                                        :editable="false"
                                        :disabled-date="(date) => date < new Date(new Date().setHours(0, 0, 0, 0))"
                                        @change="(val) => {
                                            if (Array.isArray(val) && val.length === 2 && val[0] && val[1] && currentQuestion.max) {
                                                const diff = (new Date(val[1]) - new Date(val[0])) / (1000 * 60 * 60 * 24) + 1;
                                                if (diff > currentQuestion.max) {
                                                    const start = new Date(val[0]);
                                                    const end = new Date(start);
                                                    end.setDate(start.getDate() + currentQuestion.max - 1);
                                                    form.answers[currentQuestion.id] = [val[0], end];
                                                }
                                            }
                                        }" />
                                </div>
                                <p v-if="currentQuestion.hint" class="text-sm text-gray-500 mt-2 text-center">
                                    {{ currentQuestion.hint }}
                                </p>
                            </div>

                            <div class="flex justify-center w-full">
                                <Button :class="'mt-6 mr-3 disabled:opacity-50 cursor-pointer'" @click="prevStep"
                                    :disabled="currentStep === 0">
                                    Quay lại
                                </Button>

                                <Button :class="'mt-6 cursor-pointer'" v-if="currentStep === questions.length - 1"
                                    @click="openAskLocationModal" :disabled="!isCurrentAnswerValid">
                                    Hoàn thành
                                </Button>

                                <Button :class="'mt-6 cursor-pointer'" v-if="currentStep !== questions.length - 1"
                                    @click="nextStep" :disabled="!isCurrentAnswerValid">
                                    Tiếp theo
                                </Button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <Dialog :open="isAskLocationModalOpen" @update:open="isAskLocationModalOpen = $event">
            <DialogContent>
                <form @submit.prevent="submitLocation">
                    <DialogHeader>
                        <DialogTitle>Cho phép truy cập vào địa điểm?</DialogTitle>
                        <DialogDescription>
                            <b>
                                Hãy bấm đồng ý hoặc cho phép (Allow) khi trình duyệt hỏi quyền truy cập vị trí.
                                Để có thể tìm kiếm nhà xe và nơi nghỉ ngơi hợp lý, chúng tôi cần bạn cung cấp vị trí
                                hiện tại.
                            </b>
                        </DialogDescription>
                    </DialogHeader>

                    <div class="my-4">
                        <Label for="prompt" class="sr-only"></Label>
                    </div>

                    <DialogFooter>
                        <Button type="button" variant="secondary" @click="proceedWithoutCurrentLocation()">Tôi không cần
                            tìm gì cả</Button>
                        <div class="my-1">
                            <Label for="prompt" class="sr-only"></Label>
                        </div>
                        <Button type="button" @click="submitLocation()" :disabled="isFetchingLocation.valueOf()">
                            <Loader2 v-if="isFetchingLocation.valueOf()" class="w-4 h-4 mr-1 animate-spin" />
                            Tôi muốn tìm nhà xe, khách sạn
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    </ClientLayout>
</template>
