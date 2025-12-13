<template>
    <!-- Modal chi tiết món ăn -->
    <dialog id="dishDetail" class="modal modal-bottom">
        <form method="dialog" class="modal-backdrop">
            <button @click="resetModal">close</button>
        </form>
        <div class="modal-box p-0">
            <form method="dialog">
                <button @click="resetModal" class="btn absolute top-2 right-2 btn-circle btn-ghost btn-md">✕</button>
            </form>
            <div>
                <h3 class="pt-2 text-center text-xl font-bold">Thêm món ăn</h3>
                <div class="flex flex-col">
                    <div class="my-5 grid h-24 w-full grid-cols-12 grid-rows-3 px-4 text-lg font-light">
                        <img src="/images/demo.jpg" alt="demo" class="col-span-3 row-span-full h-full w-full rounded-lg" />
                        <p class="col-span-full col-start-4 row-span-1 pl-3 font-medium">
                            {{ food.name }}
                        </p>
                        <p class="col-span-4 row-start-3 pl-3 font-semibold text-primary">{{ formatPrice(food.price) }}</p>
                        <div class="col-span-full col-start-10 row-start-3 flex h-full w-full items-center gap-3 place-self-center">
                            <button
                                @click="tempQuantity--"
                                class="transform rounded-l-full bg-primary p-1 transition-all duration-300 active:scale-125"
                                :disabled="tempQuantity <= 1"
                            >
                                <MinusIcon class="size-5 text-white" />
                            </button>
                            <p>{{ tempQuantity }}</p>
                            <button
                                @click="tempQuantity++"
                                class="transform rounded-r-full bg-primary p-1 transition-all duration-300 active:scale-125"
                            >
                                <PlusIcon class="size-5 text-white" />
                            </button>
                        </div>
                    </div>
                </div>
                <div>
                    <div class="bg-stone-200 px-4 py-2">
                        <p class="text-primary">Cách chế biến (chọn 1)</p>
                    </div>
                    <div class="flex flex-col">
                        <div v-for="dish in food.dishes" class="flex flex-col border-b border-gray-300 px-4 py-2">
                            <p class="font-medium text-black">
                                {{ dish.name }}
                            </p>
                            <div class="flex justify-between">
                                <p class="text-primary">{{ formatPrice(dish.additional_price) }}</p>
                                <input
                                    type="radio"
                                    name="cookingMethods"
                                    v-model="dishPicked"
                                    :value="dish.id"
                                    class="radio border-primary text-primary radio-primary"
                                />
                            </div>
                        </div>
                    </div>
                </div>
                <div>
                    <div class="my-2">
                        <label for="note" class="px-4">Ghi Chú:</label>
                        <textarea
                            id="note"
                            class="w-full px-4 py-1 textarea-md outline-none"
                            placeholder="📝 Ghi chú cho quán"
                            v-model="tempNote"
                        ></textarea>
                    </div>
                </div>
            </div>
            <div>
                <div class="modal-action mt-0 p-4">
                    <form method="dialog" class="w-full">
                        <button class="btn w-full border-0 bg-primary outline-0 btn-primary" @click="addDish(food.id)">Thêm vào giỏ hàng</button>
                    </form>
                </div>
            </div>
        </div>
    </dialog>
</template>
<script setup lang="ts">
import { Food } from '@/types/menu';
import { ref, watch } from 'vue';

//icons
import { MinusIcon, PlusIcon } from '@heroicons/vue/24/solid';

interface Props {
    food: Food;
}

const props = defineProps<Props>();

const emit = defineEmits<{
    addToCart: [dishData: { dishId: number; quantity: number; note: string }];
}>();

const tempQuantity = ref<number>(1);
const dishPicked = ref<number>(props.food.dishes[0]?.id || 1);
const tempNote = ref<string>('');

// Watch for food changes and reset dishPicked to first dish of new food
watch(
    () => props.food,
    (newFood) => {
        if (newFood && newFood.dishes && newFood.dishes.length > 0) {
            dishPicked.value = newFood.dishes[0].id;
            tempQuantity.value = 1;
            tempNote.value = '';
        }
    },
    { immediate: true },
);

/**
 * Add dish to cart
 * @param id
 */
function addDish(id: number) {
    const choosingDish = props.food.dishes.find((dish) => dish.id === dishPicked.value);
    if (!choosingDish) return;

    emit('addToCart', {
        dishId: choosingDish.id,
        quantity: tempQuantity.value,
        note: tempNote.value,
    });

    // Reset form
    tempQuantity.value = 1;
    dishPicked.value = props.food.dishes[0]?.id || 1;
    tempNote.value = '';
}

/**
 * Reset modal to default values
 */
function resetModal() {
    tempQuantity.value = 1;
    dishPicked.value = props.food.dishes[0]?.id || 1;
    tempNote.value = '';
}

/**
 * formatted price to money string
 * @param price
 */
function formatPrice(price: number): string {
    return price.toLocaleString('vi-VN', { style: 'currency', currency: 'VND' });
}
</script>
