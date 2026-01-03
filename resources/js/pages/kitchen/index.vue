<template>
    <div class="flex h-screen flex-col">
        <Nav :center_text="'Chi nhánh ' + props.branchName" />

        <div class="flex-1 overflow-hidden bg-gray-100">
            <div class="h-full overflow-y-auto p-4">
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
                    <Link
                        v-for="kitchen in props.kitchens"
                        :key="kitchen.id"
                        class="flex cursor-pointer flex-row items-center gap-4 rounded-xl border-2 border-gray-200 bg-white p-6 text-gray-400 shadow-sm transition-all duration-200 hover:border-primary hover:text-primary hover:shadow-md"
                        :href="route('kitchen.show', { kitchen: kitchen.id })"
                    >
                        <FireIcon class="h-12 w-12" />
                        <span class="text-xl font-bold">{{ kitchen.name }}</span>
                    </Link>
                </div>
            </div>
        </div>
    </div>
</template>
<script setup lang="ts">
import { FireIcon } from '@heroicons/vue/24/outline';
import Nav from '../components/nav.vue';
import { Link } from '@inertiajs/vue3';

interface Kitchen {
    id: number;
    name: string;
    branch_id: number;
}

interface Props {
    kitchens: Kitchen[];
    branchName: string;
}

const props = defineProps<Props>();
</script>
