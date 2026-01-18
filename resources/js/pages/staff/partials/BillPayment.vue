<template>
    <div class="card w-full max-w-sm border border-base-200 bg-base-100 shadow-xl">
        <div class="card-body items-center text-center">
            <h2 class="card-title">Quét mã thanh toán</h2>
            <p class="mb-4 text-sm text-base-content/70">Mã dành cho nhân viên quét bằng máy POS để thanh toán và in đơn</p>

            <div class="rounded-xl border border-base-300 bg-white p-4 shadow-inner">
                <div class="flex h-48 w-48 items-center justify-center rounded-lg bg-white">
                    <QrcodeVue :value="qrCodeValue" :size="180" level="H" />
                </div>
            </div>

            <div class="mt-4 text-xs text-base-content/50">Mã đơn: #{{ table.bill?.id || '---' }}</div>

            <div class="divider w-full">Hoặc</div>

            <div class="form-control w-full">
                <label class="label">
                    <span class="label-text font-medium">Thanh toán nhanh (Không in đơn)</span>
                </label>
                <div class="mt-1 flex gap-2">
                    <select
                        class="select-bordered select flex-1 outline-primary focus:border-primary"
                        :value="selectedPaymentMethod"
                        @change="$emit('update:selectedPaymentMethod', ($event.target as HTMLSelectElement).value)"
                    >
                        <option disabled selected value="">Chọn phương thức</option>
                        <option v-for="method in paymentMethods" :key="method.value" :value="method.value">
                            {{ method.label }}
                        </option>
                    </select>
                    <button
                        class="btn text-white btn-primary"
                        :disabled="!selectedPaymentMethod || isProcessing"
                        @click="$emit('confirmPayment')"
                    >
                        Xác nhận
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import QrcodeVue from 'qrcode.vue';
import { computed } from 'vue';

interface PaymentMethod {
    value: string;
    label: string;
}

interface Props {
    table: any;
    paymentMethods: PaymentMethod[];
    selectedPaymentMethod: string;
    isProcessing: boolean;
}

const props = defineProps<Props>();

defineEmits(['update:selectedPaymentMethod', 'confirmPayment']);

const qrCodeValue = computed(() => `thuongoc://table_bill?id=${props.table.id}`);
</script>
