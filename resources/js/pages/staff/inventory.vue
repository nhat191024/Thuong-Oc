<template>
    <div class="flex h-screen w-full flex-col bg-base-200">
        <Nav center_text="Kho ốc" :use_back_button="true" :back-url="route('staff.tables')">
            <template #actions>
                <Link :href="route('staff.inventory.movements.index')" class="btn btn-sm">
                    <ClockIcon class="size-4" />
                    <span class="hidden sm:inline">Lịch sử kho</span>
                </Link>
            </template>
        </Nav>

        <main class="flex min-h-0 flex-1 flex-col gap-4 p-4">
            <div class="flex flex-col gap-3 rounded-lg bg-base-100 p-4 shadow-sm lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <h1 class="text-xl font-bold">Tồn kho {{ branchName }}</h1>
                    <p class="text-sm text-base-content/60">Tồn kg có thể âm và không tự thay đổi trạng thái bán.</p>
                    <p class="mt-2 text-xs font-medium text-base-content/70">Màu tồn kho được tính theo ngưỡng riêng đã cài cho từng loại ốc.</p>
                </div>
                <div class="flex flex-col gap-2 sm:flex-row">
                    <select v-model="levelFilter" class="select w-full sm:w-44" aria-label="Lọc theo mức tồn kho">
                        <option value="all">Tất cả mức</option>
                        <option value="red">Đỏ — Thiếu hụt</option>
                        <option value="yellow">Vàng — Sắp hết</option>
                        <option value="green">Xanh — Ổn định</option>
                    </select>
                    <label class="input w-full sm:w-72">
                        <MagnifyingGlassIcon class="size-5 opacity-50" />
                        <input v-model="searchTerm" type="search" placeholder="Tìm món hoặc danh mục" />
                    </label>
                </div>
            </div>

            <div v-if="page.props.flash.success" class="alert alert-success"><CheckCircleIcon class="size-5" />{{ page.props.flash.success }}</div>
            <div v-if="page.props.flash.error" class="alert alert-error"><XCircleIcon class="size-5" />{{ page.props.flash.error }}</div>

            <div class="min-h-0 flex-1 overflow-auto rounded-lg bg-base-100 shadow-sm">
                <table class="table-pin-rows table">
                    <thead>
                        <tr>
                            <th>Món ăn</th>
                            <th class="text-right">Tồn hiện tại</th>
                            <th class="text-right">Dùng hôm nay</th>
                            <th>Nhập gần nhất</th>
                            <th class="text-right">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="food in filteredFoods" :key="food.id" class="border-l-4" :class="inventoryRowClass(food)">
                            <td>
                                <div class="font-semibold">{{ food.name }}</div>
                                <div class="text-xs opacity-60">{{ food.category_name }} · {{ formatPrice(food.price) }}</div>
                            </td>
                            <td class="text-right">
                                <span class="badge font-mono font-bold" :class="inventoryBadgeClass(food)">{{ formatKg(food.quantity_in_kg) }}</span>
                                <div v-if="!food.is_inventory_tracked" class="text-xs text-warning">Chưa theo dõi</div>
                                <div v-else class="mt-1 text-xs opacity-60">
                                    Đỏ &lt; {{ formatKg(food.red_threshold_kg) }} · Vàng &lt; {{ formatKg(food.yellow_threshold_kg) }}
                                </div>
                            </td>
                            <td class="text-right font-mono">{{ formatKg(food.used_today_in_kg) }}</td>
                            <td>
                                <template v-if="food.last_restock_quantity_in_kg !== null"
                                    ><div>+{{ formatKg(food.last_restock_quantity_in_kg) }}</div>
                                    <div class="text-xs opacity-60">{{ formatDate(food.last_restocked_at) }}</div></template
                                ><span v-else class="opacity-50">Chưa có</span>
                            </td>
                            <td>
                                <div class="flex justify-end gap-2">
                                    <button class="btn btn-sm btn-primary" @click="openModal(food, 'restock')">
                                        <PlusIcon class="size-4" />Nhập thêm
                                    </button>
                                    <button class="btn btn-sm" @click="openModal(food, 'adjustment')">
                                        <AdjustmentsHorizontalIcon class="size-4" />Điều chỉnh
                                    </button>
                                    <button class="btn btn-sm" @click="openThresholdModal(food)"><SignalIcon class="size-4" />Cài ngưỡng</button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="filteredFoods.length === 0">
                            <td colspan="5" class="py-10 text-center opacity-60">Không tìm thấy loại ốc phù hợp.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </main>

        <div v-if="selectedFood" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" @click.self="closeModal">
            <form class="w-full max-w-md rounded-xl bg-base-100 p-6 shadow-xl" @submit.prevent="submitMovement">
                <div class="flex justify-between gap-4">
                    <div>
                        <h2 class="text-lg font-bold">{{ movementType === 'restock' ? 'Nhập thêm kho' : 'Điều chỉnh tồn thực tế' }}</h2>
                        <p class="text-sm opacity-60">{{ selectedFood.name }} · đang còn {{ formatKg(selectedFood.quantity_in_kg) }}</p>
                    </div>
                    <button type="button" class="btn btn-circle btn-ghost btn-sm" @click="closeModal"><XMarkIcon class="size-5" /></button>
                </div>
                <div class="mt-5 flex flex-col gap-4">
                    <label
                        ><span class="mb-2 block font-medium">{{ movementType === 'restock' ? 'Số kg nhập thêm' : 'Tồn thực tế mới (kg)' }}</span
                        ><label class="input w-full"
                            ><input
                                v-model.number="movementQuantity"
                                class="grow"
                                type="number"
                                step="0.001"
                                :min="movementType === 'restock' ? 0.001 : undefined"
                                required
                            /><span>kg</span></label
                        ><span v-if="errors.quantity_in_kg" class="mt-1 block text-sm text-error">{{ errors.quantity_in_kg }}</span
                        ><span v-if="movementType === 'adjustment'" class="mt-1 block text-xs opacity-60"
                            >Được phép nhập số âm; giá trị này thay thế tồn hiện tại.</span
                        ></label
                    >
                    <label
                        ><span class="mb-2 block font-medium">Ghi chú</span
                        ><textarea v-model="movementNote" class="textarea w-full" rows="3" placeholder="Không bắt buộc"></textarea
                        ><span v-if="errors.note" class="text-sm text-error">{{ errors.note }}</span></label
                    >
                </div>
                <div class="mt-6 flex justify-end gap-2">
                    <button type="button" class="btn" :disabled="submitting" @click="closeModal">Hủy</button
                    ><button class="btn btn-primary" :disabled="submitting">
                        <span v-if="submitting" class="loading loading-sm loading-spinner"></span
                        >{{ movementType === 'restock' ? 'Xác nhận nhập' : 'Lưu điều chỉnh' }}
                    </button>
                </div>
            </form>
        </div>

        <div
            v-if="selectedThresholdFood"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
            @click.self="closeThresholdModal"
        >
            <form class="w-full max-w-md rounded-xl bg-base-100 p-6 shadow-xl" @submit.prevent="submitThresholds">
                <div class="flex justify-between gap-4">
                    <div>
                        <h2 class="text-lg font-bold">Cài ngưỡng tồn kho</h2>
                        <p class="text-sm opacity-60">{{ selectedThresholdFood.name }}</p>
                    </div>
                    <button type="button" class="btn btn-circle btn-ghost btn-sm" @click="closeThresholdModal">
                        <XMarkIcon class="size-5" />
                    </button>
                </div>
                <div class="mt-5 flex flex-col gap-4">
                    <label>
                        <span class="mb-2 block font-medium">Ngưỡng đỏ (kg)</span>
                        <label class="input w-full">
                            <input v-model.number="redThresholdKg" class="grow" type="number" min="0" step="0.001" required />
                            <span>kg</span>
                        </label>
                        <span v-if="thresholdErrors.red_threshold_kg" class="mt-1 block text-sm text-error">
                            {{ thresholdErrors.red_threshold_kg }}
                        </span>
                    </label>
                    <label>
                        <span class="mb-2 block font-medium">Ngưỡng vàng (kg)</span>
                        <label class="input w-full">
                            <input v-model.number="yellowThresholdKg" class="grow" type="number" min="0.001" step="0.001" required />
                            <span>kg</span>
                        </label>
                        <span v-if="thresholdErrors.yellow_threshold_kg" class="mt-1 block text-sm text-error">
                            {{ thresholdErrors.yellow_threshold_kg }}
                        </span>
                    </label>
                    <div class="rounded-lg bg-base-200 p-3 text-sm">
                        <div class="flex items-center gap-2">
                            <span class="size-2.5 rounded-full bg-error"></span>Đỏ: dưới {{ formatKg(redThresholdKg) }}
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="size-2.5 rounded-full bg-warning"></span>Vàng: từ {{ formatKg(redThresholdKg) }} đến dưới
                            {{ formatKg(yellowThresholdKg) }}
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="size-2.5 rounded-full bg-success"></span>Xanh: từ {{ formatKg(yellowThresholdKg) }}
                        </div>
                    </div>
                </div>
                <div class="mt-6 flex justify-end gap-2">
                    <button type="button" class="btn" :disabled="thresholdSubmitting" @click="closeThresholdModal">Hủy</button>
                    <button class="btn btn-primary" :disabled="thresholdSubmitting">
                        <span v-if="thresholdSubmitting" class="loading loading-sm loading-spinner"></span>Lưu ngưỡng
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup lang="ts">
import { AppPageProps } from '@/types';
import {
    AdjustmentsHorizontalIcon,
    CheckCircleIcon,
    ClockIcon,
    MagnifyingGlassIcon,
    PlusIcon,
    SignalIcon,
    XCircleIcon,
    XMarkIcon,
} from '@heroicons/vue/24/outline';
import { Link, router, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import Nav from '../components/nav.vue';

interface FoodStock {
    id: number;
    name: string;
    price: number;
    category_name: string;
    is_inventory_tracked: boolean;
    quantity_in_kg: number;
    red_threshold_kg: number;
    yellow_threshold_kg: number;
    used_today_in_kg: number;
    last_restock_quantity_in_kg: number | null;
    last_restocked_at: string | null;
}
type InventoryLevel = 'red' | 'yellow' | 'green' | 'untracked';
type InventoryLevelFilter = 'all' | Exclude<InventoryLevel, 'untracked'>;

const props = defineProps<{ branchName: string; foods: FoodStock[] }>();
const page = usePage<AppPageProps>();
const searchTerm = ref('');
const levelFilter = ref<InventoryLevelFilter>('all');
const selectedFood = ref<FoodStock | null>(null);
const movementType = ref<'restock' | 'adjustment'>('restock');
const movementQuantity = ref<number | null>(null);
const movementNote = ref('');
const errors = ref<Record<string, string>>({});
const submitting = ref(false);
const selectedThresholdFood = ref<FoodStock | null>(null);
const redThresholdKg = ref(10);
const yellowThresholdKg = ref(20);
const thresholdErrors = ref<Record<string, string>>({});
const thresholdSubmitting = ref(false);
const inventoryLevelRank: Record<InventoryLevel, number> = {
    red: 0,
    yellow: 1,
    green: 2,
    untracked: 3,
};
const filteredFoods = computed(() =>
    props.foods
        .filter((food) => {
            const level = inventoryLevel(food);
            if (levelFilter.value !== 'all' && level !== levelFilter.value) return false;

            const query = searchTerm.value.trim().toLowerCase();
            return !query || food.name.toLowerCase().includes(query) || food.category_name.toLowerCase().includes(query);
        })
        .sort((firstFood, secondFood) => {
            const levelDifference = inventoryLevelRank[inventoryLevel(firstFood)] - inventoryLevelRank[inventoryLevel(secondFood)];
            return levelDifference || firstFood.id - secondFood.id;
        }),
);
function inventoryLevel(food: FoodStock): InventoryLevel {
    if (!food.is_inventory_tracked) return 'untracked';
    if (food.quantity_in_kg < food.red_threshold_kg) return 'red';
    if (food.quantity_in_kg < food.yellow_threshold_kg) return 'yellow';
    return 'green';
}
const formatPrice = (value: number) => new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(value);
const formatKg = (value: number) => `${new Intl.NumberFormat('vi-VN', { maximumFractionDigits: 3 }).format(value)} kg`;
const formatDate = (value: string | null) =>
    value ? new Intl.DateTimeFormat('vi-VN', { dateStyle: 'short', timeStyle: 'short' }).format(new Date(value)) : '—';
function inventoryRowClass(food: FoodStock): string {
    return {
        untracked: 'border-base-300',
        red: 'border-error bg-error/5',
        yellow: 'border-warning bg-warning/5',
        green: 'border-success bg-success/5',
    }[inventoryLevel(food)];
}
function inventoryBadgeClass(food: FoodStock): string {
    return {
        untracked: 'border-base-300 bg-base-200 text-base-content/60',
        red: 'border-error/30 bg-error/15 text-error',
        yellow: 'border-warning/30 bg-warning/15 text-warning',
        green: 'border-success/30 bg-success/15 text-success',
    }[inventoryLevel(food)];
}
function openModal(food: FoodStock, type: 'restock' | 'adjustment') {
    selectedFood.value = food;
    movementType.value = type;
    movementQuantity.value = type === 'adjustment' ? food.quantity_in_kg : null;
    movementNote.value = '';
    errors.value = {};
}
function closeModal() {
    if (!submitting.value) selectedFood.value = null;
}
function submitMovement() {
    if (!selectedFood.value || movementQuantity.value === null) return;
    submitting.value = true;
    router.post(
        route('staff.inventory.movements.store', selectedFood.value.id),
        { type: movementType.value, quantity_in_kg: movementQuantity.value, note: movementNote.value || null },
        {
            preserveScroll: true,
            onSuccess: () => (selectedFood.value = null),
            onError: (value) => (errors.value = value),
            onFinish: () => (submitting.value = false),
        },
    );
}
function openThresholdModal(food: FoodStock) {
    selectedThresholdFood.value = food;
    redThresholdKg.value = food.red_threshold_kg;
    yellowThresholdKg.value = food.yellow_threshold_kg;
    thresholdErrors.value = {};
}
function closeThresholdModal() {
    if (!thresholdSubmitting.value) selectedThresholdFood.value = null;
}
function submitThresholds() {
    if (!selectedThresholdFood.value) return;
    thresholdSubmitting.value = true;
    router.patch(
        route('staff.inventory.thresholds.update', selectedThresholdFood.value.id),
        { red_threshold_kg: redThresholdKg.value, yellow_threshold_kg: yellowThresholdKg.value },
        {
            preserveScroll: true,
            onSuccess: () => (selectedThresholdFood.value = null),
            onError: (value) => (thresholdErrors.value = value),
            onFinish: () => (thresholdSubmitting.value = false),
        },
    );
}
</script>
