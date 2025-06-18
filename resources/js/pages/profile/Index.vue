<script setup>
import ClientLayout from '@/layouts/ClientAppLayout.vue';
import Button from '@/components/ui/button/Button.vue';
import { Link, router } from '@inertiajs/vue3';
import { ref } from 'vue'
import { PencilIcon } from 'lucide-vue-next'

import { usePage } from '@inertiajs/vue3'

const user = usePage().props.auth.user

const currentUser = ref({
    username: user.name,
    handle: user.email,
    avatarUrl: user.avatar,
    bannerUrl: "/images/profile-banner.png", // fix later
})

const handleLogout = () => {
    router.flushAll();
};
</script>

<template>
    <ClientLayout>
        <div class="min-h-screen bg-gray-100 dark:bg-gray-900">
            <!-- Banner Section -->
            <div class="relative w-full h-48 md:h-64 overflow-hidden">
                <img :src="currentUser.bannerUrl || '/placeholder.svg'" alt="Profile banner"
                    class="w-full h-full object-cover" width="1200" height="256" />
            </div>

            <!-- Profile Info Section -->
            <div class="relative -mt-16 ml-4 md:ml-8 flex flex-col md:flex-row md:items-end md:justify-between md:pr-8">
                <div class="flex items-end">
                    <!-- Avatar -->
                    <div
                        class="h-32 w-32 md:h-40 md:w-40 rounded-full border-4 border-white dark:border-gray-900 z-10 overflow-hidden flex items-center justify-center bg-gray-200 dark:bg-gray-700">
                        <img :src="currentUser.avatarUrl || '/placeholder.svg'"
                            :alt="`${currentUser.username}'s avatar`" class="w-full h-full object-cover" />
                        <!-- Fallback for avatar, if needed, could be a span with initials -->
                        <span v-if="!currentUser.avatarUrl"
                            class="text-4xl font-semibold text-gray-600 dark:text-gray-300">
                            {{currentUser.username.split(' ').map(n => n[0]).join('')}}
                        </span>
                    </div>

                    <!-- Username and Handle -->
                    <div class="ml-4 flex flex-col mb-2 md:mb-4">
                        <div class="flex items-center">
                            <h2 class="text-2xl md:text-3xl font-bold text-gray-900 dark:text-white">{{
                                currentUser.username }}</h2>
                            <!-- Pencil icon for mobile/small screens -->
                            <Link :href="route('profile.edit')">
                            <button
                                class="ml-2 md:hidden p-2 rounded-md hover:bg-gray-200 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500">
                                <PencilIcon class="h-5 w-5 text-gray-600 dark:text-gray-400" />
                            </button>
                            </Link>
                        </div>
                        <p class="text-gray-500 text-lg">{{ currentUser.handle }}</p>
                    </div>
                </div>

                <!-- Pencil icon for desktop/large screens -->
                <Link :href="route('profile.edit')">
                <button
                    class="hidden md:block mt-4 md:mt-0 p-2 rounded-md hover:bg-gray-200 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500">
                    <PencilIcon class="h-5 w-5 text-gray-600 dark:text-gray-400" />
                </button>
                </Link>
            </div>

            <!-- Quick Access Buttons and Content Area -->
            <div class="p-4 md:p-8 mt-4 md:mt-8">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <Link :href="route('profile.favorite')" class="w-full">
                    <Button :class="'cursor-pointer inline-flex w-full h-10 px-4 py-6 text-lg'">
                        Yêu thích
                    </Button>
                    </Link>
                    <Link :href="route('history.index')" class="w-full">
                    <Button :class="'cursor-pointer inline-flex w-full h-10 px-4 py-6 text-lg'">
                        Lịch sử
                    </Button>
                    </Link>
                </div>

                <!-- Placeholder for other profile content -->
                <div
                    class="mt-8 p-6 bg-white dark:bg-gray-800 rounded-lg shadow-sm min-h-[300px] flex items-center justify-center text-gray-400 dark:text-gray-600 border border-dashed border-gray-300 dark:border-gray-700">
                    <!-- TODO: Implement more stuff here  -->
                     Nothing is here yet...
                </div>
                <div class="p-4 md:p-8">
                    <Link class="block w-full" method="post" :href="route('logout')" @click="handleLogout" as="button">
                    <Button variant="outline" class="w-full text-red-500 border-red-500 hover:bg-red-50">
                        Log out
                    </Button>
                    </Link>
                </div>
            </div>
        </div>
    </ClientLayout>
</template>
