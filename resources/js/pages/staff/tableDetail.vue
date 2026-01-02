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
                @update-bill-quantity="updateBillQuantity"
                @update-cart-quantity="updateCartQuantity"
                @remove-from-cart="removeFromCart"
                @send-order="sendOrder"
                @update-bill="updateBill"
            />

            <!-- Right Panel: Menu -->
            <MenuPanel :menus="menus" v-model:selected-menu="selectedMenu" @food-click="handleFoodClick" />
        </div>

        <!-- Dish Detail Modal -->
        <DishDetail v-if="selectedFood" :food="selectedFood" @add-to-cart="handleAddToCart" />

        <!-- Confirm Order Modal -->
        <ConfirmOrderModal :item-count="cartItems.length" :total-amount="totalAmount" @confirm="placeOrder" />

        <NotificationModal
            v-model:isOpen="isNotificationOpen"
            :title="notificationTitle"
            :message="notificationMessage"
        />
    </div>
</template>

<script setup lang="ts">
import { useHistoryStore } from '@/stores/history';
import { Category } from '@/types/category';
import { Food, Menu } from '@/types/menu';
import { orderDish } from '@/types/order';
import { useForm } from '@inertiajs/vue3';
import { computed, onMounted, ref } from 'vue';

import DishDetail from '../menu/partials/dish-detail.vue';
import ConfirmOrderModal from './partials/ConfirmOrderModal.vue';
import MenuPanel from './partials/MenuPanel.vue';
import Nav from './partials/nav.vue';
    import OrderPanel from './partials/OrderPanel.vue';
import NotificationModal from '@/pages/components/NotificationModal.vue';

interface Props {
    table: {
        id: string;
        table_number: number;
        branch_id: number;
    };
    menus: Menu[];
    categories: Category[];
    currentOrder: orderDish[];
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

// Initialize
onMounted(() => {
    // Deep copy to avoid mutating props directly if needed, though props are readonly
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
        // Wait for DOM update then show modal
        setTimeout(() => {
            const modal = document.getElementById('dishDetail') as HTMLDialogElement;
            if (modal) modal.showModal();
        }, 0);
    } else {
        // Add default dish directly
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

    // Close modal
    const modal = document.getElementById('dishDetail') as HTMLDialogElement;
    if (modal) modal.close();
    selectedFood.value = null;
}

function addToCart(item: orderDish) {
    // Check if same item exists in cart
    const existingItem = cartItems.value.find((i) => i.foodId === item.foodId && i.dishId === item.dishId && i.note === item.note);

    if (existingItem) {
        existingItem.quantity += item.quantity;
    } else {
        cartItems.value.push(item);
    }

    // Switch to cart tab to show feedback
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
            quantity: dish.quantity,
            price: dish.price,
            note: dish.note,
        })),
    });

    form.post(route('order.place'), {
        onSuccess: () => {
            // Add to history store if needed, though this is staff view
            cartItems.value.forEach((dish) => historyStore.addHistory({ ...dish }));

            // Move items to bill locally for immediate feedback
            billItems.value.push(...cartItems.value);
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
    // Calculate differences to send only new/added quantities
    const itemsToAdd: any[] = [];

    billItems.value.forEach((currentItem) => {
        const originalItem = props.currentOrder.find(
            (i) => i.foodId === currentItem.foodId && i.dishId === currentItem.dishId && i.note === currentItem.note,
        );

        if (originalItem) {
            const quantityDiff = currentItem.quantity - originalItem.quantity;
            if (quantityDiff > 0) {
                itemsToAdd.push({
                    dish_id: currentItem.dishId,
                    quantity: quantityDiff,
                    price: currentItem.price,
                    note: currentItem.note,
                });
            }
        } else {
            // New item added directly to bill tab? (Shouldn't happen with current UI flow, but safe to handle)
            itemsToAdd.push({
                dish_id: currentItem.dishId,
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
</script>
