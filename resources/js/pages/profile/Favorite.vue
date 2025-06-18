<script setup>
import ClientLayout from '@/layouts/ClientAppLayout.vue';
import Button from '@/components/ui/button/Button.vue';
import { Link } from '@inertiajs/vue3';
import { computed, reactive } from 'vue';
import { Calendar, MapPin, Users, DollarSign, Heart, Star, Utensils, StickyNote, Navigation } from 'lucide-vue-next';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    foodItems: Object,
    sightseeingItems: Object,
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

console.log(props.sightseeingItems, props.foodItems);
const favoriteItems = computed(() => {

    const mappedFoods = props.foodItems.map(item => {
        return {
            ...item.history_food,
            item_type: 'food',
            favorited_at: item.created_at
        };
    });

    const mappedSightseeings = props.sightseeingItems.map(item => {
        return {
            ...item.history_sightseeing,
            item_type: 'sightseeing',
            favorited_at: item.created_at
        };
    });

    const combinedItems = [...mappedFoods, ...mappedSightseeings];
    combinedItems.sort((a, b) => {
        return new Date(b.favorited_at) - new Date(a.favorited_at);
    });

    return combinedItems;
});
const formatDate = (dateString) => {
    const date = new Date(dateString);
    return date.toLocaleDateString('vi-VN', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};
const goToGoogleMap = (location) => {
    const encodedLocation = encodeURIComponent(location);
    window.open(`https://www.google.com/maps/search/?api=1&query=${encodedLocation}`, '_blank');
}
</script>

<template>
    <ClientLayout>
        <div class="flex flex-col w-full h-fit gap-6 p-10">

            <div class="flex flex-col items-start gap-2">
                <h1 class="text-4xl font-semibold">Địa điểm yêu thích</h1>
            </div>

            <div class="w-full border-b border-gray-300"></div>

            <!-- favorite items here -->
            <div class="grid gap-6">
                <!-- start item  -->
                <div v-for="item in favoriteItems" :key="item.id"
                    class="bg-white rounded-lg border border-gray-200 shadow-sm hover:shadow-md transition-shadow duration-200 p-6">
                    <!-- Header -->
                    <div class="flex justify-between items-start mb-4">
                        <div class="flex-1">
                            <div class="flex items-center gap-2 mb-2">
                                <h3 class="text-xl font-semibold text-gray-900">
                                    {{ item.name }}
                                </h3>
                                <Heart class="w-5 h-5 text-red-500 fill-current" />
                            </div>
                            <p class="text-gray-600 text-sm leading-relaxed mb-3">
                                {{ item.description }}
                            </p>
                        </div>
                    </div>

                    <!-- Details grid -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <!-- Address -->
                        <div class="flex items-start gap-2 text-sm text-gray-600">
                            <MapPin class="w-4 h-4 text-red-500 mt-0.5 flex-shrink-0" />
                            <div>
                                <div class="font-medium text-gray-900">Địa chỉ</div>
                                <div class="text-gray-600">{{ item.address }}</div>
                            </div>
                        </div>

                        <!-- Food type -->
                        <div v-if="item.item_type==='food'" class="flex items-start gap-2 text-sm text-gray-600">
                            <Utensils class="w-4 h-4 text-orange-500 mt-0.5 flex-shrink-0" />
                            <div>
                                <div class="font-medium text-gray-900">Loại hình</div>
                                <div class="text-gray-600">{{ item.food_type }}</div>
                            </div>
                        </div>

                        <!-- Sightseeing type -->
                        <div v-if="item.item_type==='sightseeing'" class="flex items-start gap-2 text-sm text-gray-600">
                            <Star class="w-4 h-4 text-orange-500 mt-0.5 flex-shrink-0" />
                            <div>
                                <div class="font-medium text-gray-900">Loại hình</div>
                                <div class="text-gray-600">Khu thăm quan</div>
                            </div>
                        </div>

                        <!-- Created date -->
                        <div class="flex items-start gap-2 text-sm text-gray-600">
                            <Calendar class="w-4 h-4 text-blue-500 mt-0.5 flex-shrink-0" />
                            <div>
                                <div class="font-medium text-gray-900">Thêm vào lúc</div>
                                <div class="text-gray-600">{{ formatDate(item.created_at) }}</div>
                            </div>
                        </div>

                        <!-- Location coordinates -->
                        <div v-if="item.latitude && item.longitude"
                            class="flex items-start gap-2 text-sm text-gray-600">
                            <Navigation class="w-4 h-4 text-green-500 mt-0.5 flex-shrink-0" />
                            <div>
                                <div class="font-medium text-gray-900">Tọa độ</div>
                                <div class="text-gray-600">{{ item.latitude }}, {{ item.longitude }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- Note section -->
                    <div v-if="item.note" class="mb-4">
                        <div class="flex items-start gap-2 text-sm">
                            <StickyNote class="w-4 h-4 text-yellow-500 mt-0.5 flex-shrink-0" />
                            <div>
                                <div class="font-medium text-gray-900 mb-1">Ghi chú</div>
                                <div class="text-gray-600 bg-yellow-50 p-3 rounded-lg border border-yellow-200">
                                    {{ item.note }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex justify-between items-center pt-4 border-t border-gray-100">
                        <div class="text-xs text-gray-500">
                            ID chuyến đi: #{{ item.history_item_id }}
                        </div>
                        <div class="flex gap-3">
                            <Button v-if="item.latitude && item.longitude" variant="outline" size="sm"
                                @click="goToGoogleMap(item.name + ' ' + item.address)">
                                <MapPin class="w-4 h-4 mr-1" />
                                Xem bản đồ
                            </Button>
                            <template v-if="item.item_type==='food'">
                                <Button size="sm" @click="toggleFavoriteFood(item.id)" variant="outline" class="flex items-center gap-2"
                                    :disabled="loadingStates[item.id]"
                                    :class="{ 'text-white border-pink-300 bg-pink-400': item.user_favorite,
                                            'text-gray-600': !item.user_favorite }">
                                    <Heart class="w-4 h-4" :fill="item.user_favorite ? 'currentColor' : 'none'" />
                                    {{ item.user_favorite ? 'Đã yêu thích' : 'Yêu thích' }}
                                </Button>
                            </template>
                            <template v-if="item.item_type==='sightseeing'">
                                <Button size="sm" @click="toggleFavoriteSightseeing(item.id)" variant="outline" class="flex items-center gap-2"
                                    :disabled="loadingStates[item.id]"
                                    :class="{ 'text-white border-pink-300 bg-pink-400': item.user_favorite,
                                            'text-gray-600': !item.user_favorite }">
                                    <Heart class="w-4 h-4" :fill="item.user_favorite ? 'currentColor' : 'none'" />
                                    {{ item.user_favorite ? 'Đã yêu thích' : 'Yêu thích' }}
                                </Button>
                            </template>
                            <!-- <Button variant="outline" size="sm">
                                Chỉnh sửa
                            </Button>
                            <Button size="sm">
                                Chia sẻ
                            </Button> -->
                        </div>
                    </div>
                </div>
                <!-- end of an item  -->

                <!-- Empty state -->
                <div v-if="favoriteItems.length === 0" class="text-center py-12">
                    <div class="text-gray-400 mb-4">
                        <Heart class="w-16 h-16 mx-auto" />
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Chưa có địa điểm yêu thích</h3>
                    <p class="text-gray-500 mb-6">Bạn chưa lưu địa điểm nào vào danh sách yêu thích. Hãy khám phá và lưu
                        lại những nơi bạn thích!</p>
                    <Link :href="route('history.index')" class="inline-block">
                        <Button>
                            Khám phá địa điểm
                        </Button>
                    </Link>
                </div>
            </div>

            <div class="flex flex-col items-start gap-1">
                <p class="text-gray-500 text-sm">@ 2024-2025</p>
                <p class="text-gray-500 text-sm">Powered by Google Gemini</p>
            </div>
        </div>
    </ClientLayout>
</template>
