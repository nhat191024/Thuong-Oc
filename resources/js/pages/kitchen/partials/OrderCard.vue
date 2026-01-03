<template>
    <div
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
        <div class="mb-1 flex-1">
            <h3 class="text-xl font-bold text-primary">{{ detail.dish.food.name }}</h3>
            <p class="mt-1 text-sm text-red-500 italic">Ghi chú: {{ detail.note ?? 'Không có' }}</p>
        </div>
        <div class="flex items-end justify-between">
            <span class="text-lg font-bold">SL: {{ detail.quantity }}</span>
            <div class="flex gap-2" v-if="showActions">
                <button
                    class="rounded-lg bg-red-500 px-3 py-1 text-white hover:bg-red-600"
                    @click="$emit('updateStatus', detail.id, 'cancelled')"
                >
                    Hủy
                </button>
                <button
                    class="rounded-lg bg-green-500 px-3 py-1 text-white hover:bg-green-600"
                    @click="$emit('updateStatus', detail.id, 'done')"
                >
                    Hoàn thành
                </button>
            </div>
            <div v-else>
                <span
                    class="rounded-full px-3 py-1 text-xs font-bold"
                    :class="{
                        'bg-green-100 text-green-800': detail.status === 'done',
                        'bg-red-100 text-red-800': detail.status === 'cancelled',
                    }"
                >
                    {{ detail.status === 'done' ? 'Đã hoàn thành' : 'Đã hủy' }}
                </span>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { format } from 'date-fns';
import { ref, onMounted, onUnmounted } from 'vue';

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

const props = defineProps<{
    detail: BillDetail;
    showActions?: boolean;
}>();

defineEmits(['updateStatus']);

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

const getCardColorClass = (dateString: string) => {
    if (!props.showActions) return 'bg-white border border-gray-200';

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
</script>
