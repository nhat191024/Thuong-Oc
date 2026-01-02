<template>
    <dialog ref="dialog" class="modal" @close="close">
        <div class="modal-box">
            <!-- Nút X để tắt -->
            <button class="btn absolute top-2 right-2 btn-circle btn-ghost btn-sm" @click="close">✕</button>

            <h3 class="text-lg font-bold">{{ title }}</h3>
            <p class="py-4" v-if="message">{{ message }}</p>

            <!-- Slot cho nội dung tùy chỉnh nếu cần -->
            <slot></slot>

            <div class="modal-action">
                <!-- Nút OK -->
                <button class="btn btn-primary" @click="onOk">OK</button>
            </div>
        </div>

        <!-- Click bên ngoài để đóng -->
        <form method="dialog" class="modal-backdrop">
            <button @click="close">close</button>
        </form>
    </dialog>
</template>

<script setup>
import { ref, watch } from 'vue';

const props = defineProps({
    isOpen: {
        type: Boolean,
        default: false,
    },
    title: {
        type: String,
        default: 'Thông báo',
    },
    message: {
        type: String,
        default: '',
    },
});

const emit = defineEmits(['update:isOpen', 'ok']);

const dialog = ref(null);

watch(
    () => props.isOpen,
    (newVal) => {
        if (newVal) {
            dialog.value?.showModal();
        } else {
            dialog.value?.close();
        }
    },
);

const close = () => {
    emit('update:isOpen', false);
};

const onOk = () => {
    emit('ok');
    close();
};
</script>
