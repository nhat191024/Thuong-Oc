<template>
    <div class="flex h-full flex-col gap-2">
        <div class="carousel w-full flex-1 space-x-4 rounded-box bg-neutral-100 p-4" ref="carouselRef" @scroll="handleScroll">
            <div v-for="(page, index) in pages" :key="index" class="carousel-item h-full w-full">
                <div class="grid h-full w-full grid-cols-3 grid-rows-3 gap-4 pl-4">
                    <OrderCard
                        v-for="detail in page"
                        :key="detail.id"
                        :detail="detail"
                        :show-actions="true"
                        class="h-full w-full"
                        @update-status="updateStatus"
                    />
                </div>
            </div>
            <div v-if="billDetails.length === 0" class="flex h-full w-full items-center justify-center">
                <p class="text-gray-500">Không có món ăn nào cần chế biến</p>
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

    <ConfirmModal
        v-model:isOpen="isConfirmModalOpen"
        :title="modalTitle"
        :message="modalMessage"
        @confirm="handleConfirmAction"
    />
</template>

<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import ConfirmModal from '../../components/ConfirmModal.vue';
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
    billDetails: BillDetail[];
}>();

const carouselRef = ref<HTMLElement | null>(null);
const currentPage = ref(0);

const pages = computed(() => {
    const items = props.billDetails;
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

const handleScroll = () => {
    if (carouselRef.value) {
        const scrollLeft = carouselRef.value.scrollLeft;
        const clientWidth = carouselRef.value.clientWidth;
        currentPage.value = Math.round(scrollLeft / clientWidth);
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

const isConfirmModalOpen = ref(false);
const itemToProcess = ref<number | null>(null);
const actionStatus = ref<string>('');
const modalTitle = ref('');
const modalMessage = ref('');

const updateStatus = (detailId: number, status: string) => {
    if (status === 'cancelled') {
        itemToProcess.value = detailId;
        actionStatus.value = status;
        modalTitle.value = 'Xác nhận hủy món';
        modalMessage.value = 'Bạn có chắc chắn muốn hủy món này không? Hành động này không thể hoàn tác.';
        isConfirmModalOpen.value = true;
    } else {
        submitUpdateStatus(detailId, status);
    }
};

const handleConfirmAction = () => {
    if (itemToProcess.value && actionStatus.value) {
        submitUpdateStatus(itemToProcess.value, actionStatus.value);
        itemToProcess.value = null;
        actionStatus.value = '';
    }
};

const submitUpdateStatus = (detailId: number, status: string) => {
    router.post(
        route('kitchen.bill-detail.update-status', { billDetail: detailId }),
        {
            status: status,
        },
        {
            preserveScroll: true,
        },
    );
};
</script>
