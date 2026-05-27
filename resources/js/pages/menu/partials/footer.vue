<template>
    <div class="flex py-1 h-full justify-around bg-white shadow-[0_-4px_6px_-1px_rgba(159,7,18,0.1)]">
        <div id="cart-icon" class="flex items-center justify-center" @click="showCart()">
            <ShoppingBagIcon class="h-10 text-primary" />
            <span
                v-if="totalDishes > 0"
                class="-ml-2 flex h-5 w-5 items-center justify-center self-start rounded-full bg-primary text-xs leading-none font-light text-white"
            >
                {{ totalDishes < 10 ? totalDishes : totalDishes }}
            </span>
        </div>
        <div class="flex items-center justify-center" @click="showOrderHistory()">
            <ListBulletIcon class="h-10 text-primary" />
            <span
                v-if="totalHistory > 0"
                class="-ml-2 flex h-5 w-5 items-center justify-center self-start rounded-full bg-primary text-xs leading-none font-light text-white"
            >
                {{ totalHistory < 10 ? totalHistory : totalHistory }}
            </span>
        </div>
    </div>
</template>
<script setup lang="ts">
import { useOrderStore } from '@/stores/order';
import { useHistoryStore } from '@/stores/history';
import { ListBulletIcon, ShoppingBagIcon } from '@heroicons/vue/24/solid';
import { computed } from 'vue';

interface Props {
    tableNumber: number;
}

const props = defineProps<Props>();

const orderStore = useOrderStore();
const historyStore = useHistoryStore();

const totalDishes = computed(() => {
    return orderStore.dishes.filter((dish: any) => dish.table === props.tableNumber).length;
});

const totalHistory = computed(() => {
    return historyStore.dishes.length;
});

function showCart() {
    const cart = document.getElementById('cart') as HTMLDialogElement;
    if (cart) {
        cart.showModal();
    }
}
function showOrderHistory() {
    const history = document.getElementById('history') as HTMLDialogElement;
    if (history) {
        history.showModal();
    }
}
</script>

<style scoped>
@keyframes cartBounce {
    0% { transform: scale(1); }
    40% { transform: scale(1.3); }
    70% { transform: scale(0.9); }
    100% { transform: scale(1); }
}

:deep(.cart-bounce) {
    animation: cartBounce 0.35s ease;
}
</style>
