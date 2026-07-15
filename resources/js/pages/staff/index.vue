<template>
    <div class="flex h-screen flex-col">
        <Nav :center_text="'Chi nhánh ' + props.branchName">
            <template #actions>
                <Link :href="route('history.index')" class="btn rounded-4xl bg-white text-primary btn-sm hover:bg-white/90"> Lịch sử đơn </Link>
                <Link :href="route('staff.stock.index')" class="btn rounded-4xl bg-white text-primary btn-sm hover:bg-white/90">
                    Quản lý tồn kho
                </Link>
            </template>
        </Nav>

        <div class="flex-1 overflow-hidden bg-gray-100">
            <div v-if="page.props.flash.success" role="alert" class="mx-4 mt-4 alert alert-success">
                <span>{{ page.props.flash.success }}</span>
            </div>
            <div class="h-full overflow-x-auto p-4">
                <div class="grid h-full min-w-max grid-flow-col grid-rows-2 gap-4">
                    <Link
                        v-for="table in props.tables"
                        :key="table.id"
                        class="flex w-64 cursor-pointer flex-col items-center justify-center rounded-xl border-2 p-4 shadow-sm transition-all duration-200"
                        :class="[
                            table.is_active === 'active'
                                ? 'border-primary bg-primary text-white shadow-lg shadow-primary/30'
                                : 'border-gray-200 bg-white text-gray-400 hover:border-primary hover:text-primary hover:shadow-md',
                        ]"
                        :href="route('staff.table.show', { tableId: table.id })"
                    >
                        <TableCellsIcon class="mb-4 h-16 w-16" />
                        <span class="text-2xl font-bold">{{ table.name }}</span>
                        <span class="mt-2 text-sm">
                            {{ table.is_active === 'active' ? 'Hoạt động' : 'Không hoạt động' }}
                        </span>
                    </Link>
                </div>
            </div>
        </div>
    </div>
</template>
<script setup lang="ts">
import { AppPageProps } from '@/types';
import { Table } from '@/types/table';
import { TableCellsIcon } from '@heroicons/vue/24/outline';
import type { PageProps } from '@inertiajs/core';
import { Link, usePage } from '@inertiajs/vue3';
import Nav from '../components/nav.vue';

interface Props {
    tables: Table[];
    branchName: string;
}

const props = defineProps<Props>();
const page = usePage<PageProps & AppPageProps>();
</script>
