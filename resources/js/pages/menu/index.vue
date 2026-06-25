<template>
    <div class="flex h-dvh w-dvw flex-col bg-base-100">
        <Nav :table-name="table.name" :table-number="table.table_number"></Nav>

        <!-- Cellular Warning Banner -->
        <div v-if="showCellularWarning" class="z-30 flex items-start gap-3 border-b border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
            <svg xmlns="http://www.w3.org/2000/svg" class="mt-0.5 h-5 w-5 shrink-0 text-amber-500" viewBox="0 0 20 20" fill="currentColor">
                <path
                    fill-rule="evenodd"
                    d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495zM10 5a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 5zm0 9a1 1 0 100-2 1 1 0 000 2z"
                    clip-rule="evenodd"
                />
            </svg>
            <div class="flex-1">
                <p class="font-semibold">Bạn đang dùng dữ liệu di động</p>
                <p class="mt-0.5 text-amber-700">Có thể gây giật, lag khi đặt món. Hãy kết nối Wifi quán để trải nghiệm tốt hơn.</p>
                <p class="mt-1 font-medium">📶 Wifi: <span class="font-bold">Thuong Oc 269</span></p>
            </div>
            <button @click="showCellularWarning = false" class="shrink-0 text-amber-500 hover:text-amber-700">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path
                        fill-rule="evenodd"
                        d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                        clip-rule="evenodd"
                    />
                </svg>
            </button>
        </div>

        <!-- Category Tab -->
        <div class="sticky top-0 z-20 flex-none bg-base-100/95 shadow-sm backdrop-blur-sm">
            <div ref="tabContainer" role="tablist" class="no-scrollbar flex gap-2 overflow-x-auto scroll-smooth px-4 py-3">
                <a
                    v-for="tab in categories"
                    :key="tab.id"
                    :ref="(el) => setTabRef(el, tab.id)"
                    role="tab"
                    class="btn flex-nowrap rounded-full border-none font-normal whitespace-nowrap transition-all duration-300 btn-sm"
                    :class="
                        activeTabId === tab.id ? 'scale-105 bg-primary text-white shadow-md' : 'bg-base-200 text-base-content/70 hover:bg-base-300'
                    "
                    :href="'#' + tab.id"
                    @click.prevent="handleTabClick(tab.id)"
                >
                    {{ tab.name }}
                </a>
            </div>
        </div>

        <!-- Menu Content -->
        <div ref="menuContent" class="flex-1 overflow-y-auto scroll-smooth px-4 pb-24" @scroll="handleScroll">
            <div v-for="menu in menus" :key="menu.id" :id="menu.id" class="category animate-fade-in py-4">
                <h2
                    class="sticky top-0 z-10 -mx-4 mb-4 flex items-center gap-2 bg-base-100/90 px-4 py-2 text-xl font-bold text-base-content backdrop-blur-sm"
                >
                    {{ menu.name }}
                    <span class="rounded-full bg-primary/10 px-2 py-0.5 text-xs font-medium text-primary">{{ menu.foods.length }}</span>
                </h2>

                <div class="flex flex-col gap-4">
                    <div
                        v-for="food in menu.foods"
                        :key="food.id"
                        class="card card-side gap-3 border border-base-200 bg-base-100 p-3 shadow-sm transition-all duration-200 hover:shadow-md active:scale-[0.98]"
                        :class="{ 'opacity-60': food.is_out_of_stock }"
                    >
                        <figure class="group relative h-28 w-28 shrink-0 overflow-hidden rounded-xl">
                            <img
                                :src="food.image || '/images/demo.jpg'"
                                alt="demo"
                                class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-110"
                            />
                            <div class="absolute inset-0 bg-black/5 transition-colors group-hover:bg-transparent"></div>
                            <div v-if="food.is_out_of_stock" class="absolute inset-0 flex items-center justify-center bg-black/40">
                                <span class="rounded-full bg-error px-2 py-0.5 text-xs font-bold text-white">Hết món</span>
                            </div>
                        </figure>

                        <div class="flex min-w-0 flex-1 flex-col justify-between py-0.5">
                            <div>
                                <div class="flex items-start justify-between gap-2">
                                    <h3 class="mb-1 line-clamp-2 text-base leading-tight font-bold">{{ food.name }}</h3>
                                </div>
                                <p class="mb-2 line-clamp-2 text-xs text-base-content/60">
                                    {{ food.dishes.length === 1 ? food.dishes[0].note : food.note }}
                                </p>
                            </div>

                            <div v-if="food.is_favorite" class="flex items-center gap-1">
                                <HeartIcon class="size-4 text-primary" />
                                <p class="text-xs font-semibold text-primary">Được yêu thích</p>
                            </div>

                            <div class="mt-auto flex items-end justify-between">
                                <div>
                                    <p class="mb-0.5 text-[10px] font-medium tracking-wider text-base-content/40 uppercase">
                                        Đã bán {{ formatSoldCount(food.sold_count) }}
                                    </p>
                                    <div class="flex items-baseline gap-2">
                                        <p class="text-lg leading-none font-bold text-primary">
                                            {{ food.is_discounted ? formatPrice(food.discount_price) : formatPrice(food.price) }}
                                        </p>
                                        <p v-if="food.is_discounted" class="text-sm leading-none text-base-content/50 line-through">
                                            {{ formatPrice(food.price) }}
                                        </p>
                                    </div>
                                </div>

                                <button
                                    v-if="!food.is_out_of_stock"
                                    class="btn btn-circle bg-primary text-white shadow-lg shadow-primary/30 transition-transform btn-sm hover:scale-110"
                                    :disabled="isDishSubmitting(food.dishes[0]?.id)"
                                    @click.stop="food.dishes.length > 1 ? showDishDetail(food) : addDish(food.id)"
                                >
                                    <span v-if="isDishSubmitting(food.dishes[0]?.id)" class="loading loading-xs loading-spinner"></span>
                                    <PlusIcon v-else class="size-5" />
                                </button>
                                <span v-else class="rounded-full border border-error/40 bg-error/10 px-2 py-1 text-xs font-semibold text-error">
                                    Hết món
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="z-30 flex-none">
            <Footer :table-number="table.table_number"></Footer>
        </div>

        <Announcement v-if="props.announcement" :announcement="props.announcement"></Announcement>
        <DishDetail
            ref="dishDetailRef"
            :food="selectedFood"
            :is-submitting="isSelectedDishSubmitting"
            direct-to-kitchen
            @finalize-dish="handleFinalizeDish"
        ></DishDetail>
        <!-- TEMP: Cart disabled for direct-to-kitchen ordering.
        <Cart
            :bill-temp="billTemp"
            :table="table"
            @decrease-quantity="handleDecreaseQuantity"
            @remove-item="handleRemoveItem"
            @increase-quantity="handleIncreaseQuantity"
        ></Cart>
        -->
        <History></History>
    </div>
</template>

<style scoped>
.no-scrollbar::-webkit-scrollbar {
    display: none;
}
.no-scrollbar {
    -ms-overflow-style: none;
    scrollbar-width: none;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.animate-fade-in {
    animation: fadeIn 0.5s ease-out forwards;
}
</style>

<script setup lang="ts">
import { useHistoryStore } from '@/stores/history';
import { Auth } from '@/types';
// TEMP: Cart disabled for direct-to-kitchen ordering.
// import { useOrderStore } from '@/stores/order';
import { Category } from '@/types/category';
import { Food, Menu } from '@/types/menu';
import { orderDish } from '@/types/order';
import { usePage } from '@inertiajs/vue3';
import axios from 'axios';
import { computed, onMounted, ref } from 'vue';
import { toast } from 'vue3-toastify';

// Components
import Announcement from './partials/announcement.vue';
// TEMP: Cart disabled for direct-to-kitchen ordering.
// import Cart from './partials/cart.vue';
import DishDetail from './partials/dish-detail.vue';
import Footer from './partials/footer.vue';
import History from './partials/history.vue';
import Nav from './partials/nav.vue';

//icons
import { HeartIcon, PlusIcon } from '@heroicons/vue/24/solid';

interface Announcement {
    title: string;
    content: string;
}

interface Props {
    table: {
        id: string;
        name: string;
        table_number: number;
        branch_id: number;
    };
    categories: Category[];
    menus: Menu[];
    currentOrder: orderDish[];
    announcement: Announcement | null;
}

const props = defineProps<Props>();

const page = usePage();
const user = computed(() => (page.props.auth as Auth).user);
// TEMP: Cart disabled for direct-to-kitchen ordering.
// const orderStore = useOrderStore();
const historyStore = useHistoryStore();

const activeTabId = ref<number>(props.categories[0]?.id || 0);
const menuContent = ref<HTMLElement | null>(null);
const tabContainer = ref<HTMLElement | null>(null);
const tabRefs = ref<Map<number, HTMLElement>>(new Map());
const isScrollingFromClick = ref(false);
const lastScrollTop = ref(0);

// TEMP: Cart disabled for direct-to-kitchen ordering.
// const billTemp = ref<orderDish[]>([]);
const selectedFood = ref<Food>(props.menus[0]?.foods[0]);
const showCellularWarning = ref(false);
const submittingDishIds = ref<Set<number>>(new Set());
const dishDetailRef = ref<{ completeSubmission: () => void } | null>(null);
const isSelectedDishSubmitting = computed(() => selectedFood.value?.dishes.some((dish) => isDishSubmitting(dish.id)) ?? false);

onMounted(() => {
    const connection = (navigator as any).connection ?? (navigator as any).mozConnection ?? (navigator as any).webkitConnection;
    if (connection) {
        const isCellularType = connection.type === 'cellular';
        const isCellularEffective = connection.effectiveType && ['slow-2g', '2g', '3g'].includes(connection.effectiveType);
        if (isCellularType || isCellularEffective) {
            showCellularWarning.value = true;
        }
    }

    if (props.currentOrder) {
        historyStore.clearHistory();
        props.currentOrder.forEach((dish) => historyStore.addHistory(dish));
    }
    // TEMP: Cart disabled for direct-to-kitchen ordering.
    // loadOrderFromLocalStorage();
    if (activeTabId.value) {
        scrollTabToView(activeTabId.value);
    }
});

function setTabRef(el: any, id: number) {
    if (el) {
        tabRefs.value.set(id, el);
    }
}

function scrollTabToView(id: number) {
    const tabElement = tabRefs.value.get(id);
    if (tabElement && tabContainer.value) {
        tabElement.scrollIntoView({
            behavior: 'smooth',
            block: 'nearest',
            inline: 'center',
        });
    }
}

/*
 * TEMP: Cart disabled for direct-to-kitchen ordering.
 * The previous loadOrderFromLocalStorage() implementation is intentionally
 * disabled while each finalized dish is submitted immediately.
 */

/**
 * Handle tab click event
 * @param id
 *
 */
function handleTabClick(id: number) {
    activeTabId.value = id;
    isScrollingFromClick.value = true;

    const categoryElement = document.getElementById(id.toString());
    if (categoryElement && menuContent.value) {
        categoryElement.scrollIntoView({
            behavior: 'smooth',
            block: 'start',
        });

        setTimeout(() => {
            isScrollingFromClick.value = false;
        }, 600);
    }

    scrollTabToView(id);
}

/**
 * Handle scroll event on menu content
 */
function handleScroll() {
    if (isScrollingFromClick.value) return;

    if (!menuContent.value) return;

    const currentScrollTop = menuContent.value.scrollTop;
    const isScrollingDown = currentScrollTop > lastScrollTop.value;
    lastScrollTop.value = currentScrollTop;

    const containerRect = menuContent.value.getBoundingClientRect();
    const categories = menuContent.value.querySelectorAll('.category');

    let bestCategoryId: number | null = null;
    let bestScore = -1;

    categories.forEach((category) => {
        const rect = category.getBoundingClientRect();
        const visibleTop = Math.max(rect.top, containerRect.top);
        const visibleBottom = Math.min(rect.bottom, containerRect.bottom);
        const visibleHeight = Math.max(0, visibleBottom - visibleTop);
        const visiblePercentage = (visibleHeight / rect.height) * 100;

        const id = parseInt(category.id);
        if (isNaN(id)) return;

        let score = 0;
        let passed = false;

        if (isScrollingDown) {
            if (visiblePercentage > 10) {
                const distanceFromTop = Math.abs(rect.top - containerRect.top);
                score = 10000 - distanceFromTop + visiblePercentage;
                passed = true;
            }
        } else {
            if (visiblePercentage >= 20) {
                score = visiblePercentage;
                passed = true;
            }
        }

        if (passed && score > bestScore) {
            bestCategoryId = id;
            bestScore = score;
        }
    });

    if (bestCategoryId !== null && bestCategoryId !== activeTabId.value) {
        activeTabId.value = bestCategoryId;
        scrollTabToView(bestCategoryId);
    }
}

/**
 * Show dish detail modal
 */
function showDishDetail(food: any) {
    selectedFood.value = food;
    const dishDetail = document.getElementById('dishDetail') as HTMLDialogElement;
    if (dishDetail) {
        dishDetail.showModal();
    }
}

/**
 * Finalize a configured dish and send it directly to the kitchen.
 */
async function handleFinalizeDish(dishData: { dishId: number; quantity: number; note: string }): Promise<void> {
    const choosingDish = selectedFood.value.dishes.find((dish) => dish.id === dishData.dishId);

    if (!choosingDish) return;

    const order: orderDish = {
        table: props.table.table_number,
        foodId: selectedFood.value.id,
        dishId: choosingDish.id,
        name: selectedFood.value.name,
        quantity: dishData.quantity,
        price: selectedFood.value.price + (choosingDish.additional_price || 0),
        cookingMethod: choosingDish.name,
        cookingMethodId: choosingDish.cooking_method_id,
        note: dishData.note || null,
        image: selectedFood.value.image || null,
    };

    const wasSuccessful = await submitDish(order);
    if (wasSuccessful) {
        dishDetailRef.value?.completeSubmission();
    }
}

/*
 * TEMP: Cart disabled for direct-to-kitchen ordering.
 * Cart quantity handlers, store lookup, and the fly-to-cart animation are
 * intentionally disabled because finalized dishes no longer enter a cart.
 */

/**
 * Finalize a food with one dish option and send it directly to the kitchen.
 * @param foodId
 */
async function addDish(foodId: number): Promise<void> {
    let targetFood: Food | undefined;
    for (const menu of props.menus) {
        targetFood = menu.foods.find((food) => food.id === foodId);
        if (targetFood) break;
    }

    if (!targetFood) return;

    const dish = targetFood.dishes[0];
    if (!dish || isDishSubmitting(dish.id)) return;

    await submitDish({
        table: props.table.table_number,
        foodId: targetFood.id,
        dishId: dish.id,
        name: targetFood.name,
        quantity: 1,
        price: targetFood.price + (dish.additional_price || 0),
        cookingMethod: dish.name,
        cookingMethodId: dish.cooking_method_id,
        note: null,
        image: targetFood.image || null,
    });
}

function isDishSubmitting(dishId?: number): boolean {
    return dishId !== undefined && submittingDishIds.value.has(dishId);
}

async function submitDish(order: orderDish): Promise<boolean> {
    if (order.dishId === null || isDishSubmitting(order.dishId)) return false;

    submittingDishIds.value = new Set(submittingDishIds.value).add(order.dishId);

    try {
        await axios.post(route('order.place'), {
            table_id: props.table.id,
            branch_id: props.table.branch_id,
            customer_id: user.value?.id ?? null,
            dishes: [
                {
                    dish_id: order.dishId,
                    quantity: order.quantity,
                    price: order.price,
                    note: order.note,
                },
            ],
        });

        historyStore.addHistory({ ...order });
        toast.success(`Đã gửi ${order.name} đến bếp`);

        return true;
    } catch (error) {
        console.error('Unable to send dish to kitchen:', error);
        toast.error('Không thể gửi món đến bếp. Vui lòng thử lại.');

        return false;
    } finally {
        const nextSubmittingDishIds = new Set(submittingDishIds.value);
        nextSubmittingDishIds.delete(order.dishId);
        submittingDishIds.value = nextSubmittingDishIds;
    }
}

/**
 * formatted price to money string
 * @param price
 */
function formatPrice(price: number): string {
    return price.toLocaleString('vi-VN', { style: 'currency', currency: 'VND' });
}

/**
 * format sold count
 * @param count
 */
function formatSoldCount(count: number): string {
    switch (true) {
        case count <= 1000:
            return count.toString();
        case count >= 1000 && count < 10000:
            return Math.floor(count / 100) * 100 + '+';
        case count >= 10000:
            return '9999+';
        default:
            break;
    }
    return count.toString();
}
</script>
