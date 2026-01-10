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
            <div v-if="page.props.flash.success && showSuccess" role="alert" class="mb-4 alert w-full alert-success">
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
                    <BillTableDetails
                        :table="table"
                        :bill-details="billDetails"
                        :discount-amount="discountAmount"
                        :discount-percent="discountPercent"
                        :final-total="finalTotal"
                    />
                </div>

                <!-- Right Column -->
                <div class="mt-4 flex flex-col items-center justify-start lg:mt-0 lg:w-1/3">
                    <BillAdditionalInfo
                        :table="table"
                        v-model:customer-phone="customerPhone"
                        v-model:discount-code="discountCode"
                        :is-attaching-customer="isAttachingCustomer"
                        :is-applying-discount="isApplyingDiscount"
                        :discount-amount="discountAmount"
                        :is-removing-customer="isRemovingCustomer"
                        :is-removing-discount="isRemovingDiscount"
                        @attach-customer="attachCustomer"
                        @apply-discount="applyDiscount"
                        @remove-customer="removeCustomer"
                        @remove-discount="removeDiscount"
                    />

                    <BillPayment
                        :table="table"
                        :payment-methods="paymentMethods"
                        v-model:selected-payment-method="selectedPaymentMethod"
                        :is-processing="isProcessing"
                        @confirm-payment="confirmPayment"
                    />
                </div>
            </div>
        </div>

        <CreateCustomerDialog
            :visible="shouldCreateCustomer"
            :customer-phone="customerPhone"
            v-model:customer-name="customerName"
            :is-attaching-customer="isAttachingCustomer"
            @confirm="attachCustomer"
            @cancel="cancelCreateCustomer"
        />

        <ConfirmModal
            :is-open="showConfirmModal"
            :title="confirmTitle"
            :message="confirmMessage"
            @update:is-open="showConfirmModal = $event"
            @confirm="onConfirm"
        />
    </div>
</template>

<script setup lang="ts">
import ConfirmModal from '@/pages/components/ConfirmModal.vue';
import { AppPageProps } from '@/types';
import { ChevronLeftIcon } from '@heroicons/vue/24/outline';
import { Link, router, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import BillAdditionalInfo from './partials/BillAdditionalInfo.vue';
import BillPayment from './partials/BillPayment.vue';
import BillTableDetails from './partials/BillTableDetails.vue';
import CreateCustomerDialog from './partials/CreateCustomerDialog.vue';

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

const showSuccess = ref(false);
let successTimeout: ReturnType<typeof setTimeout>;

watch(
    () => page.props.flash.success,
    (val) => {
        if (val) {
            showSuccess.value = true;
            clearTimeout(successTimeout);
            successTimeout = setTimeout(() => {
                showSuccess.value = false;
            }, 5000);
        }
    },
    { immediate: true },
);

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
const customerName = ref('');
const shouldCreateCustomer = ref(false);
const isAttachingCustomer = ref(false);

watch(
    () => page.props.flash.payload,
    (payload: any) => {
        if (payload && payload.action === 'customer_not_found') {
            shouldCreateCustomer.value = true;
            if (payload.phone) {
                customerPhone.value = payload.phone;
            }
        } else {
            shouldCreateCustomer.value = false;
            customerName.value = '';
        }
    },
    { immediate: true },
);

function cancelCreateCustomer() {
    shouldCreateCustomer.value = false;
    customerPhone.value = '';
    customerName.value = '';
}

function attachCustomer() {
    if (!customerPhone.value) return;

    const data: any = {
        phone: customerPhone.value,
    };

    if (shouldCreateCustomer.value && customerName.value) {
        data.name = customerName.value;
    }

    const form = useForm(data);

    isAttachingCustomer.value = true;
    form.post(route('staff.table.attach-customer', props.table.id), {
        preserveScroll: true,
        onFinish: () => {
            isAttachingCustomer.value = false;
        },
        onSuccess: () => {
            if (!page.props.flash.error) {
                customerPhone.value = '';
                customerName.value = '';
                shouldCreateCustomer.value = false;
            }
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

// Confirm Modal Logic
const showConfirmModal = ref(false);
const confirmTitle = ref('');
const confirmMessage = ref('');
const confirmAction = ref<(() => void) | null>(null);

function openConfirmModal(title: string, message: string, action: () => void) {
    confirmTitle.value = title;
    confirmMessage.value = message;
    confirmAction.value = action;
    showConfirmModal.value = true;
}

function onConfirm() {
    if (confirmAction.value) {
        confirmAction.value();
    }
    showConfirmModal.value = false;
}

const isRemovingDiscount = ref(false);

function removeDiscount() {
    openConfirmModal('Xác nhận xóa mã giảm giá', 'Bạn có chắc muốn hủy mã giảm giá này?', () => {
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
    });
}

const isRemovingCustomer = ref(false);

function removeCustomer() {
    openConfirmModal('Xác nhận xóa khách hàng', 'Bạn có chắc muốn xóa khách hàng khỏi đơn này?', () => {
        isRemovingCustomer.value = true;
        router.post(
            route('staff.table.remove-customer', props.table.id),
            {},
            {
                preserveScroll: true,
                onFinish: () => {
                    isRemovingCustomer.value = false;
                },
                onSuccess: () => {
                    customerPhone.value = '';
                },
            },
        );
    });
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
</script>
