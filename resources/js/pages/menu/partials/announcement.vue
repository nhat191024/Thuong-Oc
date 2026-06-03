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

            <!-- eslint-disable-next-line vue/no-v-html -->
            <div class="rich-content text-sm text-base-content/80" v-html="announcement.content"></div>

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

<style scoped>
.rich-content :deep(h2) {
    font-size: 1.125rem;
    font-weight: 700;
    margin-top: 1rem;
    margin-bottom: 0.375rem;
}
.rich-content :deep(h3) {
    font-size: 1rem;
    font-weight: 600;
    margin-top: 0.75rem;
    margin-bottom: 0.25rem;
}
.rich-content :deep(p) {
    margin-bottom: 0.5rem;
    line-height: 1.6;
}
.rich-content :deep(strong) {
    font-weight: 700;
}
.rich-content :deep(em) {
    font-style: italic;
}
.rich-content :deep(u) {
    text-decoration: underline;
}
.rich-content :deep(s) {
    text-decoration: line-through;
}
.rich-content :deep(a) {
    color: oklch(var(--p));
    text-decoration: underline;
}
.rich-content :deep(ul) {
    list-style-type: disc;
    padding-left: 1.25rem;
    margin-bottom: 0.5rem;
}
.rich-content :deep(ol) {
    list-style-type: decimal;
    padding-left: 1.25rem;
    margin-bottom: 0.5rem;
}
.rich-content :deep(li) {
    margin-bottom: 0.2rem;
}
.rich-content :deep(blockquote) {
    border-left: 3px solid oklch(var(--p));
    padding-left: 0.75rem;
    margin: 0.5rem 0;
    opacity: 0.75;
    font-style: italic;
}
</style>
