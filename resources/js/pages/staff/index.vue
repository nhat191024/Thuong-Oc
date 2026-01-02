<template>
    <div class="flex h-screen flex-col">
        <Nav :center_text="'Chi nhánh ' +props.branchName" />

        <div class="flex-1 overflow-hidden bg-gray-100">
            <div class="h-full overflow-x-auto p-4">
                <div class="grid h-full min-w-max grid-flow-col grid-rows-2 gap-4">
                    <Link
                        v-for="table in props.tables"
                        :key="table.id"
                        class="flex w-64 cursor-pointer flex-col items-center justify-center rounded-xl border-2 p-4 shadow-sm transition-all duration-200"
                        :class="[
                            table.is_active === 'active'
                                ? 'scale-105 border-primary bg-primary text-white shadow-lg shadow-primary/30'
                                : 'border-gray-200 bg-white text-gray-400 hover:border-primary hover:text-primary hover:shadow-md',
                        ]"
                        :href="route('staff.table.show', { tableId: table.id })"
                    >
                        <TableCellsIcon class="mb-4 h-16 w-16" />
                        <span class="text-2xl font-bold">Bàn {{ table.table_number }}</span>
                        <span class="mt-2 text-sm">
                            {{ table.is_active === 'active' ? 'Hoạt động' : 'Ngừng hoạt động' }}
                        </span>
                    </Link>
                </div>
            </div>
        </div>
    </div>
</template>
<script setup lang="ts">
import { Table } from '@/types/table';
import { TableCellsIcon } from '@heroicons/vue/24/outline';
    import Nav from './partials/nav.vue';
import { Link } from '@inertiajs/vue3';

interface Props {
    tables: Table[];
    branchName: string;
}

const props = defineProps<Props>();
</script>
