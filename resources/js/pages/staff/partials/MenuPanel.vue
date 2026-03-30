<template>
    <div class="flex w-2/3 flex-col bg-base-200/50">
        <!-- Breadcrumbs / Header -->
        <div class="flex h-14 items-center justify-between border-b border-base-300 bg-base-100 px-4 shadow-sm">
            <div class="flex items-center gap-2">
                <div v-if="selectedMenu" class="flex items-center gap-2">
                    <button class="btn btn-circle btn-ghost btn-sm" @click="$emit('update:selectedMenu', null)">
                        <ArrowLeftIcon class="size-5" />
                    </button>
                    <h2 class="text-lg font-bold">{{ selectedMenu.name }}</h2>
                </div>
                <h2 v-else class="text-lg font-bold">Danh mục món ăn</h2>
            </div>

            <button class="btn text-primary btn-ghost btn-sm" @click="$emit('open-custom-dish')">
                <PlusIcon class="size-5" />
                <span class="hidden sm:inline">Món ngoài</span>
            </button>
        </div>

        <!-- Pinned Quick-Order Bar (visible on category view) -->
        <Transition name="slide-down">
            <div v-if="!selectedMenu && pinnedFoods.length > 0" class="border-b border-base-300 bg-base-100/80 px-4 py-2">
                <div class="mb-1 flex items-center gap-1 text-xs font-semibold text-base-content/60">
                    <BookmarkIconSolid class="size-3.5 text-warning" />
                    <span>Ghim nhanh</span>
                </div>
                <div class="flex gap-2 overflow-x-auto pb-1">
                    <div
                        v-for="food in pinnedFoods"
                        :key="food.id"
                        class="group relative flex min-w-28 cursor-pointer flex-col rounded-xl border border-base-200 bg-base-100 p-2 shadow-sm transition-all hover:border-primary/40 hover:shadow-md"
                        @click="$emit('foodClick', food)"
                    >
                        <button
                            class="absolute top-1 right-1 z-10 rounded-full bg-base-200/90 p-1 shadow-sm active:bg-error/20"
                            @click.stop="unpin(food.id)"
                        >
                            <XMarkIcon class="size-4 text-base-content/60" />
                        </button>
                        <img :src="food.image || '/images/demo.jpg'" :alt="food.name" class="mb-1.5 h-14 w-full rounded-lg object-cover" />
                        <span class="line-clamp-1 text-xs font-semibold" :title="food.name">{{ food.name }}</span>
                        <span class="mt-0.5 text-xs font-bold text-primary">{{
                            formatPrice(food.is_discounted ? food.discount_price : food.price)
                        }}</span>
                    </div>
                </div>
            </div>
        </Transition>

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
                        class="card-compact group card cursor-pointer border border-base-200 bg-base-100 shadow-sm transition-all hover:shadow-md"
                        @click="$emit('foodClick', food)"
                    >
                        <figure class="relative h-32 w-full overflow-hidden">
                            <img
                                :src="food.image || '/images/demo.jpg'"
                                :alt="food.name"
                                class="h-full w-full object-cover transition-transform duration-500 hover:scale-110"
                            />
                            <!-- Favorite badge -->
                            <div v-if="food.is_favorite" class="absolute top-2 right-2 rounded-full bg-base-100/80 p-1 backdrop-blur-sm">
                                <HeartIcon class="size-4 text-error" />
                            </div>
                            <!-- Pin button -->
                            <button
                                class="absolute top-2 left-2 rounded-full p-1.5 shadow-md backdrop-blur-sm transition-colors active:scale-95"
                                :class="isPinned(food.id) ? 'bg-warning/90' : 'bg-base-100/80'"
                                :title="isPinned(food.id) ? 'Bỏ ghim' : 'Ghim món nhanh'"
                                @click.stop="togglePin(food)"
                            >
                                <BookmarkIconSolid v-if="isPinned(food.id)" class="size-5 text-white" />
                                <BookmarkIconOutline v-else class="size-5 text-base-content/60" />
                            </button>
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
import { usePinnedFoods } from '@/composables/usePinnedFoods';
import { Food, Menu } from '@/types/menu';
import { BookmarkIcon as BookmarkIconOutline } from '@heroicons/vue/24/outline';
import { ArrowLeftIcon, BookmarkIcon as BookmarkIconSolid, HeartIcon, PlusIcon, Squares2X2Icon, XMarkIcon } from '@heroicons/vue/24/solid';
import { computed } from 'vue';

interface Props {
    menus: Menu[];
    selectedMenu: Menu | null;
}

const props = defineProps<Props>();

defineEmits<{
    'update:selectedMenu': [menu: Menu | null];
    foodClick: [food: Food];
    'open-custom-dish': [];
}>();

const { isPinned, togglePin, unpin, pinnedFoodsFromMenus } = usePinnedFoods();

const pinnedFoods = computed(() => pinnedFoodsFromMenus(props.menus));

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

.slide-down-enter-active,
.slide-down-leave-active {
    transition:
        max-height 0.3s ease,
        opacity 0.3s ease;
    overflow: hidden;
    max-height: 160px;
}

.slide-down-enter-from,
.slide-down-leave-to {
    opacity: 0;
    max-height: 0;
}
</style>

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
