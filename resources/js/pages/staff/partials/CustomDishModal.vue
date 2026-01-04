<template>
    <dialog id="customDishModal" class="modal modal-bottom sm:modal-middle">
        <div class="modal-box overflow-hidden p-0">
            <div class="flex items-center justify-between border-b border-base-300 bg-base-200 px-6 py-4">
                <h3 class="text-lg font-bold">Thêm món ngoài menu</h3>
                <form method="dialog">
                    <button class="btn btn-circle btn-ghost btn-sm" @click="closeModal">✕</button>
                </form>
            </div>

            <div class="flex flex-col gap-5 p-6">
                <div class="form-control w-full">
                    <label class="label pt-0">
                        <span class="label-text font-medium">Tên món <span class="text-error">*</span></span>
                    </label>
                    <input
                        type="text"
                        v-model="form.name"
                        placeholder="Ví dụ: Trà đá, Khăn lạnh..."
                        class="input-bordered input w-full focus:input-primary"
                    />
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="form-control w-full">
                        <label class="label pt-0">
                            <span class="label-text font-medium">Giá tiền <span class="text-error">*</span></span>
                        </label>
                        <div class="relative">
                            <input type="number" v-model="form.price" placeholder="0" class="input-bordered input w-full pr-12 focus:input-primary" />
                            <span class="absolute top-1/2 right-4 -translate-y-1/2 text-sm font-bold text-base-content/50">đ</span>
                        </div>
                    </div>

                    <div class="form-control w-full">
                        <label class="label pt-0">
                            <span class="label-text font-medium">Số lượng</span>
                        </label>
                        <div class="flex h-12 items-center overflow-hidden rounded-lg border border-base-300">
                            <button
                                class="btn btn-square h-full rounded-none btn-ghost btn-sm hover:bg-base-200"
                                @click="decreaseQuantity"
                                :disabled="form.quantity <= 1"
                            >
                                <MinusIcon class="size-4" />
                            </button>
                            <input
                                type="number"
                                v-model="form.quantity"
                                class="h-full w-full flex-1 [appearance:textfield] border-none bg-transparent text-center font-bold focus:ring-0 [&::-webkit-inner-spin-button]:appearance-none [&::-webkit-outer-spin-button]:appearance-none"
                            />
                            <button class="btn btn-square h-full rounded-none btn-ghost btn-sm hover:bg-base-200" @click="form.quantity++">
                                <PlusIcon class="size-4" />
                            </button>
                        </div>
                    </div>
                </div>

                <div class="form-control w-full">
                    <label class="label pt-0">
                        <span class="label-text font-medium">Bếp nấu</span>
                    </label>
                    <select v-model="form.kitchen_id" class="select select-bordered w-full focus:select-primary">
                        <option :value="null">Chọn bếp (Tùy chọn)</option>
                        <option v-for="kitchen in kitchens" :key="kitchen.id" :value="kitchen.id">
                            {{ kitchen.name }}
                        </option>
                    </select>
                </div>

                <div class="form-control flex w-full flex-col gap-1">
                    <label class="label pt-0">
                        <span class="label-text font-medium">Ghi chú</span>
                    </label>
                    <textarea
                        v-model="form.note"
                        class="textarea-bordered textarea h-24 w-full resize-none focus:textarea-primary"
                        placeholder="Ghi chú thêm cho món này..."
                    ></textarea>
                </div>
            </div>

            <div class="m-0 modal-action bg-base-100 px-6 pt-2 pb-6">
                <button class="btn btn-ghost" @click="closeModal">Hủy bỏ</button>
                <button class="btn px-8 btn-primary" @click="submit" :disabled="!isValid">Thêm món</button>
            </div>
        </div>
        <form method="dialog" class="modal-backdrop">
            <button @click="closeModal">close</button>
        </form>
    </dialog>
</template>

<script setup lang="ts">
import { MinusIcon, PlusIcon } from '@heroicons/vue/24/outline';
import { computed, reactive } from 'vue';

interface Kitchen {
    id: number;
    name: string;
}

defineProps<{
    kitchens: Kitchen[];
}>();

const emit = defineEmits(['add-custom-dish']);

const form = reactive({
    name: '',
    price: 0,
    quantity: 1,
    note: '',
    kitchen_id: null as number | null,
});

const isValid = computed(() => form.name.trim() !== '' && form.price > 0);

function decreaseQuantity() {
    if (form.quantity > 1) {
        form.quantity--;
    }
}

function submit() {
    if (!isValid.value) return;

    emit('add-custom-dish', { ...form });
    closeModal();
    resetForm();
}

    form.kitchen_id = null;
function closeModal() {
    const modal = document.getElementById('customDishModal') as HTMLDialogElement;
    if (modal) modal.close();
}

function resetForm() {
    form.name = '';
    form.price = 0;
    form.quantity = 1;
    form.note = '';
}
</script>
