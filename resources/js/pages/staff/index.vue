<template>
    <div class="flex h-screen flex-col">
        <Nav :center_text="'Chi nhánh ' + props.branchName">
            <template #actions>
                <div class="dropdown dropdown-end">
                    <button tabindex="0" type="button" class="btn rounded-4xl bg-base-100 text-primary btn-sm hover:bg-base-200">
                        <Squares2X2Icon class="size-4" />
                        Quản lý
                        <span v-if="props.redInventoryCount > 0" class="badge badge-sm text-white badge-error">
                            <ExclamationTriangleIcon class="size-3.5" />
                            {{ props.redInventoryCount }}
                        </span>
                        <ChevronDownIcon class="size-4" />
                    </button>
                    <ul tabindex="-1" class="dropdown-content menu z-50 mt-2 w-56 rounded-box bg-base-100 p-2 text-base-content shadow-lg">
                        <li><Link :href="route('history.index')">Lịch sử đơn</Link></li>
                        <li><Link :href="route('staff.stock.index')">Quản lý hết món</Link></li>
                        <li>
                            <Link :href="route('staff.inventory.index')">
                                Kho ốc
                                <span v-if="props.redInventoryCount > 0" class="ml-auto badge badge-sm text-white badge-error">
                                    <ExclamationTriangleIcon class="size-3.5" />
                                    {{ props.redInventoryCount }}
                                </span>
                            </Link>
                        </li>
                    </ul>
                </div>
            </template>
        </Nav>

        <div class="flex-1 overflow-hidden bg-base-200">
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
                                : 'border-base-300 bg-base-100 text-base-content/50 hover:border-primary hover:text-primary hover:shadow-md',
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
import { ChevronDownIcon, ExclamationTriangleIcon, Squares2X2Icon, TableCellsIcon } from '@heroicons/vue/24/outline';
import type { PageProps } from '@inertiajs/core';
import { Link, usePage } from '@inertiajs/vue3';
import Nav from '../components/nav.vue';

interface Props {
    tables: Table[];
    branchName: string;
    redInventoryCount: number;
}

const props = defineProps<Props>();
const page = usePage<PageProps & AppPageProps>();
</script>
