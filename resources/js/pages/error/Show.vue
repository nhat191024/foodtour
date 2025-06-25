<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    status: Number,
});

const title = computed(() => {
    return {
        503: '503: Service Unavailable',
        500: '500: Server Error',
        404: '404: Page Not Found',
        403: '403: Forbidden',
        401: '401: Unauthorized',
    }[props.status] || 'Error';
});

const description = computed(() => {
    return {
        503: 'Xin lỗi, chúng tôi đang thực hiện một số bảo trì. Vui lòng quay lại sau.',
        500: 'Oops, đã có lỗi xảy ra ở phía máy chủ của chúng tôi.',
        404: 'Xin lỗi, trang bạn đang tìm kiếm không tồn tại.',
        403: 'Xin lỗi, bạn không có quyền truy cập trang này.',
        401: 'Xin lỗi, bạn cần phải đăng nhập để xem trang này.',
    }[props.status] || 'Đã có lỗi xảy ra.';
});
</script>

<template>
    <div class="flex items-center justify-center min-h-screen bg-gray-100 text-gray-800">
        <div class="text-center p-8">
            <h1 class="text-6xl md:text-9xl font-extrabold text-blue-600">{{ status }}</h1>
            <h2 class="mt-4 text-2xl md:text-4xl font-bold tracking-tight">{{ title }}</h2>
            <p class="mt-4 text-base text-gray-500">{{ description }}</p>

            <Link
                :href="route('home')"
                class="mt-8 inline-block px-8 py-3 bg-blue-600 text-white font-semibold rounded-lg shadow-md hover:bg-blue-700 transition-colors duration-300"
            >
                Quay về trang chủ
            </Link>
        </div>
    </div>
</template>
