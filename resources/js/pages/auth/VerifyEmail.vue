<script setup lang="ts">
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { LoaderCircle } from 'lucide-vue-next';

defineProps<{
    status?: string;
}>();

const form = useForm({});

const submit = () => {
    form.post(route('verification.send'));
};
</script>

<template>
    <AuthLayout title="Xác minh email" description="Vui lòng xác minh địa chỉ email của bạn bằng cách nhấp vào liên kết mà chúng tôi vừa gửi cho bạn.">
        <Head title="Xác minh email" />

        <div v-if="status === 'verification-link-sent'" class="mb-4 text-center text-sm font-medium text-green-600">
            Một liên kết xác minh mới đã được gửi đến địa chỉ email mà bạn đã đăng ký.
        </div>

        <form @submit.prevent="submit" class="space-y-6 text-center">
            <Button :disabled="form.processing" variant="secondary">
                <LoaderCircle v-if="form.processing" class="h-4 w-4 animate-spin" />
                Gửi lại email xác minh
            </Button>

            <TextLink :href="route('logout')" method="post" as="button" class="mx-auto block text-sm"> Đăng xuất </TextLink>
        </form>
    </AuthLayout>
</template>
