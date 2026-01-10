<template>
    <div class="min-h-screen bg-gray-100">
        <Nav :tableNumber="0" />

        <div class="container mx-auto px-4 py-6">
            <div class="mb-6 flex items-center gap-3">
                <button class="btn btn-circle btn-sm" @click="goBack">
                    <ArrowLeftIcon class="size-5" />
                </button>
                <h1 class="text-2xl font-bold text-gray-800">Kho Voucher</h1>
            </div>

            <div v-if="vouchers.length === 0" class="flex flex-col items-center justify-center rounded-lg bg-white p-8 shadow">
                <p class="text-gray-500">Bạn chưa có voucher nào.</p>
            </div>

            <div v-else class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                <div
                    v-for="voucher in vouchers"
                    :key="voucher.id"
                    class="card bg-base-100 shadow-xl transition-transform hover:scale-105"
                >
                    <div class="card-body">
                        <div class="flex items-center justify-between">
                            <h2 class="card-title text-lg text-primary">{{ voucher.code }}</h2>
                            <span class="badge badge-sm badge-success text-white" v-if="!voucher.expires_at || new Date(voucher.expires_at) > new Date()">
                                Khả dụng
                            </span>
                            <span class="badge badge-sm badge-error text-white" v-else>
                                Hết hạn
                            </span>
                        </div>

                        <p class="text-sm text-gray-500" v-if="voucher.expires_at">
                            Hết hạn: {{ formatDate(voucher.expires_at) }}
                        </p>
                        <p class="text-sm text-gray-500" v-else>
                            Hết hạn: Vĩnh viễn
                        </p>

                        <div class="mt-2" v-if="voucher.data">
                           <!-- Display discount info if available in data, assuming JSON structure/array -->
                           <!-- This is a guess based on typical voucher data structures -->
                           <span v-if="voucher.data.discount_percent">Giảm {{ voucher.data.discount_percent }}%</span>
                           <span v-else-if="voucher.data.discount_amount">Giảm {{ formatCurrency(voucher.data.discount_amount) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ArrowLeftIcon } from '@heroicons/vue/24/outline';
import { router } from '@inertiajs/vue3';
import Nav from '../menu/partials/nav.vue';

interface Voucher {
    id: number;
    code: string;
    data: any;
    expires_at: string | null;
    model_id: number;
}

interface Props {
    vouchers: Voucher[];
}

defineProps<Props>();

const formatDate = (dateString: string) => {
    if (!dateString) return '';
    return new Date(dateString).toLocaleString('vi-VN');
};

const formatCurrency = (value: number) => {
    return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(value);
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
