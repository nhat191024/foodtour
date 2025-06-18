<script setup lang="ts">

import { usePage } from '@inertiajs/vue3';
import { watch } from 'vue';

import ClientFooter from '@/components/client/ClientAppFooter.vue';
import ClientHeader from '@/components/client/ClientAppHeader.vue';

import SuccessErrorToast from '@/components/client/SuccessErrorToast.vue';
import { showToast } from '@/composables/useToasts';

const page = usePage();

import type { BreadcrumbItemType } from '@/types';

interface Props {
    breadcrumbs?: BreadcrumbItemType[];
}

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});

watch(() => page.props.flash, (flash) => {
    if (flash.success) {
        showToast(flash.success, 'success');
    } else if (flash.error) {
        showToast(flash.error, 'error');
    }
}, { deep: true });
</script>

<style>
@import url('https://fonts.googleapis.com/icon?family=Material+Icons');
</style>

<template>
    <ClientHeader />
    <SuccessErrorToast />
    <slot />
    <ClientFooter />
</template>
