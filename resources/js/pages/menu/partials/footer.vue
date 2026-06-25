<template>
    <div class="flex h-full justify-around bg-white py-1 shadow-[0_-4px_6px_-1px_rgba(159,7,18,0.1)]">
        <!-- TEMP: Cart disabled for direct-to-kitchen ordering.
        <div id="cart-icon" class="flex items-center justify-center" @click="showCart()">
            <ShoppingBagIcon class="h-10 text-primary" />
            <span
                v-if="totalDishes > 0"
                class="-ml-2 flex h-5 w-5 items-center justify-center self-start rounded-full bg-primary text-xs leading-none font-light text-white"
            >
                {{ totalDishes < 10 ? totalDishes : totalDishes }}
            </span>
        </div>
        -->
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
import { useHistoryStore } from '@/stores/history';
// TEMP: Cart disabled for direct-to-kitchen ordering.
// import { useOrderStore } from '@/stores/order';
import { ListBulletIcon } from '@heroicons/vue/24/solid';
// TEMP: Cart disabled for direct-to-kitchen ordering.
// import { ShoppingBagIcon } from '@heroicons/vue/24/solid';
import { computed } from 'vue';

interface Props {
    tableNumber: number;
}

defineProps<Props>();

// TEMP: Cart disabled for direct-to-kitchen ordering.
// const orderStore = useOrderStore();
const historyStore = useHistoryStore();

/*
 * TEMP: Cart disabled for direct-to-kitchen ordering.
 * const totalDishes = computed(() => {
 *     return orderStore.dishes.filter((dish: any) => dish.table === props.tableNumber).length;
 * });
 */

const totalHistory = computed(() => {
    return historyStore.dishes.length;
});

/*
 * TEMP: Cart disabled for direct-to-kitchen ordering.
 * function showCart() {
 *     const cart = document.getElementById('cart') as HTMLDialogElement;
 *     if (cart) {
 *         cart.showModal();
 *     }
 * }
 */
function showOrderHistory() {
    const history = document.getElementById('history') as HTMLDialogElement;
    if (history) {
        history.showModal();
    }
}
</script>

<style scoped>
/*
 * TEMP: Cart disabled for direct-to-kitchen ordering.
@keyframes cartBounce {
    0% {
        transform: scale(1);
    }
    40% {
        transform: scale(1.3);
    }
    70% {
        transform: scale(0.9);
    }
    100% {
        transform: scale(1);
    }
}

:deep(.cart-bounce) {
    animation: cartBounce 0.35s ease;
}
*/
</style>
