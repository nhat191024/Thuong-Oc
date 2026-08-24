<template>
    <div class="dropdown" :class="align === 'end' ? 'dropdown-end' : 'dropdown-start'">
        <button
            tabindex="0"
            type="button"
            class="btn btn-circle text-current btn-ghost btn-sm"
            :aria-label="`Giao diện: ${currentLabel}`"
            :title="`Giao diện: ${currentLabel}`"
        >
            <SunIcon v-if="preference === 'light'" class="size-5" />
            <MoonIcon v-else-if="preference === 'dark'" class="size-5" />
            <ComputerDesktopIcon v-else class="size-5" />
        </button>

        <ul tabindex="0" class="dropdown-content menu z-50 mt-2 w-44 rounded-box border border-base-300 bg-base-100 p-2 text-base-content shadow-xl">
            <li v-for="option in options" :key="option.value">
                <button type="button" :class="{ 'menu-active': preference === option.value }" @click="setPreference(option.value)">
                    <component :is="option.icon" class="size-4" />
                    {{ option.label }}
                    <CheckIcon v-if="preference === option.value" class="ml-auto size-4" />
                </button>
            </li>
        </ul>
    </div>
</template>

<script setup lang="ts">
import { useTheme, type ThemePreference } from '@/composables/useTheme';
import { CheckIcon, ComputerDesktopIcon, MoonIcon, SunIcon } from '@heroicons/vue/24/outline';
import { computed, type Component } from 'vue';

withDefaults(
    defineProps<{
        align?: 'start' | 'end';
    }>(),
    {
        align: 'end',
    },
);

const { preference, setPreference } = useTheme();

const options: Array<{ value: ThemePreference; label: string; icon: Component }> = [
    { value: 'light', label: 'Sáng', icon: SunIcon },
    { value: 'dark', label: 'Tối', icon: MoonIcon },
    { value: 'system', label: 'Theo hệ thống', icon: ComputerDesktopIcon },
];

const currentLabel = computed(() => options.find((option) => option.value === preference.value)?.label ?? 'Theo hệ thống');
</script>
