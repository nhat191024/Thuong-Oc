<template>
    <div class="card border border-base-200 bg-base-100 shadow-xl">
        <div class="card-body p-4">
            <h2 class="mb-4 card-title text-lg">Chi tiết đơn hàng</h2>

            <div class="mb-4 grid grid-cols-2 gap-2 rounded-lg bg-base-200/50 p-3 text-sm">
                <div class="text-base-content/70">Mã hóa đơn:</div>
                <div class="text-right font-medium">#{{ table.bill?.id }}</div>

                <div class="text-base-content/70">Thời gian vào:</div>
                <div class="text-right font-medium">{{ formatDate(table.bill?.time_in) }}</div>

                <template v-if="table.bill?.customer">
                    <div class="text-base-content/70">Khách hàng:</div>
                    <div class="text-right font-medium">{{ table.bill.customer.name }}</div>
                </template>
            </div>

            <div class="overflow-x-auto">
                <table class="table w-full table-zebra">
                    <thead>
                        <tr>
                            <th>Món</th>
                            <th class="text-center">SL</th>
                            <th class="text-right">Đơn giá</th>
                            <th class="text-right">Thành tiền</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(item, index) in billDetails" :key="index">
                            <td>
                                <div class="font-bold">{{ item.name }}</div>
                                <div v-if="item.cookingMethod" class="text-xs opacity-70">{{ item.cookingMethod }}</div>
                                <div v-if="item.note" class="text-xs italic opacity-60">{{ item.note }}</div>
                            </td>
                            <td class="text-center">{{ item.quantity }}</td>
                            <td class="text-right">{{ formatPrice(item.price) }}</td>
                            <td class="text-right font-semibold">{{ formatPrice(item.total) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="divider my-2"></div>

            <div v-if="discountAmount > 0" class="flex items-center justify-between text-sm">
                <span>Giảm giá ({{ discountPercent }}%):</span>
                <span class="text-error">-{{ formatPrice(discountAmount) }}</span>
            </div>

            <div class="flex items-center justify-between text-xl font-bold">
                <span>Tổng cộng:</span>
                <span class="text-primary">{{ formatPrice(finalTotal) }}</span>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';

interface BillItem {
    name: string;
    quantity: number;
    price: number;
    total: number;
    cookingMethod?: string;
    note?: string;
}

interface Props {
    table: {
        id: string;
        table_number: number;
        bill?: {
            id: number;
            time_in: string;
            user?: {
                name: string;
            };
            customer?: {
                name: string;
                phone: string;
                id: number;
            };
            customer_id?: number | null;
        };
    };
    billDetails: BillItem[];
    discountAmount: number;
    discountPercent: number;
    finalTotal: number;
}

const props = defineProps<Props>();

function formatPrice(price: number): string {
    return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(price);
}

function formatDate(dateString?: string): string {
    if (!dateString) return '---';
    return new Date(dateString).toLocaleString('vi-VN', {
        hour: '2-digit',
        minute: '2-digit',
        day: '2-digit',
        month: '2-digit',
    });
}
</script>
