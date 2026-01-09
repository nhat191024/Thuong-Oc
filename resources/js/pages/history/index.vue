<template>
    <div class="min-h-screen bg-gray-100">
        <Nav :tableNumber="0" />

        <div class="container mx-auto px-4 py-6">
            <div class="mb-6 flex items-center gap-3">
                <button class="btn btn-circle btn-sm" @click="goBack">
                    <ArrowLeftIcon class="size-5" />
                </button>
                <h1 class="text-2xl font-bold text-gray-800">Lịch sử đơn hàng</h1>
            </div>

            <div v-if="bills.length === 0" class="flex flex-col items-center justify-center rounded-lg bg-white p-8 shadow">
                <p class="text-gray-500">Bạn chưa có đơn hàng nào.</p>
            </div>

            <div v-else class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                <Link
                    v-for="bill in bills"
                    :key="bill.id"
                    :href="route('history.show', { id: bill.id })"
                    class="card bg-base-100 shadow-xl transition-transform hover:scale-105"
                >
                    <div class="card-body">
                        <div class="flex items-center justify-between">
                            <h2 class="card-title text-sm">Đơn #{{ bill.id }}</h2>
                            <span :class="getStatusBadgeClass(bill.pay_status)" class="badge badge-sm">
                                {{ getStatusText(bill.pay_status) }}
                            </span>
                        </div>
                        <p class="text-sm text-gray-500">
                            {{ formatDate(bill.created_at) }}
                        </p>
                        <div class="mt-2 flex items-center justify-between font-semibold">
                            <span>Tổng tiền:</span>
                            <span class="text-primary">{{ formatCurrency(bill.total) }}</span>
                        </div>
                        <div class="mt-2 card-actions justify-end">
                            <div class="badge badge-outline" v-if="bill.branch">{{ bill.branch.name }}</div>
                            <div class="badge badge-outline" v-if="bill.table">Bàn {{ bill.table.table_number }}</div>
                        </div>
                    </div>
                </Link>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { Bill } from '@/types/bill';
import { ArrowLeftIcon } from '@heroicons/vue/24/outline';
import { Link, router } from '@inertiajs/vue3';

import Nav from '../menu/partials/nav.vue';

interface Props {
    bills: Bill[];
}

const props = defineProps<Props>();

const formatDate = (dateString: string) => {
    if (!dateString) return '';
    return new Date(dateString).toLocaleString('vi-VN');
};

const formatCurrency = (value: number) => {
    return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(value);
};

const getStatusText = (status: string) => {
    switch (status) {
        case 'PAID':
            return 'Đã thanh toán';
        case 'UNPAID':
            return 'Chưa thanh toán';
        default:
            return status;
    }
};

const getStatusBadgeClass = (status: string) => {
    switch (status) {
        case 'PAID':
            return 'badge-success text-white';
        case 'UNPAID':
            return 'badge-warning text-white';
        default:
            return 'badge-ghost';
    }
};

const goBack = () => {
    let tableId = localStorage.getItem('tableId');
    if (tableId) {
        router.visit(route('customer-menu.index', { tableId }));
    } else {
        window.history.back();
    }
};
</script>
