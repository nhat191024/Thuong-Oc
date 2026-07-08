<template>
    <div class="flex h-screen flex-col bg-gray-100">
        <Nav :center_text="`Trạm in - ${props.branchName}`" use_back_button :back-url="route('kitchen.index')">
            <template #actions>
                <label class="flex min-w-0 items-center gap-2 rounded-full bg-white px-3 py-1.5 text-primary shadow-sm">
                    <input v-model="autoPrintEnabled" type="checkbox" class="toggle toggle-primary toggle-sm" @change="saveAutoPrintPreference" />
                    <span class="text-sm font-semibold whitespace-nowrap">Tự động in</span>
                </label>

                <div
                    v-if="props.printers.length > 0"
                    class="flex min-w-0 items-center gap-2 rounded-full bg-white px-3 py-1.5 text-primary shadow-sm"
                >
                    <PrinterIcon class="size-5 shrink-0" />
                    <span class="hidden text-sm font-semibold sm:inline">Máy in</span>
                    <select
                        v-model="selectedPrinterId"
                        class="select h-8 min-h-8 w-36 border-0 bg-transparent px-1 text-sm font-bold text-gray-900 focus:outline-none sm:w-44"
                        @change="savePrinterSelection"
                    >
                        <option v-for="printer in props.printers" :key="printer.id" :value="printer.id" class="text-gray-800">
                            {{ printer.name }}
                        </option>
                    </select>
                </div>
            </template>
        </Nav>

        <main class="flex min-h-0 flex-1 flex-col gap-4 p-4">
            <div class="grid gap-3 md:grid-cols-4">
                <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                    <p class="text-sm text-gray-500">Đang chờ in</p>
                    <p class="text-3xl font-bold text-primary">{{ queuedOrders.length }}</p>
                </div>
                <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                    <p class="text-sm text-gray-500">Máy in</p>
                    <p class="truncate text-xl font-bold">{{ selectedPrinterName }}</p>
                </div>
                <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                    <p class="text-sm text-gray-500">Chế độ</p>
                    <p class="text-xl font-bold" :class="autoPrintEnabled ? 'text-primary' : 'text-gray-700'">
                        {{ autoPrintEnabled ? 'Tự động in' : 'Chờ bấm nút' }}
                    </p>
                </div>
                <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                    <p class="text-sm text-gray-500">Kết nối</p>
                    <p class="text-xl font-bold" :class="isSubscribed ? 'text-green-600' : 'text-red-600'">
                        {{ isSubscribed ? 'Đang lắng nghe' : 'Chưa kết nối' }}
                    </p>
                </div>
            </div>

            <section class="min-h-0 flex-1 overflow-y-auto">
                <div v-if="queuedOrders.length > 0" class="grid grid-cols-1 gap-3 md:grid-cols-2 xl:grid-cols-3">
                    <article
                        v-for="order in queuedOrders"
                        :key="order.id"
                        class="flex min-h-64 flex-col rounded-lg border border-gray-200 bg-white p-4 shadow-sm"
                    >
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="text-sm text-gray-500">Bàn {{ order.bill.table.table_number }}</p>
                                <h2 class="truncate text-2xl font-bold" :title="order.bill.table.name">{{ order.bill.table.name }}</h2>
                            </div>
                            <span class="rounded-full bg-primary/10 px-3 py-1 text-sm font-bold text-primary">{{
                                formatTime(order.updated_at ?? order.created_at)
                            }}</span>
                        </div>

                        <div class="mt-4 min-h-0 flex-1">
                            <p class="text-2xl font-bold text-primary">
                                {{ order.quantity }}x {{ order.dish ? order.dish.food.name : order.custom_dish_name }}
                            </p>
                            <p v-if="order.dish?.cooking_method" class="text-lg font-semibold text-gray-700">{{ order.dish.cooking_method.name }}</p>
                            <div v-if="order.note" class="mt-3 rounded-md border border-red-200 bg-red-50 px-3 py-2">
                                <p class="text-xs font-semibold text-red-400 uppercase">Ghi chú</p>
                                <p class="text-sm whitespace-pre-wrap text-red-600 italic">{{ order.note }}</p>
                            </div>
                        </div>

                        <div class="mt-4 flex items-center gap-2">
                            <button
                                class="btn flex-1 btn-primary"
                                :disabled="!selectedPrinterId || printingOrderIds.has(order.id)"
                                @click="printOrder(order)"
                            >
                                <PrinterIcon class="size-5" />
                                {{ printingOrderIds.has(order.id) ? 'Đang in...' : 'In món' }}
                            </button>
                            <button
                                class="btn btn-square btn-ghost"
                                :disabled="printingOrderIds.has(order.id)"
                                @click="dismissOrder(order.id)"
                                title="Bỏ qua"
                            >
                                <XMarkIcon class="size-5" />
                            </button>
                        </div>
                    </article>
                </div>

                <div v-else class="flex h-full min-h-96 items-center justify-center rounded-lg border border-dashed border-gray-300 bg-white">
                    <div class="text-center">
                        <PrinterIcon class="mx-auto size-14 text-gray-300" />
                        <p class="mt-3 text-lg font-semibold text-gray-600">Đang chờ tín hiệu in từ bếp</p>
                        <p class="text-sm text-gray-400">Khi bếp hoàn thành món, phiếu in sẽ xuất hiện tại đây.</p>
                    </div>
                </div>
            </section>
        </main>
    </div>
</template>

<script setup lang="ts">
import Nav from '@/pages/components/nav.vue';
import { PrinterIcon, XMarkIcon } from '@heroicons/vue/24/outline';
import { format } from 'date-fns';
import { computed, onMounted, onUnmounted, ref } from 'vue';
import { toast } from 'vue3-toastify';

interface Food {
    id: number;
    name: string;
}

interface CookingMethod {
    id: number;
    name: string;
}

interface Dish {
    id: number;
    food: Food;
    cooking_method?: CookingMethod | null;
}

interface Table {
    id: number;
    table_number: string;
    name: string;
}

interface Bill {
    id: number;
    table: Table;
}

interface BillDetail {
    id: number;
    quantity: number;
    note: string | null;
    status: string;
    created_at: string;
    updated_at?: string;
    dish: Dish | null;
    custom_dish_name: string | null;
    bill: Bill;
}

interface Printer {
    id: number;
    name: string;
}

interface Props {
    branchId: number;
    branchName: string;
    printers: Printer[];
}

const props = defineProps<Props>();

const localStorageKey = `print_station_printer_${props.branchId}`;
const autoPrintStorageKey = `print_station_auto_print_${props.branchId}`;
const savedPrinterId = typeof window !== 'undefined' ? localStorage.getItem(localStorageKey) : null;
const savedAutoPrint = typeof window !== 'undefined' ? localStorage.getItem(autoPrintStorageKey) : null;
const fallbackPrinterId = props.printers[0]?.id ?? null;
const selectedPrinterId = ref<number | null>(savedPrinterId ? Number(savedPrinterId) : fallbackPrinterId);
const autoPrintEnabled = ref(savedAutoPrint === null ? true : savedAutoPrint === 'true');
const queuedOrders = ref<BillDetail[]>([]);
const autoPrintQueue = ref<BillDetail[]>([]);
const printingOrderIds = ref(new Set<number>());
const isAutoPrinting = ref(false);
const isSubscribed = ref(false);

const selectedPrinterName = computed(() => {
    return props.printers.find((printer) => printer.id === selectedPrinterId.value)?.name ?? 'Chưa chọn';
});

const savePrinterSelection = () => {
    if (selectedPrinterId.value) {
        localStorage.setItem(localStorageKey, String(selectedPrinterId.value));
    }

    if (autoPrintEnabled.value) {
        for (const order of queuedOrders.value) {
            enqueueAutoPrint(order);
        }
    }
};

const saveAutoPrintPreference = () => {
    localStorage.setItem(autoPrintStorageKey, String(autoPrintEnabled.value));

    if (autoPrintEnabled.value) {
        for (const order of queuedOrders.value) {
            enqueueAutoPrint(order);
        }
    }
};

const enqueueOrder = (order: BillDetail) => {
    if (queuedOrders.value.some((queuedOrder) => queuedOrder.id === order.id)) {
        return;
    }

    queuedOrders.value = [order, ...queuedOrders.value];
    toast.info(`Có phiếu in mới: bàn ${order.bill.table.table_number}`);

    if (autoPrintEnabled.value) {
        enqueueAutoPrint(order);
    }
};

const dismissOrder = (orderId: number) => {
    queuedOrders.value = queuedOrders.value.filter((order) => order.id !== orderId);
    autoPrintQueue.value = autoPrintQueue.value.filter((order) => order.id !== orderId);
};

const printOrder = async (order: BillDetail) => {
    if (printingOrderIds.value.has(order.id)) {
        return;
    }

    if (!selectedPrinterId.value) {
        toast.error('Vui lòng chọn máy in.');
        return;
    }

    printingOrderIds.value.add(order.id);

    try {
        await printThermalReceipt(order);
        dismissOrder(order.id);
        toast.success('Đã mở lệnh in trên máy trạm.');
    } catch {
        toast.error('Không thể mở lệnh in trên trình duyệt.');
    } finally {
        printingOrderIds.value.delete(order.id);
    }
};

const enqueueAutoPrint = (order: BillDetail) => {
    if (!selectedPrinterId.value) {
        toast.error('Vui lòng chọn máy in để tự động in.');
        return;
    }

    if (autoPrintQueue.value.some((queuedOrder) => queuedOrder.id === order.id) || printingOrderIds.value.has(order.id)) {
        return;
    }

    autoPrintQueue.value.push(order);
    void processAutoPrintQueue();
};

const processAutoPrintQueue = async () => {
    if (isAutoPrinting.value) {
        return;
    }

    isAutoPrinting.value = true;

    try {
        while (autoPrintEnabled.value && autoPrintQueue.value.length > 0) {
            const order = autoPrintQueue.value.shift();

            if (!order || !queuedOrders.value.some((queuedOrder) => queuedOrder.id === order.id)) {
                continue;
            }

            await printOrder(order);
            await wait(800);
        }
    } finally {
        isAutoPrinting.value = false;
    }
};

const wait = (milliseconds: number) => {
    return new Promise((resolve) => window.setTimeout(resolve, milliseconds));
};

const formatTime = (dateString: string) => {
    return format(new Date(dateString), 'HH:mm');
};

const printThermalReceipt = (order: BillDetail) => {
    return new Promise<void>((resolve, reject) => {
        const printFrame = document.createElement('iframe');
        printFrame.style.position = 'fixed';
        printFrame.style.right = '0';
        printFrame.style.bottom = '0';
        printFrame.style.width = '0';
        printFrame.style.height = '0';
        printFrame.style.border = '0';
        printFrame.title = 'kitchen-print-receipt';
        printFrame.srcdoc = buildReceiptHtml(order);

        printFrame.onload = () => {
            try {
                printFrame.contentWindow?.addEventListener('afterprint', () => printFrame.remove(), { once: true });
                printFrame.contentWindow?.focus();
                printFrame.contentWindow?.print();
                window.setTimeout(() => printFrame.remove(), 60000);
                resolve();
            } catch {
                printFrame.remove();
                reject();
            }
        };

        document.body.appendChild(printFrame);
    });
};

const buildReceiptHtml = (order: BillDetail) => {
    const dishName = order.dish ? order.dish.food.name : (order.custom_dish_name ?? 'Món ăn');
    const cookingMethod = order.dish?.cooking_method?.name;
    const printedAt = format(new Date(), 'HH:mm dd/MM/yyyy');

    return `<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <title>In món ${escapeHtml(String(order.id))}</title>
    <style>
        @page {
            size: 80mm auto;
            margin: 0;
        }

        * {
            box-sizing: border-box;
        }

        body {
            width: 80mm;
            margin: 0;
            padding: 4mm;
            color: #000;
            background: #fff;
            font-family: Arial, "Helvetica Neue", sans-serif;
            font-size: 13px;
            line-height: 1.35;
        }

        .center {
            text-align: center;
        }

        .title {
            font-size: 20px;
            font-weight: 800;
            letter-spacing: 0;
            text-transform: uppercase;
        }

        .table {
            margin-top: 4mm;
            font-size: 28px;
            font-weight: 900;
        }

        .line {
            margin: 3mm 0;
            border-top: 1px dashed #000;
        }

        .dish {
            font-size: 22px;
            font-weight: 900;
        }

        .method {
            margin-top: 1mm;
            font-size: 18px;
            font-weight: 800;
        }

        .quantity {
            margin-top: 3mm;
            font-size: 22px;
            font-weight: 900;
        }

        .note-label {
            margin-top: 3mm;
            font-size: 12px;
            font-weight: 800;
            text-transform: uppercase;
        }

        .note {
            margin-top: 1mm;
            font-size: 17px;
            font-weight: 800;
            white-space: pre-wrap;
        }

        .meta {
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="center title">Bếp phục vụ</div>
    <div class="line"></div>
    <div class="table">Bàn ${escapeHtml(order.bill.table.table_number)}</div>
    <div>${escapeHtml(order.bill.table.name)}</div>
    <div class="line"></div>
    <div class="dish">${escapeHtml(dishName)}</div>
    ${cookingMethod ? `<div class="method">${escapeHtml(cookingMethod)}</div>` : ''}
    <div class="quantity">SL: ${escapeHtml(String(order.quantity))}</div>
    ${
        order.note
            ? `<div class="line"></div>
    <div class="note-label">Ghi chú</div>
    <div class="note">${escapeHtml(order.note)}</div>`
            : ''
    }
    <div class="line"></div>
    <div class="meta">Hoàn thành: ${escapeHtml(formatTime(order.updated_at ?? order.created_at))}</div>
    <div class="meta">In lúc: ${escapeHtml(printedAt)}</div>
</body>
</html>`;
};

const escapeHtml = (value: string) => {
    return value.replaceAll('&', '&amp;').replaceAll('<', '&lt;').replaceAll('>', '&gt;').replaceAll('"', '&quot;').replaceAll("'", '&#039;');
};

onMounted(() => {
    if (selectedPrinterId.value) {
        savePrinterSelection();
    }

    if (window.Echo) {
        window.Echo.private(`kitchens.${props.branchId}`).listen('.dish.print.requested', (event: { billDetail: BillDetail }) => {
            isSubscribed.value = true;
            enqueueOrder(event.billDetail);
        });

        isSubscribed.value = true;
    }
});

onUnmounted(() => {
    if (window.Echo) {
        window.Echo.leave(`kitchens.${props.branchId}`);
    }
});
</script>
