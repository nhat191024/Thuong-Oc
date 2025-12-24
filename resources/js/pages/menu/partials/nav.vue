<template>
    <nav class="flex items-center justify-between bg-primary px-4 py-1">
        <img class="h-14 w-14 rounded-full max-[400px]:h-12 max-[400px]:w-12" :src="settings.app_logo ?? ''" alt="Logo" />
        <p class="text-2xl font-semibold text-white">Bàn {{ props.tableNumber }}</p>
    </nav>
</template>

<script setup lang="ts">
import { AppSettings } from '@/types';
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const settings = computed(() => page.props.app_settings as AppSettings);

interface Props {
    tableNumber: number;
}

const props = defineProps<Props>();
</script>
