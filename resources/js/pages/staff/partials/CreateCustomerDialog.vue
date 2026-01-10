<template>
    <dialog ref="dialogRef" class="modal" @close="$emit('cancel')">
        <div class="modal-box">
            <h3 class="text-lg font-bold text-warning">Khách hàng chưa tồn tại</h3>
            <p class="py-4">
                Số điện thoại <strong>{{ customerPhone }}</strong> chưa có trong hệ thống. Vui lòng nhập tên để tạo tài khoản mới.
            </p>

            <div class="form-control w-full">
                <label class="label">
                    <span class="label-text font-medium">Tên khách hàng</span>
                </label>
                <input
                    :value="customerName"
                    @input="$emit('update:customerName', ($event.target as HTMLInputElement).value)"
                    type="text"
                    placeholder="Nhập tên khách hàng..."
                    class="input-bordered input w-full focus:outline-primary"
                    @keyup.enter="$emit('confirm')"
                />
            </div>

            <div class="mt-2 text-xs text-base-content/60">* Mật khẩu mặc định sẽ là số điện thoại</div>

            <div class="modal-action">
                <button class="btn" @click="$emit('cancel')" :disabled="isAttachingCustomer">Hủy</button>
                <button class="btn text-white btn-primary" @click="$emit('confirm')" :disabled="!customerName || isAttachingCustomer">
                    <span v-if="isAttachingCustomer" class="loading loading-spinner"></span>
                    Tạo & Thêm
                </button>
            </div>
        </div>
        <form method="dialog" class="modal-backdrop">
            <button @click="$emit('cancel')">close</button>
        </form>
    </dialog>
</template>

<script setup lang="ts">
import { ref, watch, onMounted } from 'vue';

interface Props {
    visible: boolean;
    customerPhone: string;
    customerName: string;
    isAttachingCustomer: boolean;
}

const props = defineProps<Props>();
const emit = defineEmits(['update:customerName', 'confirm', 'cancel']);

const dialogRef = ref<HTMLDialogElement | null>(null);

watch(
    () => props.visible,
    (val) => {
        if (val) {
            dialogRef.value?.showModal();
        } else {
            dialogRef.value?.close();
        }
    }
);

onMounted(() => {
    if (props.visible) {
        dialogRef.value?.showModal();
    }
});
</script>
