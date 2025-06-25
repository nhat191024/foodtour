<script setup>
import ClientLayout from '@/layouts/ClientAppLayout.vue';
import Button from '@/components/ui/button/Button.vue';
import { useForm, router, Link } from '@inertiajs/vue3';
import { ref, reactive, computed } from 'vue';
import { Heart, Trash2, Zap, RotateCw, Loader2 } from 'lucide-vue-next';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

const isReplaceModalOpen = ref(false);
const itemToReplace = ref(null);
const replaceForm = useForm({
    prompt: '',
});
const addForm = useForm({
    prompt: '',
});

const props = defineProps({
    data: Object
});

const history = computed(() => {
    if (!props.data || !props.data.items) {
        return {};
    }

    return props.data.items.reduce((accumulator, currentItem) => {
        const dayKey = currentItem.day_number;

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

const openReplaceModal = (type, item) => {
    itemToReplace.value = { id: item.id, type: type, name: item.name };
    replaceForm.reset();
    isReplaceModalOpen.value = true;
};

const isDeleteItemModalOpen = ref(false);
const itemToDelete = ref(null);
const deleteItemForm = useForm({});

const openDeleteModal = (type, item) => {
    itemToDelete.value = { id: item.id, type: type, name: item.name };
    isDeleteItemModalOpen.value = true;
};

const isAddModalOpen = ref(false);
const targetHistoryItem = ref(0);

const openAddModal = (historyItemId) => {
    targetHistoryItem.value = historyItemId;
    isAddModalOpen.value = true;
};

const submitDeleteItem = () => {
    if (!itemToDelete.value) return;

    const { type, id } = itemToDelete.value;
    const routeName = type === 'food' ? 'history.food.destroy' : 'history.sightseeing.destroy';
    const params = type === 'food' ? { food: id } : { sightseeing: id };

    deleteItemForm.delete(route(routeName, params), {
        preserveScroll: true,
        onSuccess: () => {
            isDeleteItemModalOpen.value = false;
            itemToDelete.value = null;
        }
        // todo: onError
    });
};

const submitReplacement = () => {
    if (!itemToReplace.value) return;
    const { type, id } = itemToReplace.value;

    replaceForm.post(route('history.item.replace', { type, id }), {
        preserveScroll: true,
        onSuccess: () => {
            isReplaceModalOpen.value = false;
            itemToReplace.value = null;
        }
        // todo: onError
    });
};

const submitAdd = () => {
    if (!targetHistoryItem.value) return;

    addForm.post(route('history.item.add', { id: targetHistoryItem.value }), {
        preserveScroll: true,
        onSuccess: () => {
            isAddModalOpen.value = false;
            targetHistoryItem.value = null;
        }
        // todo: onError
    });
};
</script>

<template>
    <ClientLayout>
        <div class="flex flex-col items-center px-0 md:px-[5%] lg:px-[10%]">
            <div class="flex flex-col w-full h-fit gap-6 p-5">
                <div class="flex flex-col items-start gap-2">
                    <p class="font-semibold">Kết quả gợi ý cho chuyến đi của bạn</p>
                    <h1 class="text-4xl font-semibold">{{ data.title }}</h1>
                    <div class="flex flex-wrap items-center gap-2">
                        <span v-if="data.company"
                            class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                            {{ data.company }}
                        </span>
                        <template v-if="data.interests">
                            <span v-for="interest in data.interests.split(',')" :key="interest"
                                class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                {{ interest.trim() }}
                            </span>
                        </template>
                        <span
                            class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                            Đi {{ totalDays }} ngày
                        </span>
                    </div>
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
                        <div v-for="(item, index) in itemsInDay" :key="item.id" class="flex flex-col gap-2">
                            <div class="flex flex-col gap-0">
                                <h1 class="text-2xl font-semibold">{{ getDayTimeVietnamese(item.day_time) }}</h1>
                                <p class="font-semibold">History Item: {{ history[dayLabel][index].id }}</p>
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
                                    class="flex flex-col items-start justify-start rounded-3xl shadow-lg p-6 bg-white flex-shrink-0 w-[280px] md:w-auto md:flex-1 h-fit"
                                    :class="{'md:max-w-[500px]': item.food.length === 1}">
                                    <h3 class="font-bold text-lg mb-1">🍜 {{ foodItem.name }}</h3>
                                    <div class="flex flex-wrap gap-2 mb-4">
                                        <span v-if="foodItem.food_type"
                                            class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            {{ foodItem.food_type }}
                                        </span>
                                        <span v-if="foodItem.address"
                                            class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            {{ foodItem.address }}
                                        </span>
                                    </div>
                                    <p class="text-gray-700 text-sm leading-relaxed mb-6">{{ foodItem.description }}</p>
                                    <!-- <p class="text-gray-700 text-sm leading-relaxed mb-6">{{ foodItem }}</p> -->
                                    <div class="flex flex-wrap gap-2 mt-auto w-full">
                                        <div class="flex gap-2 flex-1 min-w-[180px]">
                                            <Button @click="toggleFavoriteFood(foodItem.id)" variant="outline"
                                                class="flex items-center gap-2 flex-1"
                                                :disabled="loadingStates[foodItem.id]" :class="{
                                                    'text-white border-pink-300 bg-pink-400': foodItem.user_favorite,
                                                    'text-gray-600': !foodItem.user_favorite
                                                }">
                                                <Heart class="w-4 h-4"
                                                    :fill="foodItem.user_favorite ? 'currentColor' : 'none'" />
                                                {{ foodItem.user_favorite ? 'Đã yêu thích' : 'Yêu thích' }}
                                            </Button>
                                            <Button @click="openReplaceModal('food', foodItem)" variant="outline"
                                                class="action-btn flex-shrink-0">
                                                <RotateCw class="w-4 h-4" />
                                            </Button>
                                        </div>
                                        <div class="flex gap-2 flex-1 min-w-[180px]">
                                            <Button @click="openDeleteModal('food', foodItem)" variant="outline"
                                                class="action-btn flex-shrink-0"
                                                :disabled="loadingStates[`remove-${foodItem.id}`]">
                                                <Loader2 v-if="loadingStates[`remove-${foodItem.id}`]"
                                                    class="w-4 h-4 animate-spin" />
                                                <Trash2 v-else class="w-4 h-4" />
                                            </Button>
                                            <Button @click="goToGoogleMap(foodItem.name + ' ' + foodItem.address)"
                                                class="flex-1 cursor-pointer bg-black text-white py-3 rounded-lg text-sm font-semibold flex items-center justify-center gap-2 hover:bg-gray-800 transition-colors">
                                                Đến ngay <p aria-hidden="true">→</p>
                                            </Button>
                                        </div>
                                    </div>
                                </div>
                                <div v-for="sightseeingItem in item.sightseeing" :key="sightseeingItem.id"
                                    class="flex flex-col items-start justify-start rounded-3xl shadow-lg p-6 bg-white flex-shrink-0 w-[280px] md:w-auto md:flex-1 h-fit"
                                    :class="{'md:max-w-[500px]': item.food.length === 1}">
                                    <h3 class="font-bold text-lg mb-1">🏛️ {{ sightseeingItem.name }}</h3>
                                    <div class="flex flex-wrap gap-2 mb-4">
                                        <span
                                            class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                            Thăm quan
                                        </span>
                                        <span v-if="sightseeingItem.address"
                                            class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            {{ sightseeingItem.address }}
                                        </span>
                                    </div>
                                    <p class="text-gray-700 text-sm leading-relaxed mb-6">{{ sightseeingItem.description
                                        }}
                                    </p>
                                    <!-- <p class="text-gray-700 text-sm leading-relaxed mb-6">{{ sightseeingItem }}</p> -->
                                    <div class="flex flex-wrap gap-2 mt-auto w-full">
                                        <div class="flex gap-2 flex-1 min-w-[180px]">
                                            <Button @click="toggleFavoriteSightseeing(sightseeingItem.id)"
                                                variant="outline" class="flex items-center gap-2 flex-1"
                                                :disabled="loadingStates[sightseeingItem.id]" :class="{
                                                    'text-white border-pink-300 bg-pink-400': sightseeingItem.user_favorite,
                                                    'text-gray-600': !sightseeingItem.user_favorite
                                                }">
                                                <Heart class="w-4 h-4"
                                                    :fill="sightseeingItem.user_favorite ? 'currentColor' : 'none'" />
                                                {{ sightseeingItem.user_favorite ? 'Đã yêu thích' : 'Yêu thích' }}
                                            </Button>
                                            <Button @click="openReplaceModal('sightseeing', sightseeingItem)"
                                                variant="outline" class="action-btn flex-shrink-0">
                                                <RotateCw class="w-4 h-4" />
                                            </Button>
                                        </div>
                                        <div class="flex gap-2 flex-1 min-w-[180px]">
                                            <Button @click="openDeleteModal('sightseeing', sightseeingItem)"
                                                variant="outline" class="action-btn flex-shrink-0"
                                                :disabled="loadingStates[`remove-${sightseeingItem.id}`]">
                                                <Loader2 v-if="loadingStates[`remove-${sightseeingItem.id}`]"
                                                    class="w-4 h-4 animate-spin" />
                                                <Trash2 v-else class="w-4 h-4" />
                                            </Button>
                                            <Button
                                                @click="goToGoogleMap(sightseeingItem.name + ' ' + sightseeingItem.address)"
                                                class="flex-1 cursor-pointer bg-black text-white py-3 rounded-lg text-sm font-semibold flex items-center justify-center gap-2 hover:bg-gray-800 transition-colors">
                                                Đến ngay <p aria-hidden="true">→</p>
                                            </Button>
                                        </div>
                                    </div>
                                </div>
                                <div
                                    class="flex flex-col items-center justify-center rounded-3xl border-2 border-dashed border-gray-300 p-6 bg-gray-50 flex-shrink-0 w-[120px] h-[180px] md:w-auto md:flex-1 cursor-pointer hover:border-blue-400 transition-colors"
                                    style="min-width:120px; min-height:180px; align-self: center;"
                                    title="Thêm địa điểm mới"
                                    @click="openAddModal(history[dayLabel][index].id)"
                                >
                                    <div class="flex flex-col items-center justify-center h-full w-full">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-10 h-10 text-gray-400 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                        </svg>
                                        <span class="text-gray-500 text-sm font-medium">Thêm địa điểm</span>
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
                <div class="flex flex-col items-center justify-center w-full py-8">
                    <p class="text-lg font-semibold mb-3 text-center">
                        Cảm thấy thiếu thứ gì đó? Đừng ngại tạo một lịch trình mới ngay!
                    </p>
                    <Link :href="route('survey.start')" class="inline-block">
                        <Button size="sm" :class="'font-semibold cursor-pointer'">
                            Tạo lịch trình mới
                        </Button>
                    </Link>
                </div>
            </div>
        </div>

        <Dialog :open="isReplaceModalOpen" @update:open="isReplaceModalOpen = $event">
            <DialogContent>
                <form @submit.prevent="submitReplacement">
                    <DialogHeader>
                        <DialogTitle>Thay đổi địa điểm</DialogTitle>
                        <DialogDescription>
                            Bạn muốn thay thế "{{ itemToReplace?.name }}" bằng một địa điểm như thế nào?
                            Hãy mô tả yêu cầu của bạn.
                        </DialogDescription>
                    </DialogHeader>

                    <div class="my-4">
                        <Label for="prompt" class="sr-only">Yêu cầu mới</Label>
                        <Input id="prompt" v-model="replaceForm.prompt"
                            placeholder="Ví dụ: một quán bún chả khác gần đây" autocomplete="off" />
                        <p v-if="replaceForm.errors.prompt" class="text-sm text-red-500 mt-1">{{
                            replaceForm.errors.prompt }}</p>
                    </div>

                    <DialogFooter>
                        <Button type="button" variant="secondary" @click="isReplaceModalOpen = false">Hủy</Button>
                        <Button type="submit" :disabled="replaceForm.processing">
                            <Loader2 v-if="replaceForm.processing" class="w-4 h-4 mr-2 animate-spin" />
                            Xác nhận thay đổi
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
        <Dialog :open="isAddModalOpen" @update:open="isAddModalOpen = $event">
            <DialogContent>
                <form @submit.prevent="submitAdd">
                    <DialogHeader>
                        <DialogTitle>Thêm địa điểm mới</DialogTitle>
                        <DialogDescription>
                            Bạn muốn thêm địa điểm như thế nào?
                            Hãy mô tả yêu cầu của bạn.
                        </DialogDescription>
                    </DialogHeader>

                    <div class="my-4">
                        <Label for="prompt" class="sr-only">Yêu cầu mới</Label>
                        <Input id="prompt" v-model="addForm.prompt"
                            placeholder="Ví dụ: một khu nghỉ dưỡng..." autocomplete="off" />
                        <p v-if="addForm.errors.prompt" class="text-sm text-red-500 mt-1">{{
                            addForm.errors.prompt }}</p>
                    </div>

                    <DialogFooter>
                        <Button type="button" variant="secondary" @click="isAddModalOpen = false">Hủy</Button>
                        <Button type="submit" :disabled="addForm.processing">
                            <Loader2 v-if="addForm.processing" class="w-4 h-4 mr-2 animate-spin" />
                            Xác nhận thêm
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
        <Dialog :open="isDeleteItemModalOpen" @update:open="isDeleteItemModalOpen = $event">
            <DialogContent>
                <form @submit.prevent="submitDeleteItem">
                    <DialogHeader>
                        <DialogTitle>Xóa địa điểm</DialogTitle>
                        <DialogDescription>
                            Bạn có chắc chắn muốn xóa "{{ itemToDelete?.name }}" khỏi lịch trình không?
                        </DialogDescription>
                    </DialogHeader>

                    <div class="my-4">
                        <Label for="prompt" class="sr-only"></Label>
                    </div>

                    <DialogFooter>
                        <Button type="button" variant="secondary" @click="isDeleteItemModalOpen = false">Hủy</Button>
                        <Button type="submit" :disabled="deleteItemForm.processing">
                            <Loader2 v-if="deleteItemForm.processing" class="w-4 h-4 mr-2 animate-spin" />
                            Xác nhận xóa
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    </ClientLayout>
</template>
