<script setup lang="ts">
import { useToasts, hideToast } from '@/composables/useToasts';

const { toast } = useToasts();
</script>

<template>
    <Transition name="fade">
        <div v-if="toast.show"
            class="fixed top-5 right-5 z-50 flex items-center gap-3 min-w-[300px] p-4 rounded-lg shadow-lg text-white animate-slide-in"
            :class="{
                'bg-green-500': toast.type === 'success',
                'bg-red-500': toast.type === 'error'
            }">

            <div class="flex-grow">{{ toast.message }}</div>

            <button @click="hideToast"
                    class="hover:opacity-75 transition-opacity">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                </svg>
            </button>
        </div>
    </Transition>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: all 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
    transform: translateX(100%);
}
</style>
