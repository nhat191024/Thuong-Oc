<template>
    <div class="flex w-1/3 flex-col border-r border-base-300 bg-base-100">
        <!-- Tabs -->
        <div role="tablist" class="tabs-bordered tabs w-full">
            <a
                role="tab"
                class="tab h-12 flex-1 font-bold transition-all duration-200"
                :class="{ 'tab-active text-primary': activeTab === 'bill' }"
                @click="$emit('update:activeTab', 'bill')"
            >
                Bill ({{ billItems.length }})
            </a>
            <a
                role="tab"
                class="tab h-12 flex-1 font-bold transition-all duration-200"
                :class="{ 'tab-active text-primary': activeTab === 'cart' }"
                @click="$emit('update:activeTab', 'cart')"
            >
                Giỏ hàng ({{ cartItems.length }})
            </a>
        </div>

        <!-- Content -->
        <div class="flex-1 overflow-y-auto p-4">
            <!-- Bill Tab -->
            <div v-if="activeTab === 'bill'" class="space-y-4">
                <div v-if="billItems.length === 0" class="mt-10 text-center text-base-content/50">
                    <p>Chưa có món nào trong bill</p>
                </div>
                <div v-for="(item, index) in billItems" :key="index" class="flex flex-col gap-2 rounded-lg border border-base-200 p-3 shadow-sm">
                    <div class="flex justify-between">
                        <h3 class="font-bold">{{ item.name }}</h3>
                        <span class="font-semibold text-primary">{{ formatPrice(item.price * item.quantity) }}</span>
                    </div>
                    <p v-if="item.cookingMethod" class="text-sm text-base-content/70">
                        {{ item.cookingMethod }}
                    </p>
                    <p v-if="item.note" class="text-sm text-base-content/60 italic">Note: {{ item.note }}</p>

                    <div class="mt-2 flex items-center justify-between">
                        <span class="text-sm text-base-content/50">{{ formatPrice(item.price) }} / món</span>
                        <div class="flex items-center gap-3">
                            <button
                                class="btn btn-circle bg-base-200 btn-ghost btn-xs hover:bg-base-300"
                                @click="$emit('updateBillQuantity', index, -1)"
                            >
                                <MinusIcon class="size-3" />
                            </button>
                            <span class="w-4 text-center font-bold">{{ item.quantity }}</span>
                            <button class="btn btn-circle text-white btn-xs btn-primary" @click="$emit('updateBillQuantity', index, 1)">
                                <PlusIcon class="size-3" />
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Cart Tab -->
            <div v-else class="space-y-4">
                <div v-if="cartItems.length === 0" class="mt-10 text-center text-base-content/50">
                    <p>Giỏ hàng trống</p>
                </div>
                <div
                    v-for="(item, index) in cartItems"
                    :key="index"
                    class="bg-base-50 flex flex-col gap-2 rounded-lg border border-base-200 p-3 shadow-sm"
                >
                    <div class="flex justify-between">
                        <h3 class="font-bold">{{ item.name }}</h3>
                        <button class="text-error hover:text-error/80" @click="$emit('removeFromCart', index)">
                            <TrashIcon class="size-4" />
                        </button>
                    </div>
                    <p v-if="item.cookingMethod" class="text-sm text-base-content/70">
                        {{ item.cookingMethod }}
                    </p>
                    <p v-if="item.note" class="text-sm text-base-content/60 italic">Note: {{ item.note }}</p>

                    <div class="mt-2 flex items-center justify-between">
                        <span class="font-semibold text-primary">{{ formatPrice(item.price * item.quantity) }}</span>
                        <div class="flex items-center gap-3">
                            <button
                                class="btn btn-circle bg-base-200 btn-ghost btn-xs hover:bg-base-300"
                                @click="$emit('updateCartQuantity', index, -1)"
                            >
                                <MinusIcon class="size-3" />
                            </button>
                            <span class="w-4 text-center font-bold">{{ item.quantity }}</span>
                            <button class="btn btn-circle text-white btn-xs btn-primary" @click="$emit('updateCartQuantity', index, 1)">
                                <PlusIcon class="size-3" />
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer Summary -->
        <div class="border-t border-base-300 bg-base-100 p-4 shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.05)]">
            <div class="mb-4 flex justify-between text-lg font-bold">
                <span>Tổng cộng:</span>
                <span class="text-primary">{{ formatPrice(totalAmount) }}</span>
            </div>
            <button
                v-if="activeTab === 'cart'"
                class="btn w-full text-white shadow-lg shadow-primary/30 btn-primary"
                :disabled="cartItems.length === 0"
                @click="$emit('sendOrder')"
            >
                <PaperAirplaneIcon class="size-5" />
                Gửi Bếp ({{ cartItems.length }})
            </button>
            <div v-else class="flex gap-2">
                <button class="btn flex-1 btn-primary" @click="$emit('updateBill')">Cập nhật Bill</button>
                <div class="dropdown dropdown-center dropdown-top flex-1">
                    <div tabindex="0" role="button" class="btn w-full btn-success">Thao tác</div>
                    <ul tabindex="0" class="dropdown-content menu z-50 mb-2 w-52 rounded-box border border-black bg-base-100 p-2 shadow-xl">
                        <li><a @click="$emit('payment')">Thanh toán</a></li>
                        <li><a @click="openMoveTableModal">Chuyển bàn</a></li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Move Table Modal -->
        <dialog id="move_table_modal" class="modal">
            <div class="modal-box">
                <h3 class="font-bold text-lg">Chuyển bàn</h3>
                <p class="py-4">Chọn bàn muốn chuyển đến:</p>
                <div v-if="inactiveTables && inactiveTables.length > 0" class="grid grid-cols-4 gap-2">
                    <button
                        v-for="table in inactiveTables"
                        :key="table.id"
                        class="btn btn-outline"
                        @click="selectMoveTable(table.id)"
                    >
                        {{ table.table_number }}
                    </button>
                </div>
                <div v-else class="text-center text-gray-500">
                    Không có bàn trống nào.
                </div>
                <div class="modal-action">
                    <form method="dialog">
                        <button class="btn">Đóng</button>
                    </form>
                </div>
            </div>
        </dialog>
    </div>
</template>

<script setup lang="ts">
import { orderDish } from '@/types/order';
import { MinusIcon, PaperAirplaneIcon, PlusIcon, TrashIcon } from '@heroicons/vue/24/solid';

interface Props {
    billItems: orderDish[];
    cartItems: orderDish[];
    activeTab: 'bill' | 'cart';
    totalAmount: number;
    inactiveTables?: { id: string; table_number: number }[];
}

defineProps<Props>();

const emit = defineEmits<{
    'update:activeTab': [value: 'bill' | 'cart'];
    updateBillQuantity: [index: number, delta: number];
    updateCartQuantity: [index: number, delta: number];
    removeFromCart: [index: number];
    sendOrder: [];
    updateBill: [];
    payment: [];
    moveTable: [tableId: string];
}>();

function formatPrice(price: number): string {
    return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(price);
}

function openMoveTableModal() {
    const modal = document.getElementById('move_table_modal') as HTMLDialogElement;
    if (modal) modal.showModal();
}

function selectMoveTable(tableId: string) {
    emit('moveTable', tableId);
    const modal = document.getElementById('move_table_modal') as HTMLDialogElement;
    if (modal) modal.close();
}
</script>
