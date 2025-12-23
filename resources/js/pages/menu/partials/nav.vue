<template>
    <nav class="py-1 flex items-center justify-between bg-primary px-4">
        <img class="h-14 w-14 rounded-full max-[400px]:h-12 max-[400px]:w-12" src="/logo.svg " alt="Logo" />
        <p class="text-2xl font-semibold text-white">Bàn {{ props.tableNumber }}</p>
    </nav>
</template>

<script setup lang="ts">
interface Props {
    tableNumber: string;
}

const props = defineProps<Props>();
</script>
