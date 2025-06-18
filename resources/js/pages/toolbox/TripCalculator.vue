<script setup>
import { ref, computed } from 'vue';
import { useForm, router } from '@inertiajs/vue3'; // Import thêm router
import ClientLayout from '@/layouts/ClientAppLayout.vue';
import Button from '@/components/ui/button/Button.vue';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogClose,
} from '@/components/ui/dialog';

const props = defineProps({
    historyItem: {
        type: Object,
        required: true,
    }
});

const isAddCostModalOpen = ref(false);
const numberOfMembers = ref(1);
const deletingCostId = ref(null);
const form = useForm({
    name: '',
    value: null,
});

const totalCost = computed(() => {
    if (!props.historyItem || !props.historyItem.trip_costs) {
        return 0;
    }
    return props.historyItem.trip_costs.reduce((sum, item) => sum + Number(item.value), 0);
});

const costPerPerson = computed(() => {
    if (numberOfMembers.value > 0) {
        return totalCost.value / numberOfMembers.value;
    }
    return 0;
});

const openAddCostModal = () => {
    isAddCostModalOpen.value = true;
};

const closeAddCostModal = () => {
    isAddCostModalOpen.value = false;
    form.reset();
};

const submitNewCost = () => {
    form.post(route('calculator.store_cost', { history: props.historyItem.id }), {
        preserveScroll: true,
        onSuccess: () => {
            closeAddCostModal();
        },
    });
};

const openDeleteConfirmDialog = (costId) => {
    costIdToDelete.value = costId;
    isConfirmDeleteModalOpen.value = true;
};

const confirmDeletion = () => {
    if (!costIdToDelete.value) return;

    deletingCostId.value = costIdToDelete.value;

    router.delete(route('calculator.destroy_cost', { cost: costIdToDelete.value }), {
        preserveScroll: true,
        onSuccess: () => {
            isConfirmDeleteModalOpen.value = false;
        },
        onFinish: () => {
            deletingCostId.value = null;
            costIdToDelete.value = null;
        }
    });
};

const isConfirmDeleteModalOpen = ref(false);
const costIdToDelete = ref(null);
</script>

<template>
    <ClientLayout>
        <div class="container mx-auto p-4 md:p-8">

            <div class="mb-8 text-center">
                <h1 class="text-3xl font-bold text-gray-800">{{ historyItem.title }}</h1>
                <p class="text-gray-500 mt-1">Quản lý và tính toán chi phí cho chuyến đi.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="md:col-span-2 bg-white p-6 rounded-lg shadow-md">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-xl font-semibold">Danh sách chi phí</h2>
                        <Button @click="openAddCostModal">+ Thêm chi phí</Button>
                    </div>

                    <div v-if="historyItem.trip_costs.length > 0" class="space-y-3">
                        <div v-for="cost in historyItem.trip_costs" :key="cost.id"
                            class="flex justify-between items-center p-3 bg-gray-50 rounded-md group">
                            <span class="text-gray-700">{{ cost.name }}</span>
                            <div class="flex items-center gap-4">
                                <span class="font-medium text-gray-900">{{ Number(cost.value).toLocaleString() }}
                                    VND</span>
                                <button @click="openDeleteConfirmDialog(cost.id)"
                                    class="text-gray-400 hover:text-red-500 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div v-else class="text-center py-8 text-gray-500">
                        <p>Chưa có chi phí nào được thêm.</p>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-lg shadow-md h-fit">
                    <h2 class="text-xl font-semibold mb-4 border-b pb-3">Bảng tính</h2>
                    <div class="space-y-4">
                        <div class="flex justify-between items-baseline">
                            <span class="text-gray-600">Tổng cộng:</span>
                            <span class="text-2xl font-bold text-blue-600">{{ totalCost.toLocaleString() }} VND</span>
                        </div>
                        <div>
                            <label for="members" class="block text-sm font-medium text-gray-600 mb-1">Chia cho:</label>
                            <input id="members" type="number" v-model.number="numberOfMembers" min="1"
                                class="w-full p-2 border rounded-md" />
                        </div>
                        <div class="flex justify-between items-baseline pt-4 border-t">
                            <span class="font-medium">Mỗi người trả:</span>
                            <span class="text-xl font-bold text-green-600">{{ costPerPerson.toLocaleString(undefined,
                                {maximumFractionDigits: 0}) }} VND</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <transition enter-active-class="transition ease-out duration-200" enter-from-class="opacity-0"
            enter-to-class="opacity-100" leave-active-class="transition ease-in duration-150"
            leave-from-class="opacity-100" leave-to-class="opacity-0">
            <div v-if="isAddCostModalOpen" class="fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4">
                <div @click.self="closeAddCostModal" class="absolute inset-0"></div>
                <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-md z-10">
                    <h3 class="text-lg font-medium mb-4">Thêm chi phí mới</h3>
                    <form @submit.prevent="submitNewCost" class="space-y-4">
                        <div>
                            <label for="cost-name">Tên chi phí</label>
                            <input id="cost-name" type="text" v-model="form.name"
                                class="mt-1 w-full p-2 border rounded-md" required />
                            <div v-if="form.errors.name" class="text-red-500 text-sm mt-1">{{ form.errors.name }}</div>
                        </div>
                        <div>
                            <label for="cost-value">Số tiền (VND)</label>
                            <input id="cost-value" type="number" v-model="form.value"
                                class="mt-1 w-full p-2 border rounded-md" required />
                            <div v-if="form.errors.value" class="text-red-500 text-sm mt-1">{{ form.errors.value }}
                            </div>
                        </div>
                        <div class="flex justify-end gap-3 pt-4">
                            <Button type="button" variant="outline" @click="closeAddCostModal">Hủy</Button>
                            <Button type="submit" :disabled="form.processing">
                                <span v-if="form.processing">Đang lưu...</span>
                                <span v-else>Lưu chi phí</span>
                            </Button>
                        </div>
                    </form>
                </div>
            </div>
        </transition>
        <Dialog :open="isConfirmDeleteModalOpen" @update:open="isConfirmDeleteModalOpen = $event">
            <DialogContent>
                <DialogHeader class="space-y-3">
                    <DialogTitle>Bạn có chắc chắn muốn xóa?</DialogTitle>
                    <DialogDescription>
                        Hành động này sẽ xóa vĩnh viễn chi phí đã chọn. Bạn sẽ không thể hoàn tác.
                    </DialogDescription>
                </DialogHeader>
                <DialogFooter class="gap-2 sm:justify-end">
                    <DialogClose as-child>
                        <Button variant="secondary">Hủy</Button>
                    </DialogClose>
                    <Button variant="destructive" @click="confirmDeletion" :disabled="!!deletingCostId">
                        <span v-if="deletingCostId">Đang xóa...</span>
                        <span v-else>Xác nhận xóa</span>
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </ClientLayout>
</template>
