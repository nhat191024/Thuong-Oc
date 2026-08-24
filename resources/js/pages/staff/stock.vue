<template>
    <div class="flex h-screen w-full flex-col bg-base-200">
        <Nav center_text="Quản lý hết món" :use_back_button="true" :back-url="route('staff.tables')">
            <template #actions>
                <Link :href="route('staff.inventory.index')" class="btn btn-sm">Kho ốc</Link>
            </template>
        </Nav>

        <main class="flex min-h-0 flex-1 flex-col gap-4 p-4">
            <div class="flex flex-col gap-3 rounded-lg bg-base-100 p-4 shadow-sm md:flex-row md:items-center md:justify-between">
                <div>
                    <h1 class="text-xl font-bold">Trạng thái món {{ branchName }}</h1>
                    <p class="text-sm opacity-60">Chỉ quản lý món đang còn bán hay đã hết.</p>
                </div>
                <div class="flex flex-col gap-2 sm:flex-row">
                    <label class="input w-full sm:w-72"
                        ><MagnifyingGlassIcon class="size-5 opacity-50" /><input v-model="search" type="search" placeholder="Tìm món hoặc danh mục"
                    /></label>
                    <select v-model="status" class="select w-full sm:w-44">
                        <option value="all">Tất cả</option>
                        <option value="out">Hết món</option>
                        <option value="available">Còn món</option>
                    </select>
                </div>
            </div>
            <div v-if="page.props.flash.success" class="alert alert-success"><CheckCircleIcon class="size-5" />{{ page.props.flash.success }}</div>
            <div v-if="page.props.flash.error" class="alert alert-error"><XCircleIcon class="size-5" />{{ page.props.flash.error }}</div>
            <div class="min-h-0 flex-1 overflow-auto rounded-lg bg-base-100 shadow-sm">
                <table class="table-pin-rows table">
                    <thead>
                        <tr>
                            <th>Danh mục</th>
                            <th>Món ăn</th>
                            <th class="text-right">Giá</th>
                            <th>Trạng thái</th>
                            <th class="text-right">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="food in filtered" :key="food.id">
                            <td class="opacity-70">{{ food.category_name }}</td>
                            <td class="font-semibold">{{ food.name }}</td>
                            <td class="text-right">{{ price(food.price) }}</td>
                            <td>
                                <span class="badge text-white" :class="food.is_out_of_stock ? 'badge-error' : 'badge-success'">{{
                                    food.is_out_of_stock ? 'Hết món' : 'Còn món'
                                }}</span>
                            </td>
                            <td class="text-right">
                                <button
                                    class="btn btn-sm"
                                    :class="food.is_out_of_stock ? 'btn-success' : 'btn-error'"
                                    :disabled="processing === food.id"
                                    @click="toggle(food)"
                                >
                                    <span v-if="processing === food.id" class="loading loading-xs loading-spinner"></span
                                    >{{ food.is_out_of_stock ? 'Đánh dấu còn món' : 'Đánh dấu hết món' }}
                                </button>
                            </td>
                        </tr>
                        <tr v-if="filtered.length === 0">
                            <td colspan="5" class="py-10 text-center opacity-60">Không tìm thấy món phù hợp.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</template>
<script setup lang="ts">
import { AppPageProps } from '@/types';
import { CheckCircleIcon, MagnifyingGlassIcon, XCircleIcon } from '@heroicons/vue/24/outline';
import { Link, router, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import Nav from '../components/nav.vue';
interface FoodStatus {
    id: number;
    name: string;
    price: number;
    category_name: string;
    is_out_of_stock: boolean;
}
const props = defineProps<{ branchName: string; foods: FoodStatus[] }>();
const page = usePage<AppPageProps>();
const search = ref('');
const status = ref<'all' | 'out' | 'available'>('all');
const processing = ref<number | null>(null);
const filtered = computed(() =>
    props.foods.filter((food) => {
        const query = search.value.trim().toLowerCase();
        return (
            (!query || food.name.toLowerCase().includes(query) || food.category_name.toLowerCase().includes(query)) &&
            (status.value === 'all' || (status.value === 'out' ? food.is_out_of_stock : !food.is_out_of_stock))
        );
    }),
);
const price = (value: number) => new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(value);
function toggle(food: FoodStatus) {
    processing.value = food.id;
    router.patch(
        route('staff.stock.update', food.id),
        { is_out_of_stock: !food.is_out_of_stock },
        { preserveScroll: true, onFinish: () => (processing.value = null) },
    );
}
</script>
