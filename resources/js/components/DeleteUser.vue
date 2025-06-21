<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

// Components
import HeadingSmall from '@/components/HeadingSmall.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

const passwordInput = ref<HTMLInputElement | null>(null);

const form = useForm({
    password: '',
});

const deleteUser = (e: Event) => {
    e.preventDefault();

    form.delete(route('profile.destroy'), {
        preserveScroll: true,
        onSuccess: () => closeModal(),
        onError: () => passwordInput.value?.focus(),
        onFinish: () => form.reset(),
    });
};

const closeModal = () => {
    form.clearErrors();
    form.reset();
};
</script>

<template>
    <div class="space-y-6">
        <HeadingSmall title="Xóa tài khoản" description="Xóa tài khoản của bạn và tất cả tài nguyên liên quan" />
        <div class="space-y-4 rounded-lg border border-red-100 bg-red-50 p-4 dark:border-red-200/10 dark:bg-red-700/10">
            <div class="relative space-y-0.5 text-red-600 dark:text-red-100">
                <p class="font-medium">Cảnh báo</p>
                <p class="text-sm">Hãy cẩn thận, thao tác này không thể hoàn tác.</p>
            </div>
            <Dialog>
                <DialogTrigger as-child>
                    <Button variant="destructive">Xóa tài khoản</Button>
                </DialogTrigger>
                <DialogContent>
                    <form class="space-y-6" @submit="deleteUser">
                        <DialogHeader class="space-y-3">
                            <DialogTitle>Bạn có chắc chắn muốn xóa tài khoản của mình không?</DialogTitle>
                            <DialogDescription>
                                Khi tài khoản của bạn bị xóa, tất cả tài nguyên và dữ liệu cũng sẽ bị xóa vĩnh viễn. Vui
                                lòng nhập mật khẩu để xác nhận bạn muốn xóa tài khoản vĩnh viễn.
                            </DialogDescription>
                        </DialogHeader>

                        <div class="grid gap-2">
                            <Label for="password" class="sr-only">Mật khẩu</Label>
                            <Input id="password" type="password" name="password" ref="passwordInput"
                                v-model="form.password" placeholder="Mật khẩu" />
                            <InputError :message="form.errors.password" />
                        </div>

                        <DialogFooter class="gap-2">
                            <DialogClose as-child>
                                <Button variant="secondary" @click="closeModal">Hủy</Button>
                            </DialogClose>

                            <Button variant="destructive" :disabled="form.processing">
                                <button type="submit">Xóa tài khoản</button>
                            </Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>
        </div>
    </div>
</template>
