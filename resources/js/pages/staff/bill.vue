<template>
    <div class="flex min-h-screen flex-col bg-base-100">
        <div class="navbar bg-base-100 shadow-sm">
            <div class="flex-none">
                <Link :href="route('staff.table.show', table.id)" class="btn btn-square btn-ghost">
                    <ChevronLeftIcon class="size-6" />
                </Link>
            </div>
            <div class="flex-1">
                <h1 class="text-xl font-bold">Hóa đơn - Bàn {{ table.table_number }}</h1>
            </div>
        </div>

        <div class="flex flex-1 flex-col p-4">
            <div v-if="page.props.flash.success" role="alert" class="mb-4 alert w-full alert-success">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 shrink-0 stroke-current" fill="none" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>{{ page.props.flash.success }}</span>
            </div>
            <div v-if="page.props.flash.error && showError" role="alert" class="mb-4 alert w-full alert-error">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 shrink-0 stroke-current" fill="none" viewBox="0 0 24 24">
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"
                    />
                </svg>
                <span>{{ page.props.flash.error }}</span>
            </div>

            <div class="flex flex-col md:gap-8 lg:flex-row">
                <div class="flex-1 space-y-4">
                    <div class="card border border-base-200 bg-base-100 shadow-xl">
                        <div class="card-body p-4">
                            <h2 class="mb-4 card-title text-lg">Chi tiết đơn hàng</h2>

                            <div class="mb-4 grid grid-cols-2 gap-2 rounded-lg bg-base-200/50 p-3 text-sm">
                                <div class="text-base-content/70">Mã hóa đơn:</div>
                                <div class="text-right font-medium">#{{ table.bill?.id }}</div>

                                <div class="text-base-content/70">Thời gian vào:</div>
                                <div class="text-right font-medium">{{ formatDate(table.bill?.time_in) }}</div>

                                <template v-if="table.bill?.customer">
                                    <div class="text-base-content/70">Khách hàng:</div>
                                    <div class="text-right font-medium">{{ table.bill.customer.name }}</div>
                                </template>
                            </div>

                            <div class="overflow-x-auto">
                                <table class="table w-full table-zebra">
                                    <thead>
                                        <tr>
                                            <th>Món</th>
                                            <th class="text-center">SL</th>
                                            <th class="text-right">Đơn giá</th>
                                            <th class="text-right">Thành tiền</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="(item, index) in billDetails" :key="index">
                                            <td>
                                                <div class="font-bold">{{ item.name }}</div>
                                                <div v-if="item.cookingMethod" class="text-xs opacity-70">{{ item.cookingMethod }}</div>
                                                <div v-if="item.note" class="text-xs italic opacity-60">{{ item.note }}</div>
                                            </td>
                                            <td class="text-center">{{ item.quantity }}</td>
                                            <td class="text-right">{{ formatPrice(item.price) }}</td>
                                            <td class="text-right font-semibold">{{ formatPrice(item.total) }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="divider my-2"></div>

                            <div v-if="discountAmount > 0" class="flex items-center justify-between text-sm">
                                <span>Giảm giá ({{ discountPercent }}%):</span>
                                <div class="flex items-center gap-2">
                                    <span class="text-error">-{{ formatPrice(discountAmount) }}</span>
                                    <button
                                        @click="removeDiscount"
                                        class="btn text-error btn-ghost btn-xs"
                                        :disabled="isRemovingDiscount"
                                        title="Hủy mã giảm giá"
                                    >
                                        <XMarkIcon class="size-4" />
                                    </button>
                                </div>
                            </div>

                            <div class="flex items-center justify-between text-xl font-bold">
                                <span>Tổng cộng:</span>
                                <span class="text-primary">{{ formatPrice(finalTotal) }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- QR Code Section -->
                <div class="mt-4 flex flex-col items-center justify-start lg:mt-0 lg:w-1/3">
                    <div class="collapse-arrow collapse mb-4 w-full max-w-sm border border-base-200 bg-base-100 shadow-xl">
                        <input type="checkbox" />
                        <div class="collapse-title text-lg font-bold">Thêm thông tin</div>
                        <div class="collapse-content">
                            <div class="mb-4">
                                <label class="label pt-0 pl-0"><span class="label-text font-medium">Khách hàng thành viên</span></label>
                                <div class="join w-full">
                                    <input
                                        v-model="customerPhone"
                                        type="text"
                                        placeholder="Nhập SĐT khách..."
                                        class="input-bordered input input-sm join-item w-full focus:border-primary focus:outline-none"
                                        :disabled="isAttachingCustomer"
                                        @keyup.enter="attachCustomer"
                                    />
                                    <button
                                        class="btn join-item text-white btn-sm btn-primary"
                                        @click="attachCustomer"
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
                                        v-model="discountCode"
                                        type="text"
                                        placeholder="Nhập mã..."
                                        class="input-bordered input input-sm join-item w-full focus:border-primary focus:outline-none"
                                        :disabled="isApplyingDiscount"
                                        @keyup.enter="applyDiscount"
                                    />
                                    <button
                                        class="btn join-item text-white btn-sm btn-primary"
                                        @click="applyDiscount"
                                        :disabled="!discountCode || isApplyingDiscount"
                                    >
                                        <span v-if="isApplyingDiscount" class="loading loading-xs loading-spinner"></span>
                                        <span v-else>Áp dụng</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card w-full max-w-sm border border-base-200 bg-base-100 shadow-xl">
                        <div class="card-body items-center text-center">
                            <h2 class="card-title">Quét mã thanh toán</h2>
                            <p class="mb-4 text-sm text-base-content/70">Mã dành cho nhân viên quét bằng máy POS để thanh toán và in đơn</p>

                            <div class="rounded-xl border border-base-300 bg-white p-4 shadow-inner">
                                <div class="flex h-48 w-48 items-center justify-center rounded-lg bg-base-200">
                                    <QrCodeIcon class="size-24 text-base-content/20" />
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
                                        v-model="selectedPaymentMethod"
                                    >
                                        <option disabled selected value="">Chọn phương thức</option>
                                        <option v-for="method in paymentMethods" :key="method.value" :value="method.value">
                                            {{ method.label }}
                                        </option>
                                    </select>
                                    <button
                                        class="btn text-white btn-primary"
                                        :disabled="!selectedPaymentMethod || isProcessing"
                                        @click="confirmPayment"
                                    >
                                        Xác nhận
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { AppPageProps } from '@/types';
import { ChevronLeftIcon, QrCodeIcon, XMarkIcon } from '@heroicons/vue/24/outline';
import { Link, router, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';

const page = usePage<AppPageProps>();

interface BillItem {
    name: string;
    quantity: number;
    price: number;
    total: number;
    cookingMethod?: string;
    note?: string;
}

interface PaymentMethod {
    value: string;
    label: string;
}

interface Props {
    table: {
        id: string;
        table_number: number;
        bill?: {
            id: number;
            time_in: string;
            user?: {
                name: string;
            };
            customer?: {
                name: string;
                phone: string;
                id: number;
            };
            customer_id?: number | null;
        };
    };
    billDetails: BillItem[];
    totalAmount: number;
    paymentMethods: PaymentMethod[];
    discountPercent: number;
    discountAmount: number;
}

const props = defineProps<Props>();

const showError = ref(false);
let errorTimeout: ReturnType<typeof setTimeout>;

watch(
    () => page.props.flash.error,
    (val) => {
        if (val) {
            showError.value = true;
            clearTimeout(errorTimeout);
            errorTimeout = setTimeout(() => {
                showError.value = false;
            }, 5000);
        }
    },
    { immediate: true },
);

const discountCode = ref('');
const isApplyingDiscount = ref(false);
const discountAmount = ref(props.discountAmount || 0);
const discountPercent = ref(props.discountPercent || 0);

const customerPhone = ref('');
const isAttachingCustomer = ref(false);

function attachCustomer() {
    if (!customerPhone.value) return;

    const form = useForm({
        phone: customerPhone.value,
    });

    isAttachingCustomer.value = true;
    form.post(route('staff.table.attach-customer', props.table.id), {
        preserveScroll: true,
        onFinish: () => {
            isAttachingCustomer.value = false;
        },
        onSuccess: () => {
            customerPhone.value = '';
        },
    });
}

watch(
    () => props.discountAmount,
    (newVal) => {
        discountAmount.value = newVal || 0;
    },
);

watch(
    () => props.discountPercent,
    (newVal) => {
        discountPercent.value = newVal || 0;
    },
);

const finalTotal = computed(() => {
    return props.totalAmount - discountAmount.value;
});

function applyDiscount() {
    if (!discountCode.value) return;

    const form = useForm({
        code: discountCode.value,
    });

    isApplyingDiscount.value = true;
    form.post(route('staff.table.apply-discount', props.table.id), {
        preserveScroll: true,
        onFinish: () => {
            isApplyingDiscount.value = false;
        },
        onSuccess: () => {
            const payload = page.props.flash.payload as any;
            if (payload) {
                discountAmount.value = Number(payload.discount_amount) || 0;
                discountPercent.value = Number(payload.discount_percent) || 0;
                discountCode.value = '';
            }
        },
    });
}

const isRemovingDiscount = ref(false);

function removeDiscount() {
    if (!confirm('Bạn có chắc muốn hủy mã giảm giá này?')) return;

    isRemovingDiscount.value = true;
    router.post(
        route('staff.table.remove-discount', props.table.id),
        {},
        {
            preserveScroll: true,
            onFinish: () => {
                isRemovingDiscount.value = false;
            },
            onSuccess: () => {
                discountAmount.value = 0;
                discountPercent.value = 0;
                discountCode.value = '';
            },
        },
    );
}

const selectedPaymentMethod = ref('');
const isProcessing = ref(false);

function confirmPayment() {
    if (!selectedPaymentMethod.value) return;

    const form = useForm({
        table_id: props.table.id,
        payment_method: selectedPaymentMethod.value,
    });

    isProcessing.value = true;
    form.post(route('staff.table.pay', props.table.id), {
        onFinish: () => {
            isProcessing.value = false;
        },
        onSuccess: () => {
            //
        },
    });
}

function formatPrice(price: number): string {
    return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(price);
}

function formatDate(dateString?: string): string {
    if (!dateString) return '---';
    return new Date(dateString).toLocaleString('vi-VN', {
        hour: '2-digit',
        minute: '2-digit',
        day: '2-digit',
        month: '2-digit',
    });
}
</script>
