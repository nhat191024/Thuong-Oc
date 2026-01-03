<template>
    <div class="flex h-screen flex-col">
        <div class="relative flex w-full items-center justify-between bg-primary p-4">
            <div class="flex items-center gap-4">
                <button class="btn btn-circle btn-sm" @click="goBack">
                    <ArrowLeftIcon class="size-5" />
                </button>
                <p class="text-lg font-bold text-white">{{ props.kitchen.name }}</p>
            </div>

            <div class="flex gap-2">
                <button class="btn btn-sm" :class="currentTab === 'active' ? 'btn-active' : 'text-white btn-ghost'" @click="currentTab = 'active'">
                    Đang thực hiện
                </button>
                <button class="btn btn-sm" :class="currentTab === 'history' ? 'btn-active' : 'text-white btn-ghost'" @click="currentTab = 'history'">
                    Lịch sử
                </button>
            </div>

            <div class="dropdown dropdown-end">
                <div tabindex="0" role="button" class="btn m-1 rounded-4xl">{{ user.username }}</div>
                <ul tabindex="-1" class="dropdown-content menu z-1 w-52 rounded-box bg-base-100 p-2 shadow-sm">
                    <li><Link :href="route('logout')" method="post" as="button">Đăng xuất</Link></li>
                </ul>
            </div>
        </div>

        <div class="flex-1 overflow-hidden bg-gray-100">
            <ActiveOrders v-if="currentTab === 'active'" :bill-details="props.billDetails" />
            <HistoryOrders v-else :kitchen-id="props.kitchen.id" />
        </div>
    </div>
</template>

<script setup lang="ts">
import { AppPageProps } from '@/types';
import { ArrowLeftIcon } from '@heroicons/vue/24/outline';
import { Link, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';
import ActiveOrders from './partials/ActiveOrders.vue';
import HistoryOrders from './partials/HistoryOrders.vue';

interface Food {
    id: number;
    name: string;
}

interface Dish {
    id: number;
    food: Food;
}

interface Table {
    id: number;
    table_number: string;
}

interface Bill {
    id: number;
    table: Table;
}

interface BillDetail {
    id: number;
    quantity: number;
    note: string | null;
    status: string;
    created_at: string;
    dish: Dish;
    bill: Bill;
}

interface Kitchen {
    id: number;
    name: string;
    branch_id: number;
}

interface Props {
    kitchen: Kitchen;
    billDetails: BillDetail[];
}

const props = defineProps<Props>();
const page = usePage<AppPageProps>();
const user = page.props.auth.user;

const currentTab = ref('active');

const goBack = () => {
    window.history.back();
};
</script>
