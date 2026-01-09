<template>
    <div class="min-h-screen bg-gray-100 pb-20">
        <Nav :tableNumber="0" />

        <div class="container mx-auto px-4 py-6">
            <Link :href="route('history.index')" class="mb-4 inline-flex items-center text-gray-600 hover:text-primary">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="mr-1 h-5 w-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                </svg>
                Quay lại
            </Link>

            <div class="rounded-lg bg-white p-6 shadow-lg">
                <div class="border-b pb-4">
                    <div class="flex items-center justify-between">
                        <h1 class="text-xl font-bold">Chi tiết đơn #{{ bill.id }}</h1>
                        <span :class="getStatusBadgeClass(bill.pay_status)" class="badge badge-lg">
                            {{ getStatusText(bill.pay_status) }}
                        </span>
                    </div>
                    <p class="mt-1 text-sm text-gray-500">{{ formatDate(bill.created_at) }}</p>
                    <div class="mt-2 flex gap-2">
                        <span class="badge badge-outline" v-if="bill.branch">{{ bill.branch.name }}</span>
                        <span class="badge badge-outline" v-if="bill.table">Bàn {{ bill.table.table_number }}</span>
                    </div>
                </div>

                <div class="mt-6">
                    <h2 class="mb-4 text-lg font-semibold">Món ăn</h2>
                    <div class="overflow-x-auto">
                        <table class="table w-full">
                            <!-- head -->
                            <thead>
                                <tr>
                                    <th>Món</th>
                                    <th class="text-center">SL</th>
                                    <th class="text-right">Đơn giá</th>
                                    <th class="text-right">Thành tiền</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="detail in bill.bill_details" :key="detail.id">
                                    <td>
                                        <div class="font-bold">
                                            {{ detail.dish?.food?.name || detail.custom_dish_name || 'Món không tên' }}
                                        </div>
                                        <div class="text-xs text-gray-500" v-if="detail.note">Ghi chú: {{ detail.note }}</div>
                                    </td>
                                    <td class="text-center">{{ detail.quantity }}</td>
                                    <td class="text-right">{{ formatCurrency(detail.price) }}</td>
                                    <td class="text-right font-medium">{{ formatCurrency(detail.price * detail.quantity) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="mt-6 border-t pt-4">
                    <div class="flex justify-between text-lg font-bold">
                        <span>Tổng cộng</span>
                        <span class="text-primary">{{ formatCurrency(bill.total) }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { Bill } from '@/types/bill';
import { Link } from '@inertiajs/vue3';
import Nav from '../menu/partials/nav.vue';

interface Props {
    bill: Bill;
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
</script>
