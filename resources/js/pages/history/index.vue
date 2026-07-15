<template>
    <div class="min-h-screen bg-gray-100">
        <StaffNav v-if="props.isStaff" center_text="Lịch sử đơn hàng" use_back_button :back-url="route('staff.tables')" />
        <MenuNav v-else :tableNumber="0" table-name="" />

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
                            <span :class="getStatusBadgeClass(bill.pay_status, bill.deleted_at)" class="badge badge-sm">
                                {{ getStatusText(bill.pay_status, bill.deleted_at) }}
                            </span>
                        </div>
                        <p class="text-sm text-gray-500">
                            {{ formatDate(bill.created_at) }}
                        </p>
                        <p v-if="bill.deleted_at" class="text-sm font-medium text-error">Thời gian xóa: {{ formatDate(bill.deleted_at) }}</p>
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

import StaffNav from '../components/nav.vue';
import MenuNav from '../menu/partials/nav.vue';

interface Props {
    bills: Bill[];
    isStaff: boolean;
}

const props = defineProps<Props>();

const formatDate = (dateString: string) => {
    if (!dateString) return '';
    return new Date(dateString).toLocaleString('vi-VN');
};

const formatCurrency = (value: number) => {
    return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(value);
};

const getStatusText = (status: string, deletedAt: string | null) => {
    if (deletedAt) {
        return 'Đã xóa';
    }

    switch (status) {
        case 'paid':
            return 'Đã thanh toán';
        case 'unpaid':
            return 'Chưa thanh toán';
        case 'cancelled':
            return 'Đã hủy';
        default:
            return status;
    }
};

const getStatusBadgeClass = (status: string, deletedAt: string | null) => {
    if (deletedAt) {
        return 'badge-error text-white';
    }

    switch (status) {
        case 'paid':
            return 'badge-success text-white';
        case 'unpaid':
            return 'badge-warning text-white';
        case 'cancelled':
            return 'badge-error text-white';
        default:
            return 'badge-ghost';
    }
};

const goBack = () => {
    if (props.isStaff) {
        router.visit(route('staff.tables'));

        return;
    }

    const tableId = localStorage.getItem('tableId');
    if (tableId) {
        router.visit(route('customer-menu.index', { tableId }));
    } else {
        window.history.back();
    }
};
</script>
