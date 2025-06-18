<script setup>
import ClientLayout from '@/layouts/ClientAppLayout.vue';
import Button from '@/components/ui/button/Button.vue';
import { reactive, computed } from 'vue';
import { Heart } from 'lucide-vue-next';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    data: Object
});
const history = computed(() => {
    if (!props.data || !props.data.items) {
        return {};
    }

    return props.data.items.reduce((accumulator, currentItem) => {
        if (!accumulator[dayKey]) {
            accumulator[dayKey] = [];
        }

        accumulator[dayKey].push(currentItem);
        return accumulator;
    }, {});
});

const goToGoogleMap = (location) => {
    const encodedLocation = encodeURIComponent(location);
    window.open(`https://www.google.com/maps/search/?api=1&query=${encodedLocation}`, '_blank');
}

function getDayTimeVietnamese(dayTimeEnglish) {
    const translations = {
        'morning': 'Buổi Sáng',
        'lunch': 'Buổi Trưa',
        'afternoon': 'Buổi Chiều',
        'evening': 'Buổi Tối'
    }
    return translations[dayTimeEnglish] || dayTimeEnglish;
}

const totalDays = computed(() => {
    return Object.keys(history.value).length;
});

const loadingStates = reactive({});

const toggleFavoriteSightseeing = (sightseeingId) => {
    loadingStates[sightseeingId] = true;

    router.post(route('sightseeing.toggle-favorite', { sightseeing: sightseeingId }), {}, {
        preserveScroll: true,
        onFinish: () => {
            loadingStates[sightseeingId] = false;
        }
    });
};

const toggleFavoriteFood = (foodId) => {
    loadingStates[foodId] = true;

    router.post(route('food.toggle-favorite', { food: foodId }), {}, {
        preserveScroll: true,
        onFinish: () => {
            loadingStates[foodId] = false;
        }
    });
};
</script>

<template>
    <ClientLayout>
        <div class="flex flex-col w-full h-fit gap-6 p-10">

            <div class="flex flex-col items-start gap-2">
                <p class="font-semibold">Kết quả gợi ý cho chuyến đi của bạn</p>
                <h1 class="text-4xl font-semibold">{{ data.title }}</h1>
                <p class="text-gray-500">
                    {{ data.company }} • {{ data.interests }} • Đi {{ totalDays }} ngày</p>
                <div class="pt-2"></div>
                <p class="">
                    {{ data.description }}
                </p>
            </div>

            <div class="w-full border-b border-gray-300"></div>

            <!-- days loop here -->
            <div v-for="(itemsInDay, dayLabel) in history" :key="dayLabel">

                <div class="flex flex-col w-full h-fit gap-1 mb-3">
                    <div>
                        <p class="font-semibold">{{ dayLabel }}</p>
                    </div>

                    <div v-for="item in itemsInDay" class="flex flex-col gap-2">
                        <div class="flex flex-col gap-0">
                            <h1 class="text-2xl font-semibold">{{ getDayTimeVietnamese(item.day_time) }}</h1>
                            <div class="flex items-center gap-0">
                                <!-- <p class=" text-gray-950 mb-6" style="font-size: 3rem !important;">psychology</p> -->
                                <p class="material-icons" style="font-size: 1rem !important;">map</p>
                                <p class="material-icons" style="font-size: 1rem !important;">restaurant</p>
                                <p class="font-semibold pl-1">Địa điểm gợi ý</p>
                            </div>
                        </div>

                        <div
                            class="flex flex-nowrap gap-6 overflow-x-auto w-full min-w-0 scrollbar-hide overflow-visible p-3">
                            <div v-for="foodItem in item.food" :key="foodItem.id"
                                class="flex flex-col items-start justify-start rounded-3xl shadow-lg p-6 bg-white flex-shrink-0 w-[280px] md:w-auto md:flex-1">
                                <h3 class="font-bold text-lg mb-1">🍜 {{ foodItem.name }}</h3>
                                <p class="text-gray-500 text-sm mb-4">{{ foodItem.food_type }} • {{ foodItem.address }}
                                </p>
                                <p class="text-gray-700 text-sm leading-relaxed mb-6">{{ foodItem.description }}</p>
                                <!-- <p class="text-gray-700 text-sm leading-relaxed mb-6">{{ foodItem }}</p> -->
                                <div class="flex gap-2 mt-auto w-full">
                                    <Button @click="toggleFavoriteFood(foodItem.id)" variant="outline"
                                        class="flex items-center gap-2" :disabled="loadingStates[foodItem.id]" :class="{
                                            'text-white border-pink-300 bg-pink-400': foodItem.user_favorite,
                                            'text-gray-600': !foodItem.user_favorite
                                        }">
                                        <Heart class="w-4 h-4"
                                            :fill="foodItem.user_favorite ? 'currentColor' : 'none'" />
                                        {{ foodItem.user_favorite ? 'Đã yêu thích' : 'Yêu thích' }}
                                    </Button>
                                    <Button @click="goToGoogleMap(foodItem.name + ' ' + foodItem.address)"
                                        class="flex-1 cursor-pointer bg-black text-white py-3 rounded-lg text-sm font-semibold flex items-center justify-center gap-2 hover:bg-gray-800 transition-colors">
                                        Đến ngay <p aria-hidden="true">→</p>
                                    </Button>
                                </div>
                            </div>
                            <div v-for="sightseeingItem in item.sightseeing" :key="sightseeingItem.id"
                                class="flex flex-col items-start justify-start rounded-3xl shadow-lg p-6 bg-white flex-shrink-0 w-[280px] md:w-auto md:flex-1">
                                <h3 class="font-bold text-lg mb-1">🏛️ {{ sightseeingItem.name }}</h3>
                                <p class="text-gray-500 text-sm mb-4">Thăm quan • {{ sightseeingItem.address }}</p>
                                <p class="text-gray-700 text-sm leading-relaxed mb-6">{{ sightseeingItem.description }}
                                </p>
                                <!-- <p class="text-gray-700 text-sm leading-relaxed mb-6">{{ sightseeingItem }}</p> -->
                                <div class="flex gap-2 mt-auto w-full">
                                    <Button @click="toggleFavoriteSightseeing(sightseeingItem.id)" variant="outline"
                                        class="flex items-center gap-2" :disabled="loadingStates[sightseeingItem.id]"
                                        :class="{
                                            'text-white border-pink-300 bg-pink-400': sightseeingItem.user_favorite,
                                            'text-gray-600': !sightseeingItem.user_favorite
                                        }">
                                        <Heart class="w-4 h-4"
                                            :fill="sightseeingItem.user_favorite ? 'currentColor' : 'none'" />
                                        {{ sightseeingItem.user_favorite ? 'Đã yêu thích' : 'Yêu thích' }}
                                    </Button>
                                    <Button @click="goToGoogleMap(sightseeingItem.name + ' ' + sightseeingItem.address)"
                                        class="flex-1 cursor-pointer bg-black text-white py-3 rounded-lg text-sm font-semibold flex items-center justify-center gap-2 hover:bg-gray-800 transition-colors">
                                        Đến ngay <p aria-hidden="true">→</p>
                                    </Button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="w-full border-b border-gray-300"></div>
            </div>

            <div class="flex flex-col items-start gap-1">
                <p class="text-gray-500 text-sm">@ 2024-2025</p>
                <p class="text-gray-500 text-sm">Powered by Google Gemini</p>
            </div>
        </div>
    </ClientLayout>
</template>
