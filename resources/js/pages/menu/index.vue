<template>
    <div class="flex h-dvh w-dvw flex-col bg-base-100">
        <Nav :table-number="table.table_number"></Nav>

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
                    >
                        <figure class="group relative h-28 w-28 shrink-0 overflow-hidden rounded-xl">
                            <img
                                :src="food.image || '/images/demo.jpg'"
                                alt="demo"
                                class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-110"
                            />
                            <div class="absolute inset-0 bg-black/5 transition-colors group-hover:bg-transparent"></div>
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
                                    class="btn btn-circle bg-primary text-white shadow-lg shadow-primary/30 transition-transform btn-sm hover:scale-110"
                                    @click.stop="food.dishes.length > 1 ? showDishDetail(food) : addDish(food.id)"
                                >
                                    <PlusIcon class="size-5" />
                                </button>
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

        <DishDetail :food="selectedFood" @add-to-cart="handleAddToCart"></DishDetail>
        <Cart
            :bill-temp="billTemp"
            :table="table"
            @decrease-quantity="handleDecreaseQuantity"
            @remove-item="handleRemoveItem"
            @increase-quantity="handleIncreaseQuantity"
        ></Cart>
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
import { useOrderStore } from '@/stores/order';
import { Category } from '@/types/category';
import { Food, Menu } from '@/types/menu';
import { orderDish } from '@/types/order';
import { onMounted, ref } from 'vue';

// Components
import Cart from './partials/cart.vue';
import DishDetail from './partials/dish-detail.vue';
import Footer from './partials/footer.vue';
import History from './partials/history.vue';
import Nav from './partials/nav.vue';

//icons
import { HeartIcon, PlusIcon } from '@heroicons/vue/24/solid';

interface Props {
    table: {
        id: string;
        table_number: number;
        branch_id: number;
    };
    categories: Category[];
    menus: Menu[];
    currentOrder: orderDish[];
}

const props = defineProps<Props>();

const orderStore = useOrderStore();
const historyStore = useHistoryStore();

const activeTabId = ref<number>(props.categories[0]?.id || 0);
const menuContent = ref<HTMLElement | null>(null);
const tabContainer = ref<HTMLElement | null>(null);
const tabRefs = ref<Map<number, HTMLElement>>(new Map());
const isScrollingFromClick = ref(false);
const lastScrollTop = ref(0);

const billTemp = ref<orderDish[]>([]);
const selectedFood = ref<Food>(props.menus[0]?.foods[0]);

onMounted(() => {
    if (props.currentOrder) {
        historyStore.clearHistory();
        props.currentOrder.forEach((dish) => historyStore.addHistory(dish));
    }
    loadOrderFromLocalStorage();
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

/**
 * Load order from local storage
 */
function loadOrderFromLocalStorage() {
    if (orderStore.dishes.length === 0) return;
    orderStore.dishes.forEach((item: orderDish) => {
        if (Number(item.table) === props.table.table_number) {
            billTemp.value.push(item);
        } else {
            orderStore.clearDishes();
            return;
        }
    });
}

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
 * Handle adding dish to cart
 */
function handleAddToCart(dishData: { dishId: number; quantity: number; note: string }) {
    const choosingDish = selectedFood.value.dishes.find((dish) => dish.id === dishData.dishId);

    if (!choosingDish) return;

    const normalizedNote = dishData.note || null;

    // Check if the dish already exists in billTemp
    const existingOrderInBillTemp = billTemp.value.findIndex(
        (item) => item.foodId === selectedFood.value.id && item.dishId === choosingDish.id && item.note === normalizedNote,
    );

    if (existingOrderInBillTemp !== -1) {
        billTemp.value[existingOrderInBillTemp].quantity += dishData.quantity;

        // Find index in orderStore.dishes to update
        const existingOrderInStore = orderStore.dishes.findIndex(
            (item: orderDish) =>
                Number(item.table) === props.table.table_number &&
                item.foodId === selectedFood.value.id &&
                item.dishId === choosingDish.id &&
                item.note === normalizedNote,
        );

        if (existingOrderInStore !== -1) {
            orderStore.updateDishQuantity(existingOrderInStore, billTemp.value[existingOrderInBillTemp].quantity);
        }
    } else {
        const newOrder: orderDish = {
            table: props.table.table_number,
            foodId: selectedFood.value.id,
            dishId: choosingDish.id,
            name: selectedFood.value.name,
            quantity: dishData.quantity,
            price: selectedFood.value.price + (choosingDish.additional_price || 0),
            cookingMethod: choosingDish.name,
            cookingMethodId: choosingDish.cooking_method_id,
            note: normalizedNote,
        };

        billTemp.value.push(newOrder);
        orderStore.addDish(newOrder);
    }
}

/**
 * Handle decrease quantity from cart
 */
function handleDecreaseQuantity(index: number) {
    const dish = billTemp.value[index];
    if (!dish) return;

    // Tìm index trong orderStore
    const storeIndex = findDishInStore(dish);
    if (storeIndex !== -1) {
        orderStore.updateDishQuantity(storeIndex, dish.quantity);
    }
}

/**
 * Handle remove item from cart
 * @param index
 */
function handleRemoveItem(index: number) {
    const dish = billTemp.value[index];
    if (!dish) return;

    // Tìm index trong orderStore
    const storeIndex = findDishInStore(dish);
    if (storeIndex !== -1) {
        orderStore.removeDish(storeIndex);
    }
}

/**
 * Handle increase quantity from cart
 * @param index
 */
function handleIncreaseQuantity(index: number) {
    const dish = billTemp.value[index];
    if (!dish) return;

    // Tìm index trong orderStore
    const storeIndex = findDishInStore(dish);
    if (storeIndex !== -1) {
        orderStore.updateDishQuantity(storeIndex, dish.quantity);
    }
}

/**
 * Find dish in orderStore by matching properties
 * @param dish
 */
function findDishInStore(dish: orderDish): number {
    return orderStore.dishes.findIndex(
        (item: orderDish) => item.table === dish.table && item.foodId === dish.foodId && item.dishId === dish.dishId && item.note === dish.note,
    );
}

/**
 * Add dish directly to cart (for foods without dish options or with only one dish)
 * @param foodId
 */
function addDish(foodId: number) {
    // Tìm food từ menus
    let targetFood: Food | undefined;
    for (const menu of props.menus) {
        targetFood = menu.foods.find((food) => food.id === foodId);
        if (targetFood) break;
    }

    if (!targetFood) return;

    let dish = null;
    let dishId = null;
    let cookingMethod = null;
    let cookingMethodId = null;
    let additionalPrice = 0;

    if (targetFood.dishes && targetFood.dishes.length > 0) {
        dish = targetFood.dishes[0];
        dishId = dish.id;
        cookingMethod = dish.name;
        cookingMethodId = dish.cooking_method_id;
        additionalPrice = dish.additional_price || 0;
    }

    // Kiểm tra món đã tồn tại trong giỏ hàng chưa
    const existingOrderIndex = billTemp.value.findIndex((item) => item.foodId === targetFood!.id && item.dishId === dishId && item.note === null);

    if (existingOrderIndex !== -1) {
        // Nếu đã tồn tại, tăng quantity
        billTemp.value[existingOrderIndex].quantity += 1;

        // Cập nhật trong orderStore
        const storeIndex = findDishInStore(billTemp.value[existingOrderIndex]);
        if (storeIndex !== -1) {
            orderStore.updateDishQuantity(storeIndex, billTemp.value[existingOrderIndex].quantity);
        }
    } else {
        // Nếu chưa có, thêm món mới
        const newOrder: orderDish = {
            table: props.table.table_number,
            foodId: targetFood.id,
            dishId: dishId,
            name: targetFood.name,
            quantity: 1,
            price: targetFood.price + additionalPrice,
            cookingMethod: cookingMethod,
            cookingMethodId: cookingMethodId,
            note: null,
        };

        billTemp.value.push(newOrder);
        orderStore.addDish(newOrder);
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
