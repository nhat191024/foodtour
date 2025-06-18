<script setup>
import ClientLayout from '@/layouts/ClientAppLayout.vue';
import Button from '@/components/ui/button/Button.vue';
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <ClientLayout>
        <div class="container mx-auto px-4 py-12 space-y-16">
            <div class="relative">
            <div class="absolute inset-0 z-10">
                <div class="absolute inset-0 bg-black opacity-50 rounded-2xl"></div>
                <img src="https://www.tripsavvy.com/thmb/6pR-3PKB636UtjnuhCP1bstykH0=/4256x2832/filters:fill(auto,1)/GettyImages-583644537-58f648603df78ca159f4055f.jpg" alt="Background"
                 class="w-full h-full object-cover opacity-70 rounded-2xl">
            </div>
            <div class="relative z-20 text-center max-w-2xl mx-auto space-y-4 py-20">
                <h1 class="text-3xl md:text-4xl font-bold text-white">Bạn đang muốn đi chơi?</h1>
                <p class="text-lg text-white">Lập kế hoạch cho chuyến đi của bạn với AI chỉ trong 1 phút</p>
                <Link :href="route('survey.start')" class="inline-block">
                <Button class="text-lg px-8 py-3 cursor-pointer">Thử ngay</Button>
                </Link>
            </div>
            </div>

            <div class="text-center max-w-2xl mx-auto space-y-4">
            <p class="material-icons text-gray-950 mb-6" style="font-size: 3rem !important;">psychology</p>
            <h2 class="text-2xl md:text-3xl font-bold text-gray-900">Lập kế hoạch thông minh hơn?</h2>
            <p class="text-lg text-gray-600">Để AI lo phần khó. Có kế hoạch trong chốc lát</p>
            </div>
        </div>
    </ClientLayout>
</template>
