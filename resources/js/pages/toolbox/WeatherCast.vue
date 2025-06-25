<script setup>
import ClientLayout from '@/layouts/ClientAppLayout.vue';
import Button from '@/components/ui/button/Button.vue';
import { router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import {
    Cloud,
    CloudRain,
    Sun,
    CloudSnow,
    Zap,
    CloudDrizzle,
    Cloudy,
    MapPin,
    Calendar,
    Search,
    Loader2
} from 'lucide-vue-next';
import { showToast } from '@/composables/useToasts';

const formatDateForInput = (date) => {
    return date.toISOString().split('T')[0];
};

const today = new Date();
const nextWeek = new Date();

const props = defineProps({
    summary_location: {
        type: String,
        default: ''
    },
    location: {
        type: String,
        default: 'Hai Phong'
    },
    data: {
        type: Array,
        default: () => []
    }
})

const form = ref({
    location: props.location,
    start_date: formatDateForInput(today),
    end_date: formatDateForInput(nextWeek),
})

const isLoading = ref(false)
const searchResults = ref([])
const isSearching = ref(false)
const showSuggestions = ref(false)
const searchTimeout = ref(null)

const weatherData = ref((props.data && props.data.length > 0) ? props.data : [
    {
        "date": "",
        "temperature": 0,
        "weather": ""
    }
]);

const searchLocations = (query) => {
    return;
    if (!query || query.length < 2) {
        searchResults.value = []
        showSuggestions.value = false
        return
    }
    isSearching.value = true
    router.get(route('search-locations'), { q: query }, {
        preserveState: true,
        preserveScroll: true,
        only: ['locations'],
        onSuccess: (page) => {
            searchResults.value = page.props.locations || []
            showSuggestions.value = true
            isSearching.value = false
        },
        onError: () => {
            searchResults.value = []
            showSuggestions.value = false
            isSearching.value = false
        }
    })
}

const debouncedSearch = (query) => {
    clearTimeout(searchTimeout.value)
    searchTimeout.value = setTimeout(() => {
        searchLocations(query)
    }, 600)
}

watch(() => form.value.location, (newValue) => {
    debouncedSearch(newValue)
})

watch([() => form.value.start_date, () => form.value.end_date], ([newStart, newEnd]) => {
    if (newStart && newEnd) {
        const startDate = new Date(newStart);
        const endDate = new Date(newEnd);

        if (startDate > endDate) {
            showToast('Ngày bắt đầu không thể sau ngày kết thúc. Đã tự động sửa lại.', 'error');
            const todayDate = new Date();
            const tomorrowDate = new Date();
            tomorrowDate.setDate(todayDate.getDate() + 1);

            form.value.start_date = formatDateForInput(todayDate);
            form.value.end_date = formatDateForInput(tomorrowDate);
        }
    }
});

const selectLocation = (location) => {
    form.value.location = location.name || location
    showSuggestions.value = false
    searchResults.value = []
}

const hideSuggestions = () => {
    setTimeout(() => {
        showSuggestions.value = false
    }, 200)
}

const submitForm = () => {
    if (!form.value.location || !form.value.start_date || !form.value.end_date) {
        alert('Vui lòng điền đầy đủ thông tin')
        return
    }
    isLoading.value = true
    router.get(route('get-weather'), {
        location: form.value.location,
        start_date: form.value.start_date,
        end_date: form.value.end_date
    }, {
        onFinish: () => {
            isLoading.value = false
        }
    })
}

const getDayLabel = (dateString) => {
    const date = new Date(dateString)
    const days = ['CN', 'Th 2', 'Th 3', 'Th 4', 'Th 5', 'Th 6', 'Th 7']
    return days[date.getDay()]
}

const formatDate = (dateString) => {
    if (!dateString) return ''
    const date = new Date(dateString)
    return `${date.getDate().toString().padStart(2, '0')}/${(date.getMonth() + 1).toString().padStart(2, '0')}/${date.getFullYear()}`
}

const getWeatherIcon = (weather) => {
    const weatherLower = weather.toLowerCase()

    if (weatherLower.includes('nắng')) {
        return Sun
    } else if (weatherLower.includes('mưa to') || weatherLower.includes('mưa lớn')) {
        return CloudRain
    } else if (weatherLower.includes('mưa rào') || weatherLower.includes('mưa nhẹ')) {
        return CloudDrizzle
    } else if (weatherLower.includes('mưa vừa') || weatherLower.includes('mưa')) {
        return CloudRain
    } else if (weatherLower.includes('có mây') || weatherLower.includes('ít mây')) {
        return Cloudy
    } else if (weatherLower.includes('tuyết')) {
        return CloudSnow
    } else if (weatherLower.includes('sấm') || weatherLower.includes('giông')) {
        return Zap
    } else {
        return Cloud
    }
}


nextWeek.setDate(today.getDate() + 7);
</script>

<style scoped>
.weather-card {
    transition: all 0.2s ease-in-out;
}

.weather-card:hover {
    transform: translateY(-2px);
}

.suggestions-dropdown {
    max-height: 200px;
    overflow-y: auto;
}

</style>

<template>
    <ClientLayout>
        <div class="flex flex-col items-center p-5">
            <!-- weather forecast form -->
            <div class="w-full mx-auto bg-white rounded-lg shadow-lg p-6 mb-6">
                <h2 class="text-xl font-bold text-gray-900 mb-4">Tìm kiếm dự báo thời tiết</h2>

                <form @submit.prevent="submitForm" class="space-y-4">
                    <div class="relative">
                        <label for="location" class="block text-sm font-medium text-gray-700 mb-2">
                            <MapPin class="w-4 h-4 inline mr-1" />
                            Địa điểm
                        </label>
                        <div class="relative">
                            <input id="location" v-model="form.location" type="text" placeholder="Nhập tên thành phố..."
                                class="w-full px-3 py-2 pr-10 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                autocomplete="off" @blur="hideSuggestions" required />

                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                <Loader2 v-if="isSearching" class="w-4 h-4 text-gray-400 animate-spin" />
                                <Search v-else class="w-4 h-4 text-gray-400" />
                            </div>

                            <div v-if="showSuggestions && searchResults.length > 0"
                                class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg suggestions-dropdown">
                                <ul class="py-1">
                                    <li v-for="(location, index) in searchResults" :key="index"
                                        @click="selectLocation(location)"
                                        class="px-3 py-2 hover:bg-gray-100 cursor-pointer flex items-center">
                                        <MapPin class="w-4 h-4 text-gray-400 mr-2" />
                                        <div>
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ location.name || location }}
                                            </div>
                                            <div v-if="location.country" class="text-xs text-gray-500">
                                                {{ location.country }}
                                            </div>
                                        </div>
                                    </li>
                                </ul>
                            </div>

                            <div v-if="showSuggestions && searchResults.length === 0 && !isSearching && form.location.length >= 2"
                                class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg">
                                <div class="px-3 py-2 text-sm text-gray-500">
                                    Không tìm thấy địa điểm nào
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="start_date" class="block text-sm font-medium text-gray-700 mb-2">
                                <Calendar class="w-4 h-4 inline mr-1" />
                                Ngày bắt đầu
                            </label>
                            <input id="start_date" v-model="form.start_date" type="date"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                required />
                        </div>

                        <div>
                            <label for="end_date" class="block text-sm font-medium text-gray-700 mb-2">
                                <Calendar class="w-4 h-4 inline mr-1" />
                                Ngày kết thúc
                            </label>
                            <input id="end_date" v-model="form.end_date" type="date"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                required />
                        </div>
                    </div>

                    <div class="flex justify-center pt-2">
                        <Button type="submit" :disabled="isLoading"
                            class="px-6 py-2 bg-blue-600 text-white font-medium rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                            <span v-if="isLoading">Đang tải...</span>
                            <span v-else>Lấy dự báo thời tiết</span>
                        </Button>
                    </div>
                </form>
            </div>

            <div class="w-full mx-auto bg-white rounded-lg shadow-lg p-6">
                <div class="mb-6">
                    <p class="text-sm text-gray-600 mb-1">Dự báo thời tiết</p>
                    <h1 class="text-2xl font-bold text-gray-900 mb-2">{{ summary_location }}</h1>
                    <p class="text-sm text-gray-500">
                        Từ {{ formatDate(weatherData[0]?.date) }} đến {{ formatDate(weatherData[weatherData.length -
                            1]?.date) }}
                        ({{ weatherData.length }} ngày)
                    </p>
                </div>

                <div class="grid grid-cols-4 gap-3">
                    <div v-for="(day, index) in weatherData" :key="index"
                        class="bg-gray-50 rounded-lg p-4 text-center hover:bg-gray-100 transition-colors">
                        <div class="text-xs font-medium text-gray-600 mb-3">
                            {{ getDayLabel(day.date) }}
                        </div>

                        <div class="flex justify-center mb-3">
                            <component :is="getWeatherIcon(day.weather)" class="w-8 h-8 text-gray-700" />
                        </div>

                        <div class="text-sm">
                            <span class="font-medium text-gray-900">{{ Math.round(day.temperature) }}°</span>
                            <span class="text-gray-500 ml-1">{{ Math.round(day.temperature) }}°C</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </ClientLayout>
</template>
