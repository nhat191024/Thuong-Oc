import { ref } from 'vue';
import { defineStore } from 'pinia';

import type { orderDish } from '@/types/order';

const isClient = typeof window !== 'undefined';

export const useOrderStore = defineStore('order', () => {
    const dishes = ref<orderDish[]>(isClient ? JSON.parse(localStorage.getItem('dishes') || '[]') : []);
    const totalPrice = ref<number>(isClient ? JSON.parse(localStorage.getItem('totalPrice') || '0') : 0);

    function addDish(dish: orderDish) {
        dishes.value.push(dish);
        totalPrice.value += dish.price * dish.quantity;

        if (isClient) {
            localStorage.setItem('dishes', JSON.stringify(dishes.value));
            localStorage.setItem('totalPrice', JSON.stringify(totalPrice.value));
        }
    }

    function removeDish(index: number) {
        dishes.value.splice(index, 1);
        totalPrice.value = dishes.value.reduce((acc, dish) => acc + dish.price * dish.quantity, 0);

        if (isClient) {
            localStorage.setItem('dishes', JSON.stringify(dishes.value));
            localStorage.setItem('totalPrice', JSON.stringify(totalPrice.value));
        }
    }

    function updateDishQuantity(index: number, quantity: number) {
        dishes.value[index].quantity = quantity;
        totalPrice.value = dishes.value.reduce((acc, dish) => acc + dish.price * dish.quantity, 0);

        if (isClient) {
            localStorage.setItem('dishes', JSON.stringify(dishes.value));
            localStorage.setItem('totalPrice', JSON.stringify(totalPrice.value));
        }
    }

    function clearDishes() {
        dishes.value.splice(0, dishes.value.length);
        totalPrice.value = 0;

        if (isClient) {
            localStorage.setItem('dishes', JSON.stringify(dishes.value));
            localStorage.setItem('totalPrice', JSON.stringify(totalPrice.value));
        }
    }

    function getTotalPrice() {
        return totalPrice.value;
    }

    return {
        dishes,
        totalPrice,
        addDish,
        updateDishQuantity,
        removeDish,
        clearDishes,
        getTotalPrice
    }
});

