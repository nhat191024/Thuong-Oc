<template>
    <div class="relative h-full w-full overflow-hidden rounded-xl" ref="target">
        <!-- Swipe Background -->
        <div class="absolute inset-0 flex items-center justify-end bg-green-500 pr-8">
            <span class="text-lg font-bold text-white">Hoàn thành</span>
        </div>

        <div
            class="relative flex h-full flex-col bg-white p-4 shadow-sm transition-all duration-200 ease-out"
            :class="getCardColorClass(detail.created_at)"
            :style="cardStyle"
        >
            <div class="mb-2 flex items-center justify-between">
                <span class="text-lg font-bold">Bàn {{ detail.bill.table.table_number }}</span>
                <div class="flex flex-col items-end">
                    <span class="text-sm text-gray-500">{{ formatTime(detail.created_at) }}</span>
                    <span v-if="showActions" class="text-sm font-bold text-red-600">{{ getElapsedTime(detail.created_at) }}</span>
                </div>
            </div>
            <div class="mb-1 min-h-0 flex-1 overflow-y-auto">
                <h3 class="text-xl font-bold text-primary">{{ detail.dish ? detail.dish.food.name : detail.custom_dish_name }}</h3>
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
                    <button class="rounded-lg bg-green-500 px-3 py-1 text-white hover:bg-green-600" @click="$emit('updateStatus', detail.id, 'done')">
                        Hoàn thành
                    </button>
                </div>
                <div v-else class="flex flex-col items-end gap-1">
                    <span
                        class="rounded-full px-3 py-1 text-xs font-bold"
                        :class="{
                            'bg-green-100 text-green-800': detail.status === 'done',
                            'bg-red-100 text-red-800': detail.status === 'cancelled',
                        }"
                    >
                        {{ detail.status === 'done' ? 'Đã hoàn thành' : 'Đã hủy' }}
                    </span>
                    <button
                        v-if="showRestore"
                        class="rounded-lg bg-blue-500 px-3 py-1 text-xs text-white hover:bg-blue-600"
                        @click="$emit('restore', detail.id)"
                    >
                        Khôi phục
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { useSwipe } from '@vueuse/core';
import { format } from 'date-fns';
import { computed, onMounted, onUnmounted, ref } from 'vue';

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
    dish: Dish | null;
    custom_dish_name: string | null;
    bill: Bill;
}

const props = defineProps<{
    detail: BillDetail;
    showActions?: boolean;
    showRestore?: boolean;
}>();

const emit = defineEmits(['updateStatus', 'restore']);

const target = ref<HTMLElement | null>(null);

const { lengthX, isSwiping } = useSwipe(target, {
    threshold: 10,
    onSwipeEnd() {
        if (props.showActions && lengthX.value > 100) {
            emit('updateStatus', props.detail.id, 'done');
        }
    },
});

const cardStyle = computed(() => {
    if (!props.showActions) return {};

    if (isSwiping.value) {
        // Swipe left means lengthX is positive
        const translateX = -Math.min(lengthX.value, 200);
        if (lengthX.value < 0) return {}; // Prevent swiping right

        return {
            transform: `translateX(${translateX}px)`,
            opacity: 1 - lengthX.value / 300,
        };
    }

    return {};
});

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
