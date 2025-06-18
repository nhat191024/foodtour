<script setup lang="ts">
import ClientAppLayout from '@/layouts/app/ClientAppSidebarLayout.vue';
import type { BreadcrumbItemType } from '@/types';

interface Props {
    breadcrumbs?: BreadcrumbItemType[];
}

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});
</script>

<template>
    <ClientAppLayout :breadcrumbs="breadcrumbs">
        <slot />
    </ClientAppLayout>
</template>
