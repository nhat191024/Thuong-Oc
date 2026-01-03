<template>
    <div class="flex h-screen flex-col">
        <Nav :center_text="props.kitchen.name" :use_back_button="true" />

        <div class="flex-1 overflow-hidden bg-gray-100">
            <div class="h-full overflow-y-auto p-4">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                    <div
                        v-for="detail in props.billDetails"
                        :key="detail.id"
                        class="flex flex-col rounded-xl p-4 shadow-sm transition-colors duration-300"
                        :class="getCardColorClass(detail.created_at)"
                    >
                        <div class="mb-2 flex items-center justify-between">
                            <span class="text-lg font-bold">Bàn {{ detail.bill.table.table_number }}</span>
                            <div class="flex flex-col items-end">
                                <span class="text-sm text-gray-500">{{ formatTime(detail.created_at) }}</span>
                                <span class="text-sm font-bold text-red-600">{{ getElapsedTime(detail.created_at) }}</span>
                            </div>
                        </div>
                        <div class="mb-1">
                            <h3 class="text-xl font-bold text-primary">{{ detail.dish.food.name }}</h3>
                            <p class="mt-1 text-sm text-red-500 italic">Ghi chú: {{ detail.note ?? 'Không có' }}</p>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-lg font-bold">SL: {{ detail.quantity }}</span>
                            <div class="flex gap-2">
                                <button
                                    class="rounded-lg bg-red-500 px-3 py-1 text-white hover:bg-red-600"
                                    @click="updateStatus(detail.id, 'cancelled')"
                                >
                                    Hủy
                                </button>
                                <button
                                    class="rounded-lg bg-green-500 px-3 py-1 text-white hover:bg-green-600"
                                    @click="updateStatus(detail.id, 'done')"
                                >
                                    Hoàn thành
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div v-if="props.billDetails.length === 0" class="flex h-full items-center justify-center">
                    <p class="text-gray-500">Không có món ăn nào cần chế biến</p>
                </div>
            </div>
        </div>

        <ConfirmModal
            v-model:isOpen="isConfirmModalOpen"
            title="Xác nhận hủy món"
            message="Bạn có chắc chắn muốn hủy món này không? Hành động này không thể hoàn tác."
            @confirm="handleConfirmCancel"
        />
    </div>
</template>

<script setup lang="ts">
import { format } from 'date-fns';
import { onMounted, onUnmounted, ref } from 'vue';
import Nav from '../components/nav.vue';
import { router } from '@inertiajs/vue3';
import ConfirmModal from '../components/ConfirmModal.vue';

interface Kitchen {
    id: number;
    name: string;
    branch_id: number;
}

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

interface Props {
    kitchen: Kitchen;
    billDetails: BillDetail[];
}

const props = defineProps<Props>();

const now = ref(new Date());
let timer: ReturnType<typeof setInterval>;

onMounted(() => {
    timer = setInterval(() => {
        now.value = new Date();
    }, 1000);
});

onUnmounted(() => {
    if (timer) clearInterval(timer);
});

const formatTime = (dateString: string) => {
    return format(new Date(dateString), 'HH:mm');
};

/**
 * Get elapsed time since dateString
 * @param dateString
 */
const getElapsedTime = (dateString: string) => {
    const start = new Date(dateString).getTime();
    const current = now.value.getTime();
    const diff = Math.max(0, current - start);

    const hours = Math.floor(diff / (1000 * 60 * 60));
    const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
    const seconds = Math.floor((diff % (1000 * 60)) / 1000);

    const pad = (num: number) => num.toString().padStart(2, '0');

    if (hours > 0) {
        return `${hours}:${pad(minutes)}:${pad(seconds)}`;
    }
    return `${pad(minutes)}:${pad(seconds)}`;
};

/**
 * Set card color based on elapsed time
 * @param dateString
 */
const getCardColorClass = (dateString: string) => {
    const start = new Date(dateString).getTime();
    const current = now.value.getTime();
    const diffInMinutes = (current - start) / (1000 * 60);

    if (diffInMinutes > 30) {
        return 'bg-red-100 border-2 border-red-300';
    } else if (diffInMinutes > 15) {
        return 'bg-yellow-100 border-2 border-yellow-300';
    }
    return 'bg-white';
};

const isConfirmModalOpen = ref(false);
const itemToCancel = ref<number | null>(null);

const updateStatus = (detailId: number, status: string) => {
    if (status === 'cancelled') {
        itemToCancel.value = detailId;
        isConfirmModalOpen.value = true;
        return;
    }

    submitUpdateStatus(detailId, status);
};

const handleConfirmCancel = () => {
    if (itemToCancel.value) {
        submitUpdateStatus(itemToCancel.value, 'cancelled');
        itemToCancel.value = null;
    }
};

const submitUpdateStatus = (detailId: number, status: string) => {
    router.post(route('kitchen.bill-detail.update-status', { billDetail: detailId }), {
        status: status,
    }, {
        preserveScroll: true,
    });
};
</script>
