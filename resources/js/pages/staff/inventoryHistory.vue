<template>
    <div class="flex h-screen w-full flex-col bg-base-200">
        <Nav center_text="Lịch sử biến động kho" :use_back_button="true" :back-url="route('staff.inventory.index')" />
        <main class="flex min-h-0 flex-1 flex-col gap-4 p-4">
            <div class="flex flex-col gap-3 rounded-lg bg-base-100 p-4 shadow-sm md:flex-row md:items-center md:justify-between">
                <div>
                    <h1 class="text-xl font-bold">Biến động kho {{ branchName }}</h1>
                    <p class="text-sm opacity-60">Tồn trước và sau mỗi lần nhập, sử dụng, hoàn hoặc điều chỉnh.</p>
                </div>
                <div class="flex gap-2">
                    <label class="input"><MagnifyingGlassIcon class="size-5 opacity-50" /><input v-model="search" placeholder="Tìm loại ốc" /></label
                    ><select v-model="type" class="select">
                        <option value="all">Mọi biến động</option>
                        <option value="restock">Nhập kho</option>
                        <option value="usage">Sử dụng</option>
                        <option value="return">Hoàn kho</option>
                        <option value="adjustment">Điều chỉnh</option>
                    </select>
                </div>
            </div>
            <div class="min-h-0 flex-1 overflow-auto rounded-lg bg-base-100 shadow-sm">
                <table class="table-pin-rows table">
                    <thead>
                        <tr>
                            <th>Thời gian</th>
                            <th>Loại ốc</th>
                            <th>Loại</th>
                            <th class="text-right">Thay đổi</th>
                            <th class="text-right">Tồn trước</th>
                            <th class="text-right">Tồn sau</th>
                            <th>Người thực hiện / Ghi chú</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="item in filtered" :key="item.id">
                            <td class="whitespace-nowrap">{{ formatDate(item.occurred_at) }}</td>
                            <td class="font-semibold">{{ item.food_name }}</td>
                            <td>
                                <span class="badge" :class="meta(item.type).className">{{ meta(item.type).label }}</span>
                            </td>
                            <td class="text-right font-mono font-bold" :class="delta(item) < 0 ? 'text-error' : 'text-success'">
                                {{ signedKg(delta(item)) }}
                            </td>
                            <td class="text-right font-mono">{{ kg(item.balance_before_kg) }}</td>
                            <td class="text-right font-mono font-bold" :class="item.balance_after_kg < 0 ? 'text-error' : ''">
                                {{ kg(item.balance_after_kg) }}
                            </td>
                            <td>
                                <div>{{ item.created_by || (item.bill_detail_id ? `Đơn món #${item.bill_detail_id}` : 'Hệ thống') }}</div>
                                <div v-if="item.note" class="max-w-sm text-xs opacity-60">{{ item.note }}</div>
                            </td>
                        </tr>
                        <tr v-if="filtered.length === 0">
                            <td colspan="7" class="py-12 text-center opacity-60">Chưa có biến động kho phù hợp.</td>
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
import { MagnifyingGlassIcon } from '@heroicons/vue/24/outline';
import { Link } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import Nav from '../components/nav.vue';
type MovementType = 'restock' | 'usage' | 'return' | 'adjustment';
interface Movement {
    id: number;
    food_name: string;
    type: MovementType;
    quantity_in_kg: number;
    balance_before_kg: number;
    balance_after_kg: number;
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
const filtered = computed(() =>
    props.movements.data.filter(
        (item) =>
            (!search.value.trim() || item.food_name.toLowerCase().includes(search.value.trim().toLowerCase())) &&
            (type.value === 'all' || item.type === type.value),
    ),
);
const delta = (item: Movement) => item.balance_after_kg - item.balance_before_kg;
const kg = (value: number) => `${new Intl.NumberFormat('vi-VN', { maximumFractionDigits: 3 }).format(value)} kg`;
const signedKg = (value: number) => `${value > 0 ? '+' : ''}${kg(value)}`;
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
