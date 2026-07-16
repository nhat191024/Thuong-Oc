<template>
    <div class="relative flex w-1/3 flex-col border-r border-base-300 bg-base-100">
        <!-- Merge Loading Overlay -->
        <Transition
            enter-active-class="transition-opacity duration-200"
            enter-from-class="opacity-0"
            leave-active-class="transition-opacity duration-200"
            leave-to-class="opacity-0"
        >
            <div
                v-if="isMerging || isDeleting"
                class="absolute inset-0 z-50 flex flex-col items-center justify-center gap-3 bg-base-100/80 backdrop-blur-sm"
            >
                <span class="loading loading-lg loading-spinner" :class="isDeleting ? 'text-error' : 'text-warning'"></span>
                <p class="font-semibold text-base-content">{{ isDeleting ? 'Đang xóa đơn...' : 'Đang gộp đơn...' }}</p>
            </div>
        </Transition>
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
                        <li><a @click="openMergeTableModal">Gộp đơn</a></li>
                        <!-- <li><a class="text-error" @click="openDeleteBillModal">Xóa đơn</a></li> -->
                    </ul>
                </div>
            </div>
        </div>

        <!-- Move Table Modal -->
        <dialog id="move_table_modal" class="modal">
            <div class="modal-box">
                <h3 class="text-lg font-bold">Chuyển bàn</h3>
                <p class="py-4">Chọn bàn muốn chuyển đến:</p>
                <div v-if="inactiveTables && inactiveTables.length > 0" class="grid grid-cols-4 gap-2">
                    <button v-for="table in inactiveTables" :key="table.id" class="btn btn-outline" @click="selectMoveTable(table.id)">
                        {{ table.name ?? 'Bàn ' + table.table_number }}
                    </button>
                </div>
                <div v-else class="text-center text-gray-500">Không có bàn trống nào.</div>
                <div class="modal-action">
                    <form method="dialog">
                        <button class="btn">Đóng</button>
                    </form>
                </div>
            </div>
        </dialog>

        <!-- Merge Table Modal -->
        <dialog id="merge_table_modal" class="modal">
            <div class="modal-box max-w-lg">
                <h3 class="flex items-center gap-2 text-lg font-bold">
                    <ArrowsRightLeftIcon class="size-5 text-warning" />
                    Gộp đơn
                </h3>

                <!-- Flow description -->
                <div class="mt-3 alert alert-warning">
                    <InformationCircleIcon class="size-5 shrink-0" />
                    <div class="text-sm">
                        <p class="font-semibold">Lưu ý về luồng gộp đơn:</p>
                        <ol class="mt-1 list-inside list-decimal space-y-1">
                            <li>Toàn bộ món ăn của bàn hiện tại sẽ được <strong>chuyển vào bàn đích</strong>.</li>
                            <li>Đơn của bàn hiện tại sẽ bị <strong>xóa</strong>.</li>
                            <li>Bàn hiện tại sẽ được đặt về trạng thái <strong>trống</strong>.</li>
                            <li>Bạn sẽ được chuyển sang <strong>trang bàn đích</strong> để tiếp tục phục vụ.</li>
                        </ol>
                        <p class="mt-2 font-medium text-error">⚠ Hành động này không thể hoàn tác!</p>
                    </div>
                </div>

                <p class="mt-4 font-medium">Chọn bàn đích để gộp vào:</p>
                <div v-if="activeTables && activeTables.length > 0" class="mt-2 grid grid-cols-4 gap-2">
                    <button
                        v-for="table in activeTables"
                        :key="table.id"
                        class="btn btn-outline btn-warning"
                        @click="confirmMergeTable(table.id, table.name ?? 'Bàn ' + table.table_number)"
                    >
                        {{ table.name ?? 'Bàn ' + table.table_number }}
                    </button>
                </div>
                <div v-else class="mt-2 text-center text-gray-500">Không có bàn nào đang hoạt động để gộp.</div>
                <div class="modal-action">
                    <form method="dialog">
                        <button class="btn">Đóng</button>
                    </form>
                </div>
            </div>
        </dialog>

        <!-- Merge Confirm Dialog -->
        <dialog id="merge_confirm_modal" class="modal">
            <div class="modal-box">
                <h3 class="text-lg font-bold text-error">Xác nhận gộp đơn</h3>
                <p class="py-3">
                    Bạn chắc chắn muốn gộp đơn của bàn này vào
                    <strong>{{ pendingMergeTableName }}</strong
                    >?
                </p>
                <p class="text-sm text-base-content/60">Đơn hiện tại sẽ bị xóa và bàn sẽ được giải phóng.</p>
                <div class="modal-action">
                    <button class="btn btn-error" @click="executeMerge">Xác nhận gộp</button>
                    <form method="dialog">
                        <button class="btn">Hủy</button>
                    </form>
                </div>
            </div>
        </dialog>

        <!-- Delete Bill Confirm Dialog -->
        <dialog id="delete_bill_confirm_modal" class="modal">
            <div class="modal-box">
                <h3 class="text-lg font-bold text-error">Xác nhận xóa đơn</h3>
                <p class="py-3">Bạn có chắc muốn xóa đơn hiện tại?</p>
                <p class="text-sm text-base-content/60">Đơn sẽ được chuyển vào lịch sử, toàn bộ món sẽ bị hủy và bàn sẽ được giải phóng.</p>
                <div class="modal-action">
                    <button class="btn text-white btn-error" @click="executeDeleteBill">Xóa đơn</button>
                    <form method="dialog">
                        <button class="btn">Hủy</button>
                    </form>
                </div>
            </div>
        </dialog>
    </div>
</template>

<script setup lang="ts">
import { orderDish } from '@/types/order';
import { ArrowsRightLeftIcon, InformationCircleIcon, MinusIcon, PaperAirplaneIcon, PlusIcon, TrashIcon } from '@heroicons/vue/24/solid';
import { ref } from 'vue';

interface Props {
    billItems: orderDish[];
    cartItems: orderDish[];
    activeTab: 'bill' | 'cart';
    totalAmount: number;
    inactiveTables?: { id: string; name: string; table_number: number }[];
    activeTables?: { id: string; name: string; table_number: number }[];
    isMerging?: boolean;
    isDeleting?: boolean;
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
    mergeTable: [tableId: string];
    deleteBill: [];
}>();

const pendingMergeTableId = ref<string>('');
const pendingMergeTableName = ref<string>('');

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

function openMergeTableModal() {
    const modal = document.getElementById('merge_table_modal') as HTMLDialogElement;
    if (modal) modal.showModal();
}

function confirmMergeTable(tableId: string, tableName: string) {
    pendingMergeTableId.value = tableId;
    pendingMergeTableName.value = tableName;

    const mergeModal = document.getElementById('merge_table_modal') as HTMLDialogElement;
    if (mergeModal) mergeModal.close();

    const confirmModal = document.getElementById('merge_confirm_modal') as HTMLDialogElement;
    if (confirmModal) confirmModal.showModal();
}

function executeMerge() {
    emit('mergeTable', pendingMergeTableId.value);
    const confirmModal = document.getElementById('merge_confirm_modal') as HTMLDialogElement;
    if (confirmModal) confirmModal.close();
}

// function openDeleteBillModal() {
//     const modal = document.getElementById('delete_bill_confirm_modal') as HTMLDialogElement;
//     if (modal) modal.showModal();
// }

function executeDeleteBill() {
    emit('deleteBill');
    const modal = document.getElementById('delete_bill_confirm_modal') as HTMLDialogElement;
    if (modal) modal.close();
}
</script>
