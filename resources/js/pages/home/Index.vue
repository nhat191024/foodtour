<script setup>
import ClientLayout from '@/layouts/ClientAppLayout.vue';
import Button from '@/components/ui/button/Button.vue';
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <ClientLayout>
        <div class="min-h-screen bg-gradient-to-b from-blue-50 to-white">
            <!-- Hero Section -->
            <div class="relative overflow-hidden">
                <div class="absolute inset-0 z-10">
                    <div class="absolute inset-0 bg-gradient-to-r from-blue-900/70 to-purple-900/50"></div>
                    <img src="/images/landing-background.jpg"
                         alt="Beautiful travel destination"
                         class="w-full h-full object-cover">
                </div>
                <div class="relative z-20 container mx-auto px-4 py-24 md:py-32">
                    <div class="text-center max-w-4xl mx-auto space-y-8">
                        <h1 class="text-4xl md:text-6xl font-bold text-white leading-tight">
                            Khám phá thế giới với
                            <span class="text-yellow-400">AI</span>
                        </h1>
                        <p class="text-xl md:text-2xl text-blue-100 max-w-2xl mx-auto">
                            Lập kế hoạch du lịch hoàn hảo chỉ trong 60 giây. Để AI lo mọi thứ từ lịch trình đến chi phí!
                        </p>
                        <div class="flex flex-col sm:flex-row gap-4 justify-center items-center pt-4">
                            <Link :href="route('survey.start')" class="inline-block">
                                <Button class="text-lg px-10 py-4 bg-yellow-500 hover:bg-yellow-600 text-black font-semibold rounded-full shadow-lg transform hover:scale-105 transition-all duration-200">
                                    Bắt đầu ngay
                                </Button>
                            </Link>
                            <p class="text-blue-200 text-sm">✨ Miễn phí • Đăng nhập trải nghiệm ngay</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Features Section -->
            <div class="container mx-auto px-4 py-20">
                <div class="text-center mb-16">
                    <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
                        Lập kế hoạch thông minh
                    </h2>
                    <p class="text-xl text-gray-600 max-w-2xl mx-auto">
                        Trải nghiệm lập kế hoạch du lịch thông minh với công nghệ AI tiên tiến
                    </p>
                </div>

                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8 mb-20">
                    <!-- Smart Planning -->
                    <div class="bg-white rounded-2xl p-8 shadow-lg hover:shadow-xl transition-shadow duration-300 border border-blue-100">
                        <div class="w-16 h-16 bg-blue-500 rounded-full flex items-center justify-center mb-6 mx-auto">
                            <span class="material-icons text-white text-2xl">psychology</span>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-4 text-center">Lập kế hoạch thông minh</h3>
                        <p class="text-gray-600 text-center">
                            AI phân tích sở thích của bạn và tạo ra lịch trình du lịch được cá nhân hóa hoàn toàn
                        </p>
                    </div>

                    <!-- Cost Calculator -->
                    <div class="bg-white rounded-2xl p-8 shadow-lg hover:shadow-xl transition-shadow duration-300 border border-green-100">
                        <div class="w-16 h-16 bg-green-500 rounded-full flex items-center justify-center mb-6 mx-auto">
                            <span class="material-icons text-white text-2xl">calculate</span>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-4 text-center">Tính chi phí dễ dàng</h3>
                        <p class="text-gray-600 text-center">
                            Ước tính chi phí chính xác cho toàn bộ chuyến đi, từ vé máy bay đến khách sạn và ăn uống
                        </p>
                    </div>

                    <!-- Weather Integration -->
                    <div class="bg-white rounded-2xl p-8 shadow-lg hover:shadow-xl transition-shadow duration-300 border border-orange-100">
                        <div class="w-16 h-16 bg-orange-500 rounded-full flex items-center justify-center mb-6 mx-auto">
                            <span class="material-icons text-white text-2xl">wb_sunny</span>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-4 text-center">Dự báo thời tiết thông minh</h3>
                        <p class="text-gray-600 text-center">
                            AI đọc dự báo thời tiết và đưa ra gợi ý hoạt động phù hợp cho từng ngày trong chuyến đi
                        </p>
                    </div>

                    <!-- Save Favorites -->
                    <div class="bg-white rounded-2xl p-8 shadow-lg hover:shadow-xl transition-shadow duration-300 border border-red-100">
                        <div class="w-16 h-16 bg-red-500 rounded-full flex items-center justify-center mb-6 mx-auto">
                            <span class="material-icons text-white text-2xl">favorite</span>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-4 text-center">Lưu yêu thích</h3>
                        <p class="text-gray-600 text-center">
                            Lưu lại những chuyến đi và địa điểm yêu thích để dễ dàng lập kế hoạch cho lần sau
                        </p>
                    </div>

                    <!-- Quick Survey -->
                    <div class="bg-white rounded-2xl p-8 shadow-lg hover:shadow-xl transition-shadow duration-300 border border-purple-100">
                        <div class="w-16 h-16 bg-purple-500 rounded-full flex items-center justify-center mb-6 mx-auto">
                            <span class="material-icons text-white text-2xl">quiz</span>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-4 text-center">Khảo sát nhanh</h3>
                        <p class="text-gray-600 text-center">
                            Chỉ cần trả lời vài câu hỏi đơn giản, AI sẽ hiểu được sở thích du lịch của bạn
                        </p>
                    </div>

                    <!-- Trip Management -->
                    <div class="bg-white rounded-2xl p-8 shadow-lg hover:shadow-xl transition-shadow duration-300 border border-indigo-100">
                        <div class="w-16 h-16 bg-indigo-500 rounded-full flex items-center justify-center mb-6 mx-auto">
                            <span class="material-icons text-white text-2xl">travel_explore</span>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-4 text-center">Quản lý chuyến đi</h3>
                        <p class="text-gray-600 text-center">
                            Tạo, chỉnh sửa và quản lý nhiều chuyến đi với giao diện thân thiện
                        </p>
                    </div>
                </div>
            </div>

            <!-- How It Works Section -->
            <div class="bg-gradient-to-r from-blue-600 to-purple-600 py-20">
                <div class="container mx-auto px-4">
                    <div class="text-center mb-16">
                        <h2 class="text-3xl md:text-4xl font-bold text-white mb-4">
                            Cách thức hoạt động
                        </h2>
                        <p class="text-xl text-blue-100 max-w-2xl mx-auto">
                            Chỉ 3 bước đơn giản để có chuyến đi hoàn hảo
                        </p>
                    </div>

                    <div class="grid md:grid-cols-3 gap-8 max-w-4xl mx-auto">
                        <div class="text-center">
                            <div class="w-20 h-20 bg-white rounded-full flex items-center justify-center mb-6 mx-auto">
                                <span class="text-3xl font-bold text-blue-600">1</span>
                            </div>
                            <h3 class="text-xl font-bold text-white mb-4">Trả lời khảo sát</h3>
                            <p class="text-blue-100">
                                Chia sẻ sở thích, ngân sách và thời gian du lịch của bạn
                            </p>
                        </div>

                        <div class="text-center">
                            <div class="w-20 h-20 bg-white rounded-full flex items-center justify-center mb-6 mx-auto">
                                <span class="text-3xl font-bold text-blue-600">2</span>
                            </div>
                            <h3 class="text-xl font-bold text-white mb-4">AI xử lý</h3>
                            <p class="text-blue-100">
                                Công nghệ AI phân tích và tạo ra kế hoạch tối ưu cho bạn
                            </p>
                        </div>

                        <div class="text-center">
                            <div class="w-20 h-20 bg-white rounded-full flex items-center justify-center mb-6 mx-auto">
                                <span class="text-3xl font-bold text-blue-600">3</span>
                            </div>
                            <h3 class="text-xl font-bold text-white mb-4">Nhận kế hoạch</h3>
                            <p class="text-blue-100">
                                Lịch trình chi tiết với chi phí, thời tiết và gợi ý hoạt động
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- CTA Section -->
            <div class="container mx-auto px-4 py-20">
                <div class="bg-gradient-to-r from-yellow-400 to-orange-500 rounded-3xl p-12 text-center">
                    <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
                        Sẵn sàng cho chuyến phiêu lưu tiếp theo?
                    </h2>
                    <p class="text-xl text-gray-800 mb-8 max-w-2xl mx-auto">
                        Hàng nghìn du khách đã tin tưởng AI của chúng tôi để tạo ra những chuyến đi đáng nhớ
                    </p>
                    <Link :href="route('survey.start')" class="inline-block">
                        <Button class="text-xl px-12 py-4 bg-gray-900 hover:bg-gray-800 text-white font-bold rounded-full shadow-xl transform hover:scale-105 transition-all duration-200">
                            Tạo chuyến đi ngay
                        </Button>
                    </Link>
                    <p class="text-gray-700 text-sm mt-4">Thời gian khảo sát ước tính là 1 phút</p>
                </div>
            </div>

            <!-- Stats Section -->
            <div class="bg-gray-50 py-16">
                <div class="container mx-auto px-4">
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-8 text-center">
                        <div>
                            <div class="text-3xl md:text-4xl font-bold text-blue-600 mb-2">10K+</div>
                            <div class="text-gray-600">Chuyến đi đã tạo</div>
                        </div>
                        <div>
                            <div class="text-3xl md:text-4xl font-bold text-green-600 mb-2">95%</div>
                            <div class="text-gray-600">Khách hàng hài lòng</div>
                        </div>
                        <div>
                            <div class="text-3xl md:text-4xl font-bold text-purple-600 mb-2">50+</div>
                            <div class="text-gray-600">Quốc gia hỗ trợ</div>
                        </div>
                        <div>
                            <div class="text-3xl md:text-4xl font-bold text-orange-600 mb-2">24/7</div>
                            <div class="text-gray-600">Hỗ trợ AI</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </ClientLayout>
</template>

<style scoped>
.material-icons {
    font-family: 'Material Icons';
    font-weight: normal;
    font-style: normal;
    font-size: 24px;
    line-height: 1;
    letter-spacing: normal;
    text-transform: none;
    display: inline-block;
    white-space: nowrap;
    word-wrap: normal;
    direction: ltr;
    -webkit-font-feature-settings: 'liga';
    -webkit-font-smoothing: antialiased;
}
</style>
