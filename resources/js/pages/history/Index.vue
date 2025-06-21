<script setup>
import ClientLayout from '@/layouts/ClientAppLayout.vue';
import Button from '@/components/ui/button/Button.vue';
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import { Calendar, MapPin, Users, DollarSign, Heart } from 'lucide-vue-next';

const props = defineProps({
    data: Object
});

const formatDate = (dateString) => {
    const date = new Date(dateString);
    return date.toLocaleDateString('vi-VN', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric'
});
};

const getTripDuration = (startDate, endDate) => {
    const start = new Date(startDate);
    const end = new Date(endDate);
    const diffTime = Math.abs(end - start);
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
    return diffDays === 0 ? 1 : diffDays + 1;
};

const formatCost = (cost) => {
    return cost === "0" ? "Miễn phí" : `${parseInt(cost).toLocaleString('vi-VN')} VNĐ`;
};

const calculateTotalCost = (item) => {
    if (!item.trip_costs || !Array.isArray(item.trip_costs)) return 0;
    return item.trip_costs.reduce((total, cost) => total + (parseInt(cost.value) || 0), 0);
}

const historyItems = computed(() => props.data);
</script>

<template>
    <ClientLayout>
        <div class="flex flex-col w-full h-fit gap-6 p-10">
            <div class="flex flex-col items-start gap-2">
                <h1 class="text-4xl font-semibold">Lịch sử chuyến đi</h1>
            </div>

            <div class="w-full border-b border-gray-300"></div>

            <!-- History items list -->
            <div class="grid gap-6">
                <div
                    v-for="item in historyItems"
                    :key="item.id"
                    class="bg-white rounded-lg border border-gray-200 shadow-sm hover:shadow-md transition-shadow duration-200 p-6"
                >
                    <!-- Header -->
                    <div class="flex flex-col sm:flex-row justify-between items-start mb-4">
                        <div class="flex-1 mb-3 sm:mb-0">
                            <h3 class="text-xl font-semibold text-gray-900 mb-2">
                                {{ item.title }}
                            </h3>
                            <p class="text-gray-600 text-sm leading-relaxed">
                                Mô tả: {{ item.description.length > 150 ? item.description.slice(0, 150) + '...' : item.description }}
                            </p>
                        </div>
                        <div class="w-full sm:w-auto sm:ml-4 text-left">
                            <div class="text-lg font-bold text-green-600">
                                {{ formatCost(calculateTotalCost(item)) }}
                            </div>
                        </div>
                    </div>

                    <!-- Trip details -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
                        <!-- Dates -->
                        <div class="flex items-center gap-2 text-sm text-gray-600">
                            <Calendar class="w-4 h-4 text-blue-500" />
                            <div>
                                <div class="font-medium">{{ formatDate(item.start_date) }}</div>
                                <div class="text-xs">đến {{ formatDate(item.end_date) }}</div>
                            </div>
                        </div>

                        <!-- Duration -->
                        <div class="flex items-center gap-2 text-sm text-gray-600">
                            <MapPin class="w-4 h-4 text-red-500" />
                            <div>
                                <div class="font-medium">{{ getTripDuration(item.start_date, item.end_date) }} ngày</div>
                                <div class="text-xs">Thời gian</div>
                            </div>
                        </div>

                        <!-- Company type -->
                        <div class="flex items-center gap-2 text-sm text-gray-600">
                            <Users class="w-4 h-4 text-purple-500" />
                            <div>
                                <div class="font-medium">{{ item.company }}</div>
                                <div class="text-xs">Loại hình</div>
                            </div>
                        </div>

                        <!-- Created date -->
                        <div class="flex items-center gap-2 text-sm text-gray-600">
                            <Heart class="w-4 h-4 text-pink-500" />
                            <div>
                                <div class="font-medium">{{ new Date(item.created_at).toLocaleString('vi-VN', {
                                    day: '2-digit',
                                    month: '2-digit',
                                    year: 'numeric',
                                    hour: '2-digit',
                                    minute: '2-digit'
                                }) }}</div>
                                <div class="text-xs">Tạo lúc</div>
                            </div>
                        </div>
                    </div>

                    <!-- Interests -->
                    <div class="mb-4">
                        <div class="text-sm font-medium text-gray-700 mb-2">Sở thích:</div>
                        <div class="flex flex-wrap gap-2">
                            <span
                                v-for="interest in item.interests.split(', ')"
                                :key="interest"
                                class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800"
                            >
                                {{ interest.trim() }}
                            </span>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
                        <Link :href="route('calculator', { id: item.id })" class="inline-block">
                            <Button variant="outline" size="sm">
                                Tính chi tiêu
                            </Button>
                        </Link>
                        <Link :href="route('survey.result', { id: item.id })" class="inline-block">
                            <Button size="sm">
                                Xem chi tiết
                            </Button>
                        </Link>
                    </div>
                </div>

                <!-- Empty state -->
                <div v-if="historyItems.length === 0" class="text-center py-12">
                    <div class="text-gray-400 mb-4">
                        <MapPin class="w-16 h-16 mx-auto" />
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Chưa có chuyến đi nào</h3>
                    <p class="text-gray-500 mb-6">Bạn chưa tạo lịch trình du lịch nào. Hãy bắt đầu lên kế hoạch cho chuyến đi đầu tiên!</p>
                    <Link :href="route('survey.start')" class="inline-block">
                        <Button >
                            Tạo lịch trình mới
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
