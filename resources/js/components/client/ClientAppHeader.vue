<script setup>
import { ref } from 'vue'
import { Menu, X } from 'lucide-vue-next'
import { usePage } from '@inertiajs/vue3'
import { Link } from '@inertiajs/vue3';

const user = usePage().props.auth.user

const isMenuOpen = ref(false)

const toggleMenu = () => {
    isMenuOpen.value = !isMenuOpen.value
}

const closeMenu = () => {
    isMenuOpen.value = false
}

const navItems = [
    { name: 'Trang chủ', route: 'home' },
    { name: 'Bắt đầu', route: 'survey.start' },
    { name: 'Dự báo thời tiết', route: 'weathercast' },
    { name: 'Máy tính', route: 'calculator' },
]
</script>

<template>
    <header class="sticky top-0 z-50 w-full border-b bg-white/95 shadow-sm backdrop-blur supports-[backdrop-filter]:bg-white/60">
        <div class="container flex h-16 items-center justify-between px-4 md:px-6">
            <Link :href="route('home')">
                <span class="mr-6 flex items-center space-x-2 font-bold text-lg">
                    FoodTourVN
                </span>
            </Link>

            <!-- Desktop Navigation -->
            <nav class="hidden md:flex flex-1 items-center justify-end gap-6">
                <Link v-for="item in navItems" :key="item.name" :href="route(item.route)"
                    class="text-sm font-medium transition-colors hover:text-primary">
                    {{ item.name }}
                </Link>
                <div v-if="user">
                    <Link :href="route('profile')" class="flex items-center gap-3">
                        <span class="text-sm font-medium">{{ user.name }}</span>
                        <img :src="user.avatar ?? 'https://ui-avatars.com/api/?name=' + user.name" :alt="user.name"
                            class="w-8 h-8 rounded-full" />
                    </Link>
                </div>
                <Link v-else :href="route('login')"
                    class="inline-flex h-9 items-center justify-center rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground shadow transition-colors hover:bg-primary/90 focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50">
                    Đăng nhập
                </Link>
            </nav>

            <!-- Mobile Menu Button -->
            <button
                class="md:hidden inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors hover:bg-accent hover:text-accent-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 h-10 w-10"
                @click="toggleMenu"
                aria-label="Toggle navigation menu"
                :aria-expanded="isMenuOpen">
                <Menu v-if="!isMenuOpen" class="h-6 w-6" />
                <X v-else class="h-6 w-6" />
                <span class="sr-only">Mở thanh điều hướng</span>
            </button>
        </div>

        <!-- Mobile Navigation Menu -->
        <Transition
            enter-active-class="transition-all duration-300 ease-out"
            enter-from-class="opacity-0 max-h-0"
            enter-to-class="opacity-100 max-h-96"
            leave-active-class="transition-all duration-300 ease-in"
            leave-from-class="opacity-100 max-h-96"
            leave-to-class="opacity-0 max-h-0">
            <div v-if="isMenuOpen" class="md:hidden overflow-hidden bg-white border-t border-gray-200">
                <nav class="px-4 py-4 space-y-3">
                    <!-- Navigation Links -->
                    <div class="space-y-2">
                        <Link
                            v-for="item in navItems"
                            :key="item.name"
                            :href="route(item.route)"
                            class="block px-3 py-2 text-base font-medium text-gray-700 hover:text-primary hover:bg-gray-50 rounded-md transition-colors"
                            @click="closeMenu">
                            {{ item.name }}
                        </Link>
                    </div>

                    <!-- Divider -->
                    <div class="border-t border-gray-200 my-3"></div>

                    <!-- User Section -->
                    <div v-if="user" class="px-3 py-2">
                        <Link :href="route('profile')" @click="closeMenu" class="flex items-center gap-3 hover:bg-gray-50 rounded-md p-2 transition-colors">
                            <img
                                :src="user.avatar ?? 'https://ui-avatars.com/api/?name=' + user.name"
                                :alt="user.name"
                                class="w-10 h-10 rounded-full" />
                            <div class="flex flex-col">
                                <span class="text-base font-medium text-gray-900">{{ user.name }}</span>
                                <span class="text-sm text-gray-500">Xem hồ sơ</span>
                            </div>
                        </Link>
                    </div>

                    <!-- Login Button -->
                    <div v-else class="px-3">
                        <Link
                            :href="route('login')"
                            class="flex w-full items-center justify-center rounded-md bg-primary px-4 py-3 text-base font-medium text-primary-foreground shadow transition-colors hover:bg-primary/90 focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
                            @click="closeMenu">
                            Đăng nhập
                        </Link>
                    </div>
                </nav>
            </div>
        </Transition>
    </header>
</template>

<style scoped>
.transition-all {
    transition-property: all;
}
</style>
