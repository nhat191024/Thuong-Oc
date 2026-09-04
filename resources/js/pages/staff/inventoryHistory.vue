<template>
    <div class="flex h-screen w-full flex-col bg-base-200">
        <Nav center_text="Lịch sử biến động kho" :use_back_button="true" :back-url="route('staff.inventory.index')" />
        <main class="flex min-h-0 flex-1 flex-col gap-4 p-4">
            <div class="grid gap-3 rounded-lg bg-base-100 p-4 shadow-sm md:grid-cols-[minmax(0,1fr)_auto] md:items-center">
                <div>
                    <h1 class="text-xl font-bold">Biến động kho {{ branchName }}</h1>
                    <p class="text-sm opacity-60">Tồn trước và sau mỗi lần nhập, sử dụng, hoàn hoặc điều chỉnh.</p>
                </div>
                <div class="grid w-full grid-cols-1 gap-2 sm:grid-cols-[minmax(14rem,1fr)_auto_auto] md:w-auto md:justify-self-end">
                    <label class="input w-full"
                        ><MagnifyingGlassIcon class="size-5 opacity-50" /><input v-model="search" class="min-w-0" placeholder="Tìm loại ốc"
                    /></label>
                    <select v-model="type" class="select w-full sm:min-w-44">
                        <option value="all">Mọi biến động</option>
                        <option value="restock">Nhập kho</option>
                        <option value="usage">Sử dụng</option>
                        <option value="return">Hoàn kho</option>
                        <option value="adjustment">Điều chỉnh</option>
                    </select>
                    <a :href="exportUrl" class="btn w-full self-center whitespace-nowrap btn-sm btn-success sm:w-auto">
                        <ArrowDownTrayIcon class="size-5" />
                        Xuất Excel
                    </a>
                </div>
            </div>
            <div class="min-h-0 flex-1 overflow-auto rounded-lg bg-base-100 shadow-sm">
                <table class="table-pin-rows table">
                    <thead>
                        <tr>
                            <th>Thời gian</th>
                            <th>Loại ốc</th>
                            <th class="min-w-28 whitespace-nowrap">Loại</th>
                            <th class="text-right">Thay đổi</th>
                            <th class="text-right">Tồn trước</th>
                            <th class="text-right">Tồn sau</th>
                            <th class="text-right">Đơn giá</th>
                            <th class="text-right">Thành tiền</th>
                            <th>Người thực hiện / Ghi chú</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="item in filtered" :key="item.id">
                            <td class="whitespace-nowrap">{{ formatDate(item.occurred_at) }}</td>
                            <td class="font-semibold">{{ item.food_name }}</td>
                            <td class="min-w-28 whitespace-nowrap">
                                <span class="badge whitespace-nowrap" :class="meta(item.type).className">{{ meta(item.type).label }}</span>
                            </td>
                            <td class="text-right font-mono font-bold" :class="delta(item) < 0 ? 'text-error' : 'text-success'">
                                {{ signedQuantity(delta(item), item.unit_symbol) }}
                            </td>
                            <td class="text-right font-mono">{{ quantity(item.balance_before, item.unit_symbol) }}</td>
                            <td class="text-right font-mono font-bold" :class="item.balance_after < 0 ? 'text-error' : ''">
                                {{ quantity(item.balance_after, item.unit_symbol) }}
                            </td>
                            <td class="text-right whitespace-nowrap">{{ item.unit_price === null ? '—' : currency(item.unit_price) }}</td>
                            <td class="text-right font-bold whitespace-nowrap">{{ item.total_cost === null ? '—' : currency(item.total_cost) }}</td>
                            <td>
                                <div>{{ item.created_by || (item.bill_detail_id ? `Đơn món #${item.bill_detail_id}` : 'Hệ thống') }}</div>
                                <div v-if="item.note" class="max-w-sm text-xs opacity-60">{{ item.note }}</div>
                            </td>
                        </tr>
                        <tr v-if="filtered.length === 0">
                            <td colspan="9" class="py-12 text-center opacity-60">Chưa có biến động kho phù hợp.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div v-if="movements.links.length > 3" class="flex justify-center gap-1">
                <Link
                    v-for="link in movements.links"
                    :key="link.label"
                    :href="link.url || ''"
                    class="btn btn-sm"
                    :class="{ 'btn-primary': link.active, 'btn-disabled': !link.url }"
                    preserve-scroll
                    ><span v-html="link.label"></span
                ></Link>
            </div>
        </main>
    </div>
</template>
<script setup lang="ts">
import { ArrowDownTrayIcon, MagnifyingGlassIcon } from '@heroicons/vue/24/outline';
import { Link } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import Nav from '../components/nav.vue';
type MovementType = 'restock' | 'usage' | 'return' | 'adjustment';
interface Movement {
    id: number;
    food_name: string;
    type: MovementType;
    unit_name: string | null;
    unit_symbol: string | null;
    quantity: number;
    balance_before: number;
    balance_after: number;
    unit_price: number | null;
    total_cost: number | null;
    bill_detail_id: number | null;
    created_by: string | null;
    note: string | null;
    occurred_at: string | null;
}
interface PageLink {
    url: string | null;
    label: string;
    active: boolean;
}
const props = defineProps<{ branchName: string; movements: { data: Movement[]; links: PageLink[] } }>();
const search = ref('');
const type = ref<'all' | MovementType>('all');
const exportUrl = computed(() =>
    route('staff.inventory.movements.export', {
        search: search.value.trim() || undefined,
        type: type.value === 'all' ? undefined : type.value,
    }),
);
const filtered = computed(() =>
    props.movements.data.filter(
        (item) =>
            (!search.value.trim() || item.food_name.toLowerCase().includes(search.value.trim().toLowerCase())) &&
            (type.value === 'all' || item.type === type.value),
    ),
);
const delta = (item: Movement) => item.balance_after - item.balance_before;
const quantity = (value: number, symbol: string | null) =>
    `${new Intl.NumberFormat('vi-VN', { maximumFractionDigits: 3 }).format(value)}${symbol ? ` ${symbol}` : ''}`;
const signedQuantity = (value: number, symbol: string | null) => `${value > 0 ? '+' : ''}${quantity(value, symbol)}`;
const currency = (value: number) => new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(value);
const formatDate = (value: string | null) =>
    value ? new Intl.DateTimeFormat('vi-VN', { dateStyle: 'short', timeStyle: 'short' }).format(new Date(value)) : '—';
const meta = (value: MovementType) =>
    ({
        restock: { label: 'Nhập kho', className: 'badge-success' },
        usage: { label: 'Sử dụng', className: 'badge-error' },
        return: { label: 'Hoàn kho', className: 'badge-info' },
        adjustment: { label: 'Điều chỉnh', className: 'badge-warning' },
    })[value];
</script>
