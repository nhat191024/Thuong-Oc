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
                <div class="flex flex-col">
                    <div class="my-5 flex h-24 w-full gap-4 px-4 text-lg font-light">
                        <img :src="food.image || '/images/demo.jpg'" alt="demo" class="h-full w-24 shrink-0 rounded-lg object-cover" />
                        <div class="flex flex-1 flex-col justify-between gap-1">
                            <div class="flex flex-col">
                                <p class="line-clamp-2 leading-tight font-semibold">
                                    {{ food.name }}
                                </p>
                                <p class="line-clamp-2 text-xs leading-tight font-medium text-primary">
                                    {{ food.dishes.length === 1 ? food.dishes[0].note : food.note }}
                                </p>
                            </div>
                            <div class="flex items-end justify-between">
                                <p class="font-semibold text-primary">{{ formatPrice(food.price) }}</p>
                                <div class="flex items-center gap-3">
                                    <button
                                        @click="tempQuantity--"
                                        class="transform rounded-l-full bg-primary p-1 transition-all duration-300 active:scale-125"
                                        :disabled="tempQuantity <= 1"
                                    >
                                        <MinusIcon class="size-5 text-white" />
                                    </button>
                                    <input
                                        type="number"
                                        v-model.number="tempQuantity"
                                        @blur="clampQuantity"
                                        min="1"
                                        max="99"
                                        class="w-12 [appearance:textfield] rounded border border-gray-300 px-1 py-0.5 text-center text-base font-semibold outline-none focus:border-primary [&::-webkit-inner-spin-button]:appearance-none [&::-webkit-outer-spin-button]:appearance-none"
                                    />
                                    <button
                                        @click="tempQuantity++"
                                        class="transform rounded-r-full bg-primary p-1 transition-all duration-300 active:scale-125"
                                        :disabled="tempQuantity >= 99"
                                    >
                                        <PlusIcon class="size-5 text-white" />
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div>
                    <div class="bg-stone-200 px-4 py-2">
                        <p class="text-primary">Cách chế biến (chọn 1)</p>
                    </div>
                    <div class="flex flex-col">
                        <div v-for="dish in food.dishes" :key="dish.id" class="flex flex-col border-b border-gray-300 px-4 py-2">
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
                    <div class="w-full">
                        <button
                            type="button"
                            class="btn w-full border-0 bg-primary outline-0 btn-primary"
                            :disabled="isSubmitting"
                            @click="finalizeDish"
                        >
                            <span v-if="isSubmitting" class="loading loading-sm loading-spinner"></span>
                            {{ submitButtonLabel }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </dialog>
</template>
<script setup lang="ts">
import { Food } from '@/types/menu';
import { computed, ref, watch } from 'vue';

//icons
import { MinusIcon, PlusIcon } from '@heroicons/vue/24/solid';

interface Props {
    food: Food;
    isSubmitting?: boolean;
    directToKitchen?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    isSubmitting: false,
    directToKitchen: false,
});

const emit = defineEmits<{
    finalizeDish: [dishData: { dishId: number; quantity: number; note: string }];
    addToCart: [dishData: { dishId: number; quantity: number; note: string }];
}>();

const tempQuantity = ref<number>(1);
const dishPicked = ref<number>(props.food.dishes[0]?.id || 1);
const tempNote = ref<string>('');
const submitButtonLabel = computed(() => {
    if (props.isSubmitting) {
        return 'Đang gửi đến bếp...';
    }

    return props.directToKitchen ? 'Chốt món' : 'Thêm vào giỏ hàng';
});

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
 * Ask the parent page to finalize and submit the selected dish.
 */
function finalizeDish() {
    if (props.isSubmitting) return;

    const choosingDish = props.food.dishes.find((dish) => dish.id === dishPicked.value);
    if (!choosingDish) return;

    const dishData = {
        dishId: choosingDish.id,
        quantity: tempQuantity.value,
        note: tempNote.value,
    };

    if (props.directToKitchen) {
        emit('finalizeDish', dishData);
        return;
    }

    emit('addToCart', dishData);
    resetModal();
}

function completeSubmission() {
    resetModal();
    const dishDetail = document.getElementById('dishDetail') as HTMLDialogElement;
    dishDetail?.close();
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
 * Clamp quantity between 1 and 99 on blur
 */
function clampQuantity() {
    if (!tempQuantity.value || tempQuantity.value < 1 || isNaN(tempQuantity.value)) {
        tempQuantity.value = 1;
    } else if (tempQuantity.value > 99) {
        tempQuantity.value = 99;
    } else {
        tempQuantity.value = Math.floor(tempQuantity.value);
    }
}

/**
 * formatted price to money string
 * @param price
 */
function formatPrice(price: number): string {
    return price.toLocaleString('vi-VN', { style: 'currency', currency: 'VND' });
}

defineExpose({
    completeSubmission,
});
</script>
