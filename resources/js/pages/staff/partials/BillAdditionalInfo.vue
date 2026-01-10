<template>
    <div class="collapse-arrow collapse mb-4 w-full max-w-sm border border-base-200 bg-base-100 shadow-xl">
        <input type="checkbox" />
        <div class="collapse-title text-lg font-bold">Thêm thông tin</div>
        <div class="collapse-content">
            <div class="mb-4">
                <label class="label pt-0 pl-0"><span class="label-text font-medium">Khách hàng thành viên</span></label>
                <div class="join w-full">
                    <input
                        :value="customerPhone"
                        @input="$emit('update:customerPhone', ($event.target as HTMLInputElement).value)"
                        type="text"
                        placeholder="Nhập SĐT khách..."
                        class="input-bordered input input-sm join-item w-full focus:border-primary focus:outline-none"
                        :disabled="isAttachingCustomer"
                        @keyup.enter="$emit('attachCustomer')"
                    />
                    <button
                        class="btn join-item text-white btn-sm btn-primary"
                        @click="$emit('attachCustomer')"
                        :disabled="!customerPhone || isAttachingCustomer"
                    >
                        <span v-if="isAttachingCustomer" class="loading loading-xs loading-spinner"></span>
                        <span v-else>Tìm</span>
                    </button>
                </div>
                <label class="label pt-0 pl-0"
                    ><span class="text-xs font-medium text-base-content/70"
                        >Nhập khi đơn đã có khách sẽ ghi đè khách hiện tại</span
                    ></label
                >
            </div>

            <div>
                <label class="label pt-0 pl-0"><span class="label-text font-medium">Mã giảm giá</span></label>
                <div class="join w-full">
                    <input
                        :value="discountCode"
                        @input="$emit('update:discountCode', ($event.target as HTMLInputElement).value)"
                        type="text"
                        placeholder="Nhập mã..."
                        class="input-bordered input input-sm join-item w-full focus:border-primary focus:outline-none"
                        :disabled="isApplyingDiscount"
                        @keyup.enter="$emit('applyDiscount')"
                    />
                    <button
                        class="btn join-item text-white btn-sm btn-primary"
                        @click="$emit('applyDiscount')"
                        :disabled="!discountCode || isApplyingDiscount"
                    >
                        <span v-if="isApplyingDiscount" class="loading loading-xs loading-spinner"></span>
                        <span v-else>Áp dụng</span>
                    </button>
                </div>
            </div>

            <div class="mt-4 flex gap-2">
                <button
                    v-if="table?.bill?.customer"
                    class="btn flex-1 btn-outline btn-sm btn-error"
                    @click="$emit('removeCustomer')"
                    :disabled="isRemovingCustomer"
                >
                    <span v-if="isRemovingCustomer" class="loading loading-xs loading-spinner"></span>
                    <span v-else>Xóa khách</span>
                </button>
                <button
                    v-if="discountAmount > 0"
                    class="btn flex-1 btn-outline btn-sm btn-error"
                    @click="$emit('removeDiscount')"
                    :disabled="isRemovingDiscount"
                >
                    <span v-if="isRemovingDiscount" class="loading loading-xs loading-spinner"></span>
                    <span v-else>Xóa Voucher</span>
                </button>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
interface Props {
    table: any;
    customerPhone: string;
    isAttachingCustomer: boolean;
    discountCode: string;
    isApplyingDiscount: boolean;
    discountAmount: number;
    isRemovingCustomer?: boolean;
    isRemovingDiscount?: boolean;
}

const props = defineProps<Props>();

defineEmits([
    'update:customerPhone',
    'update:discountCode',
    'attachCustomer',
    'applyDiscount',
    'removeCustomer',
    'removeDiscount'
]);
</script>
