<template>
    <div class="flex w-2/3 flex-col bg-base-200/50">
        <!-- Breadcrumbs / Header -->
        <div class="flex h-14 items-center border-b border-base-300 bg-base-100 px-4 shadow-sm">
            <div v-if="selectedMenu" class="flex items-center gap-2">
                <button class="btn btn-circle btn-ghost btn-sm" @click="$emit('update:selectedMenu', null)">
                    <ArrowLeftIcon class="size-5" />
                </button>
                <h2 class="text-lg font-bold">{{ selectedMenu.name }}</h2>
            </div>
            <h2 v-else class="text-lg font-bold">Danh mục món ăn</h2>
        </div>

        <!-- Content -->
        <div class="flex-1 overflow-y-auto p-4">
            <Transition name="fade" mode="out-in">
                <!-- Categories Grid -->
                <div v-if="!selectedMenu" key="categories" class="grid grid-cols-3 gap-4 xl:grid-cols-4">
                    <div
                        v-for="menu in menus"
                        :key="menu.id"
                        class="card cursor-pointer border border-base-200 bg-base-100 shadow-sm transition-all hover:scale-105 hover:shadow-md"
                        @click="$emit('update:selectedMenu', menu)"
                    >
                        <div class="card-body items-center p-6 text-center">
                            <div class="mb-2 rounded-full bg-primary/10 p-3 text-primary">
                                <Squares2X2Icon class="size-8" />
                            </div>
                            <h3 class="card-title text-base">{{ menu.name }}</h3>
                            <p class="text-xs text-base-content/60">{{ menu.foods.length }} món</p>
                        </div>
                    </div>
                </div>

                <!-- Foods Grid -->
                <div v-else key="foods" class="grid grid-cols-2 gap-4 xl:grid-cols-3">
                    <div
                        v-for="food in selectedMenu.foods"
                        :key="food.id"
                        class="card-compact card cursor-pointer border border-base-200 bg-base-100 shadow-sm transition-all hover:shadow-md"
                        @click="$emit('foodClick', food)"
                    >
                        <figure class="relative h-32 w-full overflow-hidden">
                            <img
                                :src="food.image || '/images/demo.jpg'"
                                :alt="food.name"
                                class="h-full w-full object-cover transition-transform duration-500 hover:scale-110"
                            />
                            <div v-if="food.is_favorite" class="absolute top-2 right-2 rounded-full bg-base-100/80 p-1 backdrop-blur-sm">
                                <HeartIcon class="size-4 text-error" />
                            </div>
                        </figure>
                        <div class="card-body">
                            <h3 class="card-title line-clamp-1 text-sm" :title="food.name">{{ food.name }}</h3>
                            <div class="mt-auto flex items-end justify-between">
                                <div class="flex flex-col">
                                    <span class="text-xs text-base-content/50">Đã bán {{ food.sold_count }}</span>
                                    <div class="flex items-baseline gap-1">
                                        <span class="font-bold text-primary">{{
                                            formatPrice(food.is_discounted ? food.discount_price : food.price)
                                        }}</span>
                                        <span v-if="food.is_discounted" class="text-xs text-base-content/50 line-through">{{
                                            formatPrice(food.price)
                                        }}</span>
                                    </div>
                                </div>
                                <button class="btn btn-circle text-white shadow-md shadow-primary/30 btn-xs btn-primary">
                                    <PlusIcon class="size-4" />
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </Transition>
        </div>
    </div>
</template>

<script setup lang="ts">
import { Menu, Food } from '@/types/menu';
import { ArrowLeftIcon, HeartIcon, PlusIcon, Squares2X2Icon } from '@heroicons/vue/24/solid';

interface Props {
    menus: Menu[];
    selectedMenu: Menu | null;
}

defineProps<Props>();

defineEmits<{
    'update:selectedMenu': [menu: Menu | null];
    foodClick: [food: Food];
}>();

function formatPrice(price: number): string {
    return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(price);
}
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition:
        opacity 0.2s ease,
        transform 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
    transform: translateY(10px);
}
</style>
