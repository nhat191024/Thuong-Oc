import { ref } from 'vue';
import { defineStore } from 'pinia';

import type { orderDish } from '@/types/order';

export const useHistoryStore = defineStore('history', () => {
    const dishes = ref<orderDish[]>(JSON.parse(localStorage.getItem('historyDishes') || '[]'));
    const totalPrice = ref<number>(JSON.parse(localStorage.getItem('historyTotalPrice') || '0'));

    function addHistory(dish: orderDish) {
        dishes.value.push(dish);
        totalPrice.value += dish.price * dish.quantity;

        localStorage.setItem('historyDishes', JSON.stringify(dishes.value));
        localStorage.setItem('historyTotalPrice', JSON.stringify(totalPrice.value));
    }

    function updateHistory(index: number, quantity: number) {
        dishes.value[index].quantity = quantity;
        totalPrice.value = dishes.value.reduce((acc, dish) => acc + dish.price * dish.quantity, 0);

        localStorage.setItem('historyDishes', JSON.stringify(dishes.value));
        localStorage.setItem('historyTotalPrice', JSON.stringify(totalPrice.value));
    }

    function clearHistory() {
        dishes.value.splice(0, dishes.value.length);
        totalPrice.value = 0;

        localStorage.setItem('historyDishes', JSON.stringify(dishes.value));
        localStorage.setItem('historyTotalPrice', JSON.stringify(totalPrice.value));
    }

    function getTotalPrice() {
        return totalPrice.value;
    }

    return {
        dishes,
        totalPrice,
        addHistory,
        updateHistory,
        clearHistory,
        getTotalPrice
    }
});

