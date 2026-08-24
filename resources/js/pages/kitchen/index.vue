<template>
    <div class="flex h-screen flex-col">
        <Nav :center_text="'Chi nhánh ' + props.branchName" />

        <div class="flex-1 overflow-hidden bg-base-200">
            <div class="h-full overflow-y-auto p-4">
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
                    <article
                        v-for="kitchen in props.kitchens"
                        :key="kitchen.id"
                        class="flex flex-row items-center gap-4 rounded-xl border-2 border-base-300 bg-base-100 p-6 text-base-content/50 shadow-sm transition-all duration-200 hover:border-primary hover:text-primary hover:shadow-md"
                    >
                        <Link :href="route('kitchen.show', { kitchen: kitchen.id })" class="flex min-w-0 items-center gap-4">
                            <FireIcon class="h-12 w-12 shrink-0" />
                            <span class="truncate text-xl font-bold">{{ kitchen.name }}</span>
                        </Link>
                    </article>
                </div>
            </div>
        </div>
    </div>
</template>
<script setup lang="ts">
import { FireIcon } from '@heroicons/vue/24/outline';
import { Link } from '@inertiajs/vue3';
import Nav from '../components/nav.vue';

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
