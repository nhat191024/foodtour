<script setup>
import ClientLayout from '@/layouts/ClientAppLayout.vue';
import Button from '@/components/ui/button/Button.vue';
import { ref, computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { Loader2 } from 'lucide-vue-next';

const props = defineProps({
    questions: Array,
});

const currentStep = ref(0);

const form = useForm({
    answers: {},
});

const isLoading = ref(false);

const isCurrentAnswerValid = computed(() => {
    const currentAnswer = form.answers[currentQuestion.value.id];

    if (!currentAnswer) return false;

    switch (currentQuestion.value.type) {
        case 'text':
            return currentAnswer.length >= 3;
        case 'checkbox':
            if (currentAnswer.length === 0) return false;
            if (currentAnswer.includes('user_defined')) {
                const customTextKey = currentQuestion.value.id + '_custom_text';
                const customText = form.answers[customTextKey];
                if (!customText || customText.trim() === '') {
                    return false;
                }
            }
            return true;
        case 'radio':
            if (currentAnswer === null || currentAnswer === '') return false;
            if (currentAnswer === 'user_defined') {
                const customTextKey = currentQuestion.value.id + '_custom_text';
                const customText = form.answers[customTextKey];
                if (!customText || customText.trim() === '') {
                    return false;
                }
            }
            return true;
        case 'number':
            return currentAnswer !== null && currentAnswer !== '';
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
</script>

<template>
    <ClientLayout>
        <!-- loading overlay -->
        <div v-if="isLoading" class="fixed inset-0 bg-white bg-opacity-95 flex items-center justify-center z-50">
            <div class="text-center">
                <Loader2 class="w-12 h-12 animate-spin text-blue-500 mx-auto mb-4" />
                <p class="text-lg font-medium text-gray-700">Đang sắp xếp lịch trình cho bạn...</p>
                <p class="text-sm text-gray-500 mt-2">Vui lòng đợi trong giây lát. Bạn có thể rời màn hình này và quay lại vào lúc sau. Kết quả sẽ được lưu ở trong phần lịch sử chuyến đi.</p>
                <a :href="route('history.index')" target="_blank" class="inline-block">
                    <Button size="sm" class="mt-3">
                        Lịch sử lịch trình
                    </Button>
                </a>
            </div>
        </div>

        <div class="h-fit flex flex-col p-4 mb-3">
            <div class="flex items-center justify-between mb-4 md:mb-6">
                <!-- <button @click="prevStep" :disabled="currentStep === 0" class="p-4 disabled:opacity-50">
                    <h6>BACK</h6>
                </button> -->
            </div>

            <div class="flex-1 text-center mb-2">
                <div class="text-sm md:text-base mb-2">
                    ({{ currentStep + 1 }}/{{ questions.length }})
                </div>
                <div v-if="Object.keys(form.errors).length > 0" class="text-red-500 font-bold text-sm md:text-base">
                    Vui lòng kiểm tra những lỗi sau và sửa lại theo yêu cầu:
                </div>
                <div v-if="form.errors" class="text-red-500 font-bold text-sm">
                    <div v-for="(error, key) in form.errors" :key="key">
                        {{ error }}
                    </div>
                </div>
            </div>

            <div class="w-16">
                <!-- <button @click="nextStep"
                        v-if="currentStep < questions.length - 1"
                        :disabled="!isCurrentAnswerValid"
                        class="p-4"
                        :class="{'opacity-50 cursor-not-allowed': !isCurrentAnswerValid}">
                    <h6>NEXT</h6>
                </button>
                <button @click="submitSurvey"
                        v-if="currentStep === questions.length - 1"
                        :disabled="!isCurrentAnswerValid"
                        class="p-4 rounded"
                        :class="{'opacity-50 cursor-not-allowed': !isCurrentAnswerValid}">
                    <h6>SUBMIT</h6>
                </button> -->
            </div>

            <div class="flex-1 flex flex-col items-center justify-start md:justify-center">
                <div class="w-full max-w-2xl px-4">
                    <h2 class="text-2xl md:text-3xl font-bold mb-6 md:mb-8 text-center">{{ currentQuestion.text }}</h2>

                    <div class="space-y-4" :key="currentQuestion.id">
                        <input v-if="currentQuestion.type === 'text'" type="text"
                            v-model="form.answers[currentQuestion.id]" :placeholder="currentQuestion.placeholder"
                            class="p-4 border rounded-lg w-full" />
                        <div v-if="currentQuestion.type === 'text'" class="flex flex-wrap gap-2 justify-center mt-3">
                            <span class="px-4 py-2 rounded-full transition-colors duration-200">Gợi ý: </span>
                            <span v-for="option in currentQuestion.options" :key="option.value"
                                class="px-4 py-2 rounded-full border cursor-pointer transition-colors duration-200"
                                :class="form.answers[currentQuestion.id] === option.label ? 'bg-blue-500 text-white border-blue-500' : 'hover:bg-gray-100 border-gray-300'"
                                @click="form.answers[currentQuestion.id] = option.label">{{ option.label }}</span>
                        </div>

                        <input v-if="currentQuestion.type === 'number'" type="number"
                            v-model="form.answers[currentQuestion.id]" :placeholder="currentQuestion.placeholder"
                            class="p-4 border rounded-lg w-full" />
                        <div v-if="currentQuestion.type === 'number'" class="flex flex-wrap gap-2 justify-center mt-3">
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
                                    class="ml-0 sm:ml-4 p-2 border rounded-md flex-grow focus:outline-none focus:ring-2 focus:ring-blue-500"

                                    />
                            </label>
                        </div>

                        <div v-if="currentQuestion.type === 'checkbox'" class="space-y-4">
                            <label v-for="option in currentQuestion.options" :key="option.value"
                                class="flex items-center p-4 border rounded-lg cursor-pointer"
                                :class="{ 'bg-blue-100 border-blue-500': form.answers[currentQuestion.id].includes(option.value) }">
                                <input type="checkbox" :value="option.value" v-model="form.answers[currentQuestion.id]"
                                    class="mr-4" @change="handleSelectionChange(option, $event)" />
                                <span>{{ option.label }}</span>
                                <input v-if="option.value === 'user_defined'" type="text"
                                    :value="form.answers[currentQuestion.id + '_custom_text']"
                                    @input="event => form.answers[currentQuestion.id + '_custom_text'] = event.target.value"
                                    @focus="handleClickOnOption(currentQuestion.id)"
                                    :placeholder="option.placeholder"
                                    class="ml-0 sm:ml-4 p-2 border rounded-md flex-grow focus:outline-none focus:ring-2 focus:ring-blue-500"

                                    />
                            </label>
                        </div>

                        <div class="flex justify-center w-full">
                            <Button :class="'mt-6 mr-3 disabled:opacity-50 cursor-pointer'"
                                    @click="prevStep" :disabled="currentStep === 0">
                                Quay lại
                            </Button>

                            <Button :class="'mt-6 cursor-pointer'"
                                    v-if="currentStep === questions.length - 1"
                                    @click="submitSurvey"
                                    :disabled="!isCurrentAnswerValid">
                                Hoàn thành
                            </Button>

                            <Button :class="'mt-6 cursor-pointer'"
                                    v-if="currentStep !== questions.length - 1"
                                    @click="nextStep"
                                    :disabled="!isCurrentAnswerValid">
                                Tiếp theo
                            </Button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </ClientLayout>
</template>
