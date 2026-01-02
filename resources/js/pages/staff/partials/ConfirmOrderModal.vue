<template>
    <dialog id="confirmOrder" class="modal">
        <div class="modal-box">
            <form method="dialog">
                <button class="btn absolute top-2 right-2 btn-circle btn-ghost btn-sm">✕</button>
            </form>
            <h3 class="text-lg font-bold text-primary">Xác nhận đặt đơn!</h3>
            <p>Số món: {{ itemCount }}</p>
            <p>Tổng: {{ formatPrice(totalAmount) }}</p>
            <p class="pt-4 text-sm font-light">Đơn hàng sẽ được gửi đến bếp để chuẩn bị.</p>
            <div class="modal-action">
                <button class="btn bg-primary text-white" @click="$emit('confirm')">Xác nhận</button>
            </div>
        </div>
    </dialog>
</template>

<script setup lang="ts">
interface Props {
    itemCount: number;
    totalAmount: number;
}

defineProps<Props>();

defineEmits<{
    confirm: [];
}>();

function formatPrice(price: number): string {
    return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(price);
}
</script>
