<template>
    <dialog ref="dialog" class="modal" @close="close">
        <div class="modal-box">
            <h3 class="text-lg font-bold">{{ title }}</h3>
            <p class="py-4">{{ message }}</p>

            <div class="modal-action">
                <button class="btn" @click="close">Hủy</button>
                <button class="btn btn-error text-white" @click="confirm">Xác nhận</button>
            </div>
        </div>
        <form method="dialog" class="modal-backdrop">
            <button @click="close">close</button>
        </form>
    </dialog>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue';

const props = defineProps<{
    isOpen: boolean;
    title?: string;
    message?: string;
}>();

const emit = defineEmits(['update:isOpen', 'confirm']);

const dialog = ref<HTMLDialogElement | null>(null);

watch(
    () => props.isOpen,
    (newVal) => {
        if (newVal) {
            dialog.value?.showModal();
        } else {
            dialog.value?.close();
        }
    }
);

const close = () => {
    emit('update:isOpen', false);
};

const confirm = () => {
    emit('confirm');
    close();
};
</script>
