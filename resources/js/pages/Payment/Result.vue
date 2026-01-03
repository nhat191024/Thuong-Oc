<template>
    <div class="flex h-screen flex-col">
        <div class="flex h-16 items-center justify-center bg-white shadow-sm">
            <h1 class="text-xl font-bold">Kết quả thanh toán</h1>
        </div>

        <div class="flex flex-1 flex-col items-center justify-center bg-gray-100 p-4">
            <div class="w-full max-w-md rounded-xl bg-white p-8 shadow-lg text-center">
                <div v-if="status === 'PAID' || status === '00'" class="mb-4">
                    <CheckCircleIcon class="mx-auto h-20 w-20 text-green-500" />
                    <h2 class="mt-4 text-2xl font-bold text-green-600">Thanh toán thành công!</h2>
                    <p class="mt-2 text-gray-600">Đơn hàng #{{ orderCode }} đã được thanh toán.</p>
                </div>
                <div v-else class="mb-4">
                    <XCircleIcon class="mx-auto h-20 w-20 text-red-500" />
                    <h2 class="mt-4 text-2xl font-bold text-red-600">Thanh toán thất bại</h2>
                    <p class="mt-2 text-gray-600">Đơn hàng #{{ orderCode }} chưa được thanh toán.</p>
                </div>

                <div class="mt-8">
                    <Link :href="route('staff.tables')" class="rounded-lg bg-primary px-6 py-3 text-white hover:bg-primary/90">
                        Về trang chủ
                    </Link>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { CheckCircleIcon, XCircleIcon } from '@heroicons/vue/24/solid';

defineProps<{
    orderCode: string | number;
    status: string;
}>();
</script>
