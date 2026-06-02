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

            <div class="flex items-center gap-3">
                <div v-if="props.printers.length > 0" class="flex items-center gap-2">
                    <PrinterIcon class="size-5 text-white" />
                    <select
                        v-model="selectedPrinterId"
                        class="select select-sm bg-white/10 text-white border-white/30 focus:outline-none"
                        @change="savePrinterSelection"
                    >
                        <option :value="null" class="text-gray-800">Không in</option>
                        <option v-for="p in props.printers" :key="p.id" :value="p.id" class="text-gray-800">
                            {{ p.name }}
                        </option>
                    </select>
                </div>

                <div class="dropdown dropdown-end">
                    <div tabindex="0" role="button" class="btn m-1 rounded-4xl">{{ user.username }}</div>
                    <ul tabindex="-1" class="dropdown-content menu z-1 w-52 rounded-box bg-base-100 p-2 shadow-sm">
                        <li><Link :href="route('logout')" method="post" as="button">Đăng xuất</Link></li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="flex-1 overflow-hidden bg-gray-100">
            <ActiveOrders v-if="currentTab === 'active'" :bill-details="billDetails" :selected-printer-id="selectedPrinterId" />
            <HistoryOrders v-else :kitchen-id="props.kitchen.id" />
        </div>
    </div>
</template>

<script setup lang="ts">
import { AppPageProps } from '@/types';
import { ArrowLeftIcon, PrinterIcon } from '@heroicons/vue/24/outline';
import { Link, usePage } from '@inertiajs/vue3';
import type { PageProps } from '@inertiajs/core';
import { onMounted, onUnmounted, ref, watch } from 'vue';
import ActiveOrders from './partials/ActiveOrders.vue';
import HistoryOrders from './partials/HistoryOrders.vue';

interface Food {
    id: number;
    name: string;
}

interface Dish {
    id: number;
    food: Food;
    cooking_method_id?: number;
    cooking_method?: { id: number; name: string } | null;
}

interface Table {
    id: number;
    table_number: string;
}

interface Bill {
    id: number;
    table: Table;
    branch_id: number;
}

interface BillDetail {
    id: number;
    quantity: number;
    note: string | null;
    status: string;
    created_at: string;
    dish: Dish;
    bill: Bill;
    custom_kitchen_id?: number | null;
    custom_dish_name: string | null;
}

interface Kitchen {
    id: number;
    name: string;
    branch_id: number;
}

interface Printer {
    id: number;
    name: string;
}

interface Props {
    kitchen: Kitchen;
    billDetails: BillDetail[];
    cookingMethodIds: number[];
    printers: Printer[];
}

const props = defineProps<Props>();
const page = usePage<PageProps & AppPageProps>();
const user = page.props.auth.user;

const currentTab = ref('active');
const billDetails = ref(props.billDetails);

const localStorageKey = `kitchen_printer_${props.kitchen.id}`;
const savedPrinterId = typeof window !== 'undefined' ? localStorage.getItem(localStorageKey) : null;
const selectedPrinterId = ref<number | null>(savedPrinterId ? parseInt(savedPrinterId) : null);

const savePrinterSelection = () => {
    if (selectedPrinterId.value) {
        localStorage.setItem(localStorageKey, String(selectedPrinterId.value));
    } else {
        localStorage.removeItem(localStorageKey);
    }
};

watch(
    () => props.billDetails,
    (newVal) => {
        billDetails.value = newVal;
    },
);

const goBack = () => {
    window.history.back();
};

onMounted(() => {
    if ((window as any).Echo) {
        console.log('Echo initialized, subscribing to channel kitchens.' + props.kitchen.branch_id);
        (window as any).Echo.private(`kitchens.${props.kitchen.branch_id}`).listen('.new.dish.ordered', (e: any) => {
            console.log('Event received', e);
            const newBillDetail: BillDetail = e.billDetail;

            // Logic to determine if this kitchen should handle the dish
            const belongsToKitchen =
                (newBillDetail.dish && props.cookingMethodIds.includes(newBillDetail.dish.cooking_method_id!)) ||
                newBillDetail.custom_kitchen_id === props.kitchen.id;

            if (belongsToKitchen) {
                billDetails.value.push(newBillDetail);
            }
        });
    } else {
        console.error('Echo is NOT initialized');
    }
});

onUnmounted(() => {
    if ((window as any).Echo) {
        (window as any).Echo.leave(`kitchens.${props.kitchen.branch_id}`);
    }
});
</script>
