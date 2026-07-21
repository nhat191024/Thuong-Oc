<template>
    <div class="flex h-screen flex-col">
        <div class="grid w-full grid-cols-[minmax(0,1fr)_auto_minmax(0,1fr)] items-center gap-4 bg-primary p-4">
            <div class="flex min-w-0 items-center gap-4">
                <button class="btn btn-circle btn-sm" @click="goBack">
                    <ArrowLeftIcon class="size-5" />
                </button>
                <p class="truncate text-lg font-bold text-white">{{ props.kitchen.name }}</p>
            </div>

            <div class="flex justify-center gap-2">
                <button class="btn btn-sm" :class="currentTab === 'active' ? 'btn-active' : 'text-white btn-ghost'" @click="currentTab = 'active'">
                    Đang thực hiện
                </button>
                <button class="btn btn-sm" :class="currentTab === 'history' ? 'btn-active' : 'text-white btn-ghost'" @click="currentTab = 'history'">
                    Lịch sử
                </button>
            </div>

            <div class="flex min-w-0 items-center justify-end gap-2">
                <label class="flex shrink-0 items-center gap-2 rounded-full bg-white px-3 py-1.5 text-primary shadow-sm">
                    <input
                        v-model="kitchenSettings.auto_print"
                        type="checkbox"
                        class="toggle toggle-primary toggle-sm"
                        :disabled="isSavingPrintSettings"
                        @change="savePrintSettings"
                    />
                    <span class="text-sm font-semibold whitespace-nowrap">Tự động in</span>
                </label>

                <select
                    v-model="kitchenSettings.printer_id"
                    class="select h-8 min-h-8 w-36 shrink-0 rounded-full border-0 bg-white px-3 text-sm font-semibold text-gray-900 shadow-sm focus:outline-none"
                    :disabled="isSavingPrintSettings"
                    @change="savePrintSettings"
                >
                    <option :value="null">Chưa chọn máy in</option>
                    <option v-for="printer in props.printers" :key="printer.id" :value="printer.id">
                        {{ printer.name }}
                    </option>
                </select>

                <div class="dropdown dropdown-end">
                    <div tabindex="0" role="button" class="btn m-1 rounded-4xl">{{ user.username }}</div>
                    <ul tabindex="-1" class="dropdown-content menu z-1 w-52 rounded-box bg-base-100 p-2 shadow-sm">
                        <li><Link :href="route('logout')" method="post" as="button">Đăng xuất</Link></li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="flex-1 overflow-hidden bg-gray-100">
            <ActiveOrders v-if="currentTab === 'active'" :bill-details="billDetails" :kitchen-id="props.kitchen.id" />
            <HistoryOrders v-else :kitchen-id="props.kitchen.id" />
        </div>
    </div>
</template>

<script setup lang="ts">
import { AppPageProps } from '@/types';
import { ArrowLeftIcon } from '@heroicons/vue/24/outline';
import type { PageProps } from '@inertiajs/core';
import { Link, usePage } from '@inertiajs/vue3';
import axios from 'axios';
import { onMounted, onUnmounted, ref, watch } from 'vue';
import { toast } from 'vue3-toastify';
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
    name: string;
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
    printer_id: number | null;
    auto_print: boolean;
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
const isSavingPrintSettings = ref(false);
const kitchenSettings = ref({
    printer_id: props.kitchen.printer_id,
    auto_print: props.kitchen.auto_print,
});

watch(
    () => props.billDetails,
    (newVal) => {
        billDetails.value = newVal;
    },
);

watch(
    () => page.props.flash.error,
    (message) => {
        if (message) {
            toast.error(message);
        }
    },
);

const goBack = () => {
    window.history.back();
};

const savePrintSettings = async () => {
    if (isSavingPrintSettings.value) {
        return;
    }

    isSavingPrintSettings.value = true;

    try {
        await axios.patch(route('kitchen.print-settings.update', { kitchen: props.kitchen.id }), {
            printer_id: kitchenSettings.value.printer_id,
            auto_print: kitchenSettings.value.auto_print,
        });

        toast.success('Đã lưu cài đặt in.');
    } catch (error) {
        toast.error(getErrorMessage(error));
    } finally {
        isSavingPrintSettings.value = false;
    }
};

const getErrorMessage = (error: unknown) => {
    if (axios.isAxiosError(error)) {
        const responseMessage = error.response?.data?.message;

        if (typeof responseMessage === 'string' && responseMessage.length > 0) {
            return responseMessage;
        }
    }

    return 'Không thể lưu cài đặt in.';
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

        (window as any).Echo.private(`kitchens.${props.kitchen.branch_id}`).listen('.bill.details.cancelled', (e: any) => {
            const cancelledBillDetailIds = new Set<number>(e.billDetailIds ?? []);
            billDetails.value = billDetails.value.filter((detail) => !cancelledBillDetailIds.has(detail.id));
        });

        (window as any).Echo.private(`kitchens.${props.kitchen.branch_id}`).listen('.bill.detail.quantity.reduced', (e: any) => {
            const billDetail = billDetails.value.find((detail) => detail.id === e.billDetailId);

            if (billDetail) {
                billDetail.quantity = e.quantity;
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
