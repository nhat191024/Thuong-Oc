<template>
    <dialog ref="dialogRef" id="announcementModal" class="modal modal-middle">
        <div class="modal-box relative">
            <button class="btn btn-circle btn-ghost btn-sm absolute top-2 right-2" @click="close">✕</button>

            <div class="mb-4 flex items-center gap-3 pr-8">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-primary/10">
                    <MegaphoneIcon class="size-5 text-primary" />
                </div>
                <h3 class="text-lg font-bold text-base-content">{{ announcement.title }}</h3>
            </div>

            <div class="whitespace-pre-wrap text-sm leading-relaxed text-base-content/80">{{ announcement.content }}</div>

            <div class="modal-action mt-6">
                <button class="btn btn-primary w-full" @click="close">Đã hiểu</button>
            </div>
        </div>
        <form method="dialog" class="modal-backdrop">
            <button @click="close">close</button>
        </form>
    </dialog>
</template>

<script setup lang="ts">
import { MegaphoneIcon } from '@heroicons/vue/24/solid';
import { onMounted, ref } from 'vue';

interface Announcement {
    title: string;
    content: string;
}

interface Props {
    announcement: Announcement | null;
}

const props = defineProps<Props>();

const dialogRef = ref<HTMLDialogElement | null>(null);

onMounted(() => {
    if (props.announcement && dialogRef.value) {
        dialogRef.value.showModal();
    }
});

function close() {
    if (dialogRef.value) {
        dialogRef.value.close();
    }
}
</script>
