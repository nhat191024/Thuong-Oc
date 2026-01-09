<template>
    <dialog id="cart" class="modal modal-bottom sm:modal-middle">
        <form method="dialog" class="modal-backdrop">
            <button>close</button>
        </form>
        <div class="modal-box flex h-[90vh] flex-col bg-base-100 p-0 sm:h-auto sm:max-h-[80vh]">
            <!-- Header -->
            <div class="sticky top-0 z-10 flex items-center justify-between border-b border-base-200 bg-base-100 p-4">
                <button v-if="billTemp.length > 0" class="text-sm font-normal text-primary" @click="clearCart">Xóa tất cả</button>

                <h3 class="text-center text-lg font-bold">Giỏ hàng</h3>

                <form method="dialog" class="btn btn-circle btn-ghost">
                    <button class="">✕</button>
                </form>
            </div>

            <!-- Content -->
            <div class="flex-1 overflow-y-auto p-4">
                <div v-if="billTemp.length === 0" class="flex h-full flex-col items-center justify-center py-10 text-base-content/50">
                    <ShoppingCartIcon class="mb-4 h-20 w-20 opacity-20" />
                    <p class="text-lg font-medium">Chưa có món nào</p>
                    <p class="text-sm">Vui lòng chọn món từ thực đơn</p>
                </div>

                <!-- List -->
                <div v-else class="space-y-4">
                    <div v-for="(dish, index) in billTemp" :key="index" class="flex gap-3 bg-base-100">
                        <img src="/images/demo.jpg" alt="demo" class="h-24 w-24 shrink-0 rounded-xl object-cover" />

                        <div class="flex flex-1 flex-col justify-between py-1">
                            <div>
                                <div class="flex items-start justify-between gap-2">
                                    <h4 class="line-clamp-2 text-base font-bold">{{ dish.name }}</h4>
                                    <p class="font-bold whitespace-nowrap text-primary">{{ formatPrice(dish.price) }}</p>
                                </div>
                                <p class="line-clamp-1 text-sm text-gray-500">{{ dish.cookingMethod }}</p>
                            </div>

                            <div class="mt-2 flex items-end justify-between gap-2">
                                <input class="input-bordered input input-sm w-full max-w-35 text-sm" placeholder="Ghi chú..." v-model="dish.note" />

                                <div class="join h-8 items-center rounded-lg border border-base-300">
                                    <button
                                        @click="dishDelete(dish.dishId, index)"
                                        class="btn h-full rounded-none rounded-l-lg px-2 btn-ghost btn-xs hover:bg-base-200"
                                    >
                                        <MinusIcon class="size-4" />
                                    </button>
                                    <span class="w-8 text-center text-sm font-medium">{{ dish.quantity }}</span>
                                    <button
                                        @click="dishIncrease(index)"
                                        class="btn h-full rounded-none rounded-r-lg px-2 btn-ghost btn-xs hover:bg-base-200"
                                    >
                                        <PlusIcon class="size-4" />
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="safe-area-bottom border-t border-base-200 bg-base-100 p-4">
                <div class="mb-4 flex items-center justify-between">
                    <span class="text-base font-medium text-gray-500">Tổng cộng</span>
                    <span class="text-2xl font-bold text-primary">{{ formatPrice(total) }}</span>
                </div>
                <button
                    class="btn w-full bg-primary text-lg text-white shadow-lg shadow-primary/30"
                    @click="confirmOrder"
                    :disabled="billTemp.length < 1"
                >
                    Đặt món
                </button>
            </div>
        </div>
    </dialog>

    <!-- confirm modal -->
    <dialog id="confirmOrder" class="modal">
        <div class="modal-box">
            <form method="dialog">
                <button class="btn absolute top-2 right-2 btn-circle btn-ghost btn-sm">✕</button>
            </form>
            <h3 class="text-lg font-bold text-primary">Xác nhận đặt đơn!</h3>
            <p>Số món: {{ billTemp.length }}</p>
            <p>Tổng: {{ formatPrice(total) }}</p>
            <p class="pt-4 text-sm font-light">Tổng tiền sẽ được thanh toán vào cuối bữa. Bạn có thể thêm món kể cả khi đã xác nhận đơn!</p>
            <div class="modal-action">
                <button class="btn bg-primary text-white" @click="placeOrder">Xác nhận</button>
            </div>
        </div>
    </dialog>
</template>
<script setup lang="ts">
import { Auth } from '@/types';

import { useHistoryStore } from '@/stores/history';
import { useOrderStore } from '@/stores/order';
import { orderDish } from '@/types/order';
import { useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

//icons
import { ShoppingCartIcon } from '@heroicons/vue/24/outline';
import { MinusIcon, PlusIcon } from '@heroicons/vue/24/solid';

const page = usePage();
const user = computed(() => (page.props.auth as Auth).user);

interface Props {
    billTemp: orderDish[];
    table: {
        id: string;
        table_number: number;
        branch_id: number;
    };
}

const props = defineProps<Props>();

const orderStore = useOrderStore();
const historyStore = useHistoryStore();
const total = computed(() => orderStore.totalPrice);

//emits for updating quantity and removing items
const emit = defineEmits<{
    decreaseQuantity: [index: number];
    removeItem: [index: number];
    increaseQuantity: [index: number];
}>();

/**
 * Decrease dish quantity or remove dish from cart
 * @param dishId
 * @param index
 */
function dishDelete(dishId: number | null, index: number) {
    if (dishId === null) return;

    const dish = props.billTemp.find((d) => d.dishId === dishId);
    if (dish) {
        if (dish.quantity > 1) {
            dish.quantity--;
            emit('decreaseQuantity', index);
        } else {
            emit('removeItem', index);
            props.billTemp.splice(index, 1);
        }
    }
}

/**
 * Increase dish quantity
 * @param index
 */
function dishIncrease(index: number) {
    props.billTemp[index].quantity++;
    emit('increaseQuantity', index);
}

/**
 * Confirm order
 */
function confirmOrder() {
    const confirmModal = document.getElementById('confirmOrder') as HTMLDialogElement;
    if (confirmModal) {
        confirmModal.showModal();
    }
}

/**
 * Place order
 */
function placeOrder() {
    const form = useForm({
        table_id: props.table.id,
        branch_id: props.table.branch_id,
        customer_id: user.value ? user.value.id : null,
        dishes: props.billTemp.map((dish) => ({
            dish_id: dish.dishId,
            quantity: dish.quantity,
            price: dish.price,
            note: dish.note,
        })),
    });

    form.post(route('order.place'), {
        onSuccess: () => {
            props.billTemp.forEach((dish) => historyStore.addHistory({ ...dish }));
            clearCart();
            const confirmModal = document.getElementById('confirmOrder') as HTMLDialogElement;
            const cartModal = document.getElementById('cart') as HTMLDialogElement;
            if (confirmModal) {
                confirmModal.close();
            }
            if (cartModal) {
                cartModal.close();
            }
        },
        onError: (errors) => {
            console.error(errors);
        },
    });
}

/**
 * Clear cart
 */
function clearCart() {
    props.billTemp.splice(0, props.billTemp.length);
    orderStore.clearDishes();
}

/**
 * formatted price to money string
 * @param price
 */
function formatPrice(price: number): string {
    return price.toLocaleString('vi-VN', { style: 'currency', currency: 'VND' });
}
</script>
