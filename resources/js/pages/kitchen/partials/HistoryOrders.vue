<template>
    <div class="flex h-full flex-col gap-2">
        <div class="carousel w-full flex-1 space-x-4 rounded-box bg-neutral-100 p-4" ref="carouselRef" @scroll="handleScroll">
            <div v-for="(page, index) in pages" :key="index" class="carousel-item h-full w-full">
                <div class="grid h-full w-full grid-cols-3 grid-rows-3 gap-4 pl-4">
                    <OrderCard
                        v-for="detail in page"
                        :key="detail.id"
                        :detail="detail"
                        :show-actions="false"
                        class="h-full w-full"
                    />
                </div>
            </div>
            <div v-if="loading" class="carousel-item flex h-full w-full items-center justify-center">
                <span class="loading loading-md loading-spinner"></span>
            </div>
            <div v-if="!loading && historyDetails.length === 0" class="flex h-full w-full items-center justify-center">
                <p class="text-gray-500">Chưa có lịch sử đơn hàng</p>
            </div>
        </div>
        <div class="flex justify-center gap-2 mb-2" v-if="pages.length > 1">
            <template v-for="(p, index) in visiblePages" :key="index">
                <button
                    v-if="typeof p === 'number'"
                    class="btn btn-xs"
                    :class="currentPage === p ? 'btn-primary' : 'btn-ghost'"
                    @click="scrollToPage(p)"
                >
                    {{ p + 1 }}
                </button>
                <span v-else class="btn btn-xs btn-ghost btn-disabled">...</span>
            </template>
        </div>
    </div>
</template>

<script setup lang="ts">
import axios from 'axios';
import { computed, onMounted, ref } from 'vue';
import OrderCard from './OrderCard.vue';

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
    kitchenId: number;
}>();

const historyDetails = ref<BillDetail[]>([]);
const page = ref(1);
const loading = ref(false);
const hasMore = ref(true);
const carouselRef = ref<HTMLElement | null>(null);
const currentPage = ref(0);

const pages = computed(() => {
    const items = historyDetails.value;
    const chunkSize = 9;
    const result = [];
    for (let i = 0; i < items.length; i += chunkSize) {
        result.push(items.slice(i, i + chunkSize));
    }
    return result;
});

const visiblePages = computed(() => {
    const total = pages.value.length;
    const current = currentPage.value;

    if (total <= 7) {
        return Array.from({ length: total }, (_, i) => i);
    }

    const items: (number | string)[] = [0];

    if (current > 2) {
        items.push('...');
    }

    const start = Math.max(1, current - 1);
    const end = Math.min(total - 2, current + 1);

    if (current <= 2) {
        items.push(1, 2, 3);
        items.push('...');
    } else if (current >= total - 3) {
        items.push(total - 4, total - 3, total - 2);
    } else {
        for (let i = start; i <= end; i++) {
            items.push(i);
        }
        items.push('...');
    }

    items.push(total - 1);
    return [...new Set(items)];
});

const fetchHistory = async () => {
    if (loading.value || !hasMore.value) return;

    loading.value = true;
    try {
        const response = await axios.get(route('kitchen.history', { kitchen: props.kitchenId }), {
            params: { page: page.value },
        });

        const data = response.data;
        if (data.data.length > 0) {
            historyDetails.value.push(...data.data);
            page.value++;
        } else {
            hasMore.value = false;
        }

        if (data.next_page_url === null) {
            hasMore.value = false;
        }
    } catch (error) {
        console.error('Error fetching history:', error);
    } finally {
        loading.value = false;
    }
};

const handleScroll = () => {
    if (carouselRef.value) {
        const scrollLeft = carouselRef.value.scrollLeft;
        const clientWidth = carouselRef.value.clientWidth;
        const scrollWidth = carouselRef.value.scrollWidth;

        currentPage.value = Math.round(scrollLeft / clientWidth);

        if (scrollLeft + clientWidth >= scrollWidth - 50) {
            fetchHistory();
        }
    }
};

const scrollToPage = (index: number) => {
    if (carouselRef.value) {
        const clientWidth = carouselRef.value.clientWidth;
        carouselRef.value.scrollTo({
            left: index * clientWidth,
            behavior: 'smooth',
        });
    }
};

onMounted(() => {
    fetchHistory();
});
</script>
