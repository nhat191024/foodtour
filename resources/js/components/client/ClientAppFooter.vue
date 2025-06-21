<script setup>
import { Facebook, Instagram, Twitter, Youtube } from 'lucide-vue-next'
import { Link } from '@inertiajs/vue3';

const currentYear = new Date().getFullYear()

const footerNav = [
    {
        title: 'Hồ sơ',
        items: [
            { name: 'Hồ sơ', route: 'profile' },
            { name: 'Yêu thích', route: 'profile.favorite' },
            { name: 'Lịch sử', route: 'history.index' },
            { name: 'Giới thiệu', route: 'home' },
        ],
    },
    {
        title: 'Liên kết',
        items: [
            { name: 'Trang chủ', route: 'home' },
            { name: 'Bắt đầu ngay', route: 'survey.start' },
            { name: 'Dự báo thời tiết', route: 'weathercast' },
            { name: 'Máy tính', route: 'calculator' },
        ],
    }
]

const socialLinks = [
  { icon: Facebook, href: '#', label: 'Facebook' },
  { icon: Instagram, href: '#', label: 'Instagram' },
  { icon: Twitter, href: '#', label: 'Twitter' },
  { icon: Youtube, href: '#', label: 'YouTube' },
]
</script>

<template>
  <footer class="bg-gray-900 text-gray-300 py-12 md:py-16">
    <div class="container mx-auto px-4 md:px-6 grid grid-cols-1 md:grid-cols-4 gap-8">
      <!-- Brand Info -->
      <div class="col-span-1 md:col-span-2 flex flex-col items-start">
        <Link :href="route('home')">
            <p class="text-2xl font-bold text-white mb-4">FoodTourVN</p>
        </Link>
        <p class="text-sm leading-relaxed max-w-md">
          Khám phá những trải nghiệm ẩm thực tuyệt vời nhất và những món ngon đặc sản tại VNR cùng FoodTourVNR. Người bạn đồng hành đáng tin cậy cho những cuộc phiêu lưu ẩm thực đáng nhớ.
        </p>
        <div class="flex space-x-4 mt-6">
          <a
            v-for="social in socialLinks"
            :key="social.label"
            :href="social.href"
            :aria-label="social.label"
            class="text-gray-400 hover:text-white transition-colors"
          >
            <component :is="social.icon" class="h-6 w-6" />
          </a>
        </div>
      </div>

      <!-- Navigation Links -->
      <div
        v-for="section in footerNav"
        :key="section.title"
        class="col-span-1"
      >
        <h3 class="text-lg font-semibold text-white mb-4">{{ section.title }}</h3>
        <ul class="space-y-2">
          <li v-for="item in section.items" :key="item.name">
            <Link :href="route(item.route)">
                <p class="text-sm hover:text-white transition-colors">
                {{ item.name }}
                </p>
            </Link>
          </li>
        </ul>
      </div>
    </div>

    <!-- Copyright -->
    <div class="border-t border-gray-800 mt-10 pt-8 text-center text-sm text-gray-500">
    &copy; {{ currentYear }} FoodTourVN. Đã đăng ký bản quyền.
    </div>
  </footer>
</template>
