<template>
    <div class="flex h-screen w-full flex-col bg-base-100">
        <Nav :center_text="'Bàn ' + props.table.table_number" :use_back_button="true" :back-url="route('staff.tables')" />

        <div class="flex flex-1 overflow-hidden">
            <!-- Left Panel: Bill & Cart -->
            <OrderPanel
                :bill-items="billItems"
                :cart-items="cartItems"
                v-model:active-tab="activeTab"
                :total-amount="totalAmount"
                :inactive-tables="inactiveTables"
                @update-bill-quantity="updateBillQuantity"
                @update-cart-quantity="updateCartQuantity"
                @remove-from-cart="removeFromCart"
                @send-order="sendOrder"
                @update-bill="updateBill"
                @payment="handlePayment"
                @move-table="handleMoveTable"
            />

            <!-- Right Panel: Menu -->
            <MenuPanel :menus="menus" v-model:selected-menu="selectedMenu" @food-click="handleFoodClick" @open-custom-dish="openCustomDishModal" />
        </div>

        <!-- Dish Detail Modal -->
        <DishDetail v-if="selectedFood" :food="selectedFood" @add-to-cart="handleAddToCart" />

        <!-- Custom Dish Modal -->
        <CustomDishModal :kitchens="kitchens" @add-custom-dish="handleAddCustomDish" />

        <!-- Confirm Order Modal -->
        <ConfirmOrderModal :item-count="cartItems.length" :total-amount="totalAmount" @confirm="placeOrder" />

        <NotificationModal v-model:isOpen="isNotificationOpen" :title="notificationTitle" :message="notificationMessage" />
    </div>
</template>

<script setup lang="ts">
import { useHistoryStore } from '@/stores/history';
import { AppPageProps } from '@/types';
import { Category } from '@/types/category';
import { Food, Menu } from '@/types/menu';
import { orderDish } from '@/types/order';
import { router, useForm, usePage } from '@inertiajs/vue3';
import { computed, onMounted, ref } from 'vue';

import NotificationModal from '@/pages/components/NotificationModal.vue';
import Nav from '../components/nav.vue';
import DishDetail from '../menu/partials/dish-detail.vue';
import ConfirmOrderModal from './partials/ConfirmOrderModal.vue';
import CustomDishModal from './partials/CustomDishModal.vue';
import MenuPanel from './partials/MenuPanel.vue';
import OrderPanel from './partials/OrderPanel.vue';

const page = usePage<AppPageProps>();

interface Props {
    table: {
        id: string;
        table_number: number;
        branch_id: number;
    };
    menus: Menu[];
    categories: Category[];
    currentOrder: orderDish[];
    inactiveTables?: { id: string; table_number: number }[];
    kitchens: { id: number; name: string }[];
}

const props = defineProps<Props>();

const historyStore = useHistoryStore();

// State
const activeTab = ref<'bill' | 'cart'>('bill');
const selectedMenu = ref<Menu | null>(null);
const selectedFood = ref<Food | null>(null);

const billItems = ref<orderDish[]>([]);
const cartItems = ref<orderDish[]>([]);

// Notification State
const isNotificationOpen = ref(false);
const notificationTitle = ref('');
const notificationMessage = ref('');

function showNotification(title: string, message: string) {
    notificationTitle.value = title;
    notificationMessage.value = message;
    isNotificationOpen.value = true;
}

function handleMoveTable(newTableId: string) {
    router.post(
        route('staff.table.move', props.table.id),
        {
            new_table_id: newTableId,
        },
        {
            onSuccess: () => {
                const status = page.props.flash.error ? 'Thất bại' : 'Thành công';
                const message = page.props.flash.error ?? page.props.flash.success;
                showNotification(status, message || '');
            },
            onError: (errors) => {
                showNotification('Lỗi', errors.error || 'Có lỗi xảy ra khi chuyển bàn');
            },
        },
    );
}

// Initialize
onMounted(() => {
    if (props.currentOrder) {
        billItems.value = JSON.parse(JSON.stringify(props.currentOrder));
    }
});

// Computed
const totalAmount = computed(() => {
    const billTotal = billItems.value.reduce((sum, item) => sum + item.price * item.quantity, 0);
    const cartTotal = cartItems.value.reduce((sum, item) => sum + item.price * item.quantity, 0);
    return activeTab.value === 'bill' ? billTotal : cartTotal;
});

// Methods
function handleFoodClick(food: Food) {
    if (food.dishes.length > 1) {
        selectedFood.value = food;
        setTimeout(() => {
            const modal = document.getElementById('dishDetail') as HTMLDialogElement;
            if (modal) modal.showModal();
        }, 0);
    } else {
        const defaultDish = food.dishes[0];
        const newItem: orderDish = {
            table: props.table.table_number,
            foodId: food.id,
            dishId: defaultDish?.id || null,
            name: food.name,
            quantity: 1,
            price: food.is_discounted ? food.discount_price : food.price,
            cookingMethod: defaultDish?.name || null,
            cookingMethodId: defaultDish?.cooking_method_id || null,
            note: null,
        };
        addToCart(newItem);
    }
}

function handleAddToCart(dishData: { dishId: number; quantity: number; note: string }) {
    if (!selectedFood.value) return;

    const food = selectedFood.value;
    const dish = food.dishes.find((d) => d.id === dishData.dishId);

    const newItem: orderDish = {
        table: props.table.table_number,
        foodId: food.id,
        dishId: dishData.dishId,
        name: food.name,
        quantity: dishData.quantity,
        price: (food.is_discounted ? food.discount_price : food.price) + (dish?.additional_price || 0),
        cookingMethod: dish?.name || null,
        cookingMethodId: dish?.cooking_method_id || null,
        note: dishData.note,
    };

    addToCart(newItem);

    const modal = document.getElementById('dishDetail') as HTMLDialogElement;
    if (modal) modal.close();
    selectedFood.value = null;
}

function openCustomDishModal() {
    const modal = document.getElementById('customDishModal') as HTMLDialogElement;
    if (modal) modal.showModal();
}

function handleAddCustomDish(data: { name: string; price: number; quantity: number; note: string; kitchen_id: number | null }) {
    const newItem: orderDish = {
        table: props.table.table_number,
        foodId: null,
        dishId: null,
        custom_dish_name: data.name,
        custom_kitchen_id: data.kitchen_id,
        name: data.name,
        quantity: data.quantity,
        price: data.price,
        cookingMethod: null,
        cookingMethodId: null,
        note: data.note,
    };
    addToCart(newItem);
}

function addToCart(item: orderDish) {
    const existingItem = cartItems.value.find((i) => {
        if (item.custom_dish_name) {
            return i.custom_dish_name === item.custom_dish_name && i.price === item.price && i.note === item.note;
        }
        return i.foodId === item.foodId && i.dishId === item.dishId && i.note === item.note;
    });

    if (existingItem) {
        existingItem.quantity += item.quantity;
    } else {
        cartItems.value.push(item);
    }

    activeTab.value = 'cart';
}

function updateCartQuantity(index: number, delta: number) {
    const item = cartItems.value[index];
    const newQuantity = item.quantity + delta;

    if (newQuantity <= 0) {
        removeFromCart(index);
    } else {
        item.quantity = newQuantity;
    }
}

function removeFromCart(index: number) {
    cartItems.value.splice(index, 1);
}

function updateBillQuantity(index: number, delta: number) {
    const item = billItems.value[index];
    const newQuantity = item.quantity + delta;

    if (newQuantity > 0) {
        item.quantity = newQuantity;
    }
}

function sendOrder() {
    const confirmModal = document.getElementById('confirmOrder') as HTMLDialogElement;
    if (confirmModal) {
        confirmModal.showModal();
    }
}

function placeOrder() {
    const form = useForm({
        table_id: props.table.id,
        branch_id: props.table.branch_id,
        dishes: cartItems.value.map((dish) => ({
            dish_id: dish.dishId,
            custom_kitchen_id: dish.custom_kitchen_id,
            custom_dish_name: dish.custom_dish_name,
            quantity: dish.quantity,
            price: dish.price,
            note: dish.note,
        })),
    });

    form.post(route('order.place'), {
        onSuccess: () => {
            cartItems.value.forEach((dish) => historyStore.addHistory({ ...dish }));

            cartItems.value.forEach((item) => {
                console.log('Adding item to bill:', item);
                const existingItem = billItems.value.find((i) => {
                    console.log('Checking against bill item:', i);
                    if (item.custom_dish_name) {
                        return i.custom_dish_name === item.custom_dish_name && i.price === item.price && i.note === item.note;
                    }
                    return i.foodId === item.foodId && i.dishId === item.dishId && i.note === item.note;
                });

                console.log('Existing item found:', existingItem);

                if (existingItem) {
                    existingItem.quantity += item.quantity;
                } else {
                    console.log('Pushing new item to bill:', item);
                    billItems.value.push(item);
                }
            });

            cartItems.value = [];
            activeTab.value = 'bill';

            const confirmModal = document.getElementById('confirmOrder') as HTMLDialogElement;
            if (confirmModal) {
                confirmModal.close();
            }
        },
        onError: (errors) => {
            console.error(errors);
            showNotification('Lỗi', 'Có lỗi xảy ra khi gửi đơn!');
        },
    });
}

function updateBill() {
    const itemsToAdd: any[] = [];

    billItems.value.forEach((currentItem) => {
        const originalItem = props.currentOrder.find((i) => {
            if (currentItem.custom_dish_name) {
                return (
                    i.custom_dish_name === currentItem.custom_dish_name &&
                    i.price === currentItem.price &&
                    i.note === currentItem.note
                );
            }
            return i.foodId === currentItem.foodId && i.dishId === currentItem.dishId && i.note === currentItem.note;
        });

        if (originalItem) {
            const quantityDiff = currentItem.quantity - originalItem.quantity;
            if (quantityDiff > 0) {
                itemsToAdd.push({
                    custom_kitchen_id: currentItem.custom_kitchen_id,
                    quantity: quantityDiff,
                    price: currentItem.price,
                    note: currentItem.note,
                });
            }
        } else {
            itemsToAdd.push({
                dish_id: currentItem.dishId,
                custom_dish_name: currentItem.custom_dish_name,
                custom_kitchen_id: currentItem.custom_kitchen_id,
                quantity: currentItem.quantity,
                price: currentItem.price,
                note: currentItem.note,
            });
        }
    });

    if (itemsToAdd.length === 0) {
        showNotification('Thông báo', 'Không có thay đổi nào để cập nhật (hoặc bạn đang giảm số lượng - chức năng này chưa được hỗ trợ).');
        return;
    }

    const form = useForm({
        table_id: props.table.id,
        branch_id: props.table.branch_id,
        dishes: itemsToAdd,
    });

    form.post(route('order.place'), {
        onSuccess: () => {
            showNotification('Thành công', 'Đã cập nhật Bill thành công!');
            // Reload to get fresh data
            window.location.reload();
        },
        onError: (errors) => {
            console.error(errors);
            showNotification('Lỗi', 'Có lỗi xảy ra khi cập nhật bill!');
        },
    });
}

function handlePayment() {
    router.visit(route('staff.table.bill', props.table.id));
}
</script>
