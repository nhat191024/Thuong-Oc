<template>
    <dialog id="history" class="modal modal-bottom">
        <form method="dialog" class="modal-backdrop">
            <button>close</button>
        </form>
        <div class="modal-box h-4/5 p-0">
            <form method="dialog">
                <button class="btn absolute top-2 right-2 btn-circle btn-ghost btn-sm">✕</button>
            </form>
            <div class="grid h-full grid-rows-10">
                <h3 class="row-start-1 place-self-center pt-2 text-center text-xl font-bold">Lịch sử gọi món</h3>
                <div class="row-span-full row-start-2 overflow-auto">
                    <div v-for="(dish, index) in historyStore.dishes" :key="index" class="my-2 flex flex-col border-b px-4 py-2">
                        <div class="flex justify-between text-lg font-medium">
                            <span>{{ dish.name }}</span>
                            <span class="text-primary">{{ formatPrice(dish.price * dish.quantity) }}</span>
                        </div>
                        <div class="flex justify-between text-sm text-gray-500">
                            <span>{{ dish.cookingMethod }}</span>
                            <span>x{{ dish.quantity }}</span>
                        </div>
                        <div v-if="dish.note" class="text-sm italic text-gray-400">
                            Ghi chú: {{ dish.note }}
                        </div>
                    </div>
                    <div v-if="historyStore.dishes.length === 0" class="flex h-full items-center justify-center text-gray-400">
                        Chưa có lịch sử gọi món
                    </div>
                </div>
                <div class="py-2">
                    <div class="flex justify-between px-4 py-2 text-lg">
                        <p>Tổng cộng :</p>
                        <p class="font-medium text-primary">{{ formatPrice(historyStore.totalPrice) }}</p>
                    </div>
                </div>
            </div>
        </div>
    </dialog>
</template>
<script setup lang="ts">
import { useHistoryStore } from '@/stores/history';

const historyStore = useHistoryStore();

function formatPrice(price: number): string {
    return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(price);
}
</script>
