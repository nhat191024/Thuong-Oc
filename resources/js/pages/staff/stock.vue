<template>
    <div class="flex h-screen w-full flex-col bg-base-200">
        <Nav center_text="Quản lý tồn kho" :use_back_button="true" :back-url="route('staff.tables')" />

        <main class="flex min-h-0 flex-1 flex-col gap-4 p-4">
            <div class="flex flex-col gap-3 rounded-lg bg-base-100 p-4 shadow-sm md:flex-row md:items-center md:justify-between">
                <div>
                    <h1 class="text-xl font-bold text-base-content">Tồn kho {{ props.branchName }}</h1>
                    <p class="text-sm text-base-content/60">{{ filteredFoods.length }} món đang hiển thị</p>
                </div>

                <div class="flex flex-col gap-2 sm:flex-row">
                    <label class="input w-full sm:w-72">
                        <MagnifyingGlassIcon class="size-5 opacity-50" />
                        <input v-model="searchTerm" type="search" placeholder="Tìm món hoặc danh mục" />
                    </label>

                    <select v-model="statusFilter" class="select w-full sm:w-44">
                        <option value="all">Tất cả</option>
                        <option value="out">Hết món</option>
                        <option value="available">Còn món</option>
                    </select>
                </div>
            </div>

            <div v-if="page.props.flash.success && showSuccess" role="alert" class="alert alert-success">
                <CheckCircleIcon class="size-5" />
                <span>{{ page.props.flash.success }}</span>
            </div>

            <div v-if="page.props.flash.error && showError" role="alert" class="alert alert-error">
                <XCircleIcon class="size-5" />
                <span>{{ page.props.flash.error }}</span>
            </div>

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
                        <tr v-for="food in filteredFoods" :key="food.id">
                            <td class="text-base-content/70">{{ food.category_name }}</td>
                            <td class="font-semibold">{{ food.name }}</td>
                            <td class="text-right">{{ formatPrice(food.price) }}</td>
                            <td>
                                <span class="badge" :class="food.is_out_of_stock ? 'text-white badge-error' : 'text-white badge-success'">
                                    {{ food.is_out_of_stock ? 'Hết món' : 'Còn món' }}
                                </span>
                            </td>
                            <td class="text-right">
                                <button
                                    class="btn btn-sm"
                                    :class="food.is_out_of_stock ? 'btn-success' : 'btn-error'"
                                    :disabled="processingFoodId === food.id"
                                    @click="toggleStock(food)"
                                >
                                    <span v-if="processingFoodId === food.id" class="loading loading-xs loading-spinner"></span>
                                    {{ food.is_out_of_stock ? 'Đánh dấu còn món' : 'Đánh dấu hết món' }}
                                </button>
                            </td>
                        </tr>
                        <tr v-if="filteredFoods.length === 0">
                            <td colspan="5" class="py-10 text-center text-base-content/60">Không tìm thấy món phù hợp.</td>
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
import { router, usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import Nav from '../components/nav.vue';

interface StaffFoodStock {
    id: number;
    name: string;
    price: number;
    category_name: string;
    is_out_of_stock: boolean;
}

interface Props {
    branchName: string;
    foods: StaffFoodStock[];
}

const props = defineProps<Props>();
const page = usePage<AppPageProps>();

const searchTerm = ref('');
const statusFilter = ref<'all' | 'out' | 'available'>('all');
const processingFoodId = ref<number | null>(null);
const showSuccess = ref(Boolean(page.props.flash.success));
const showError = ref(Boolean(page.props.flash.error));

const filteredFoods = computed(() => {
    const query = searchTerm.value.trim().toLowerCase();

    return props.foods.filter((food) => {
        const matchesSearch = query.length === 0 || food.name.toLowerCase().includes(query) || food.category_name.toLowerCase().includes(query);
        const matchesStatus =
            statusFilter.value === 'all' ||
            (statusFilter.value === 'out' && food.is_out_of_stock) ||
            (statusFilter.value === 'available' && !food.is_out_of_stock);

        return matchesSearch && matchesStatus;
    });
});

function formatPrice(price: number): string {
    return new Intl.NumberFormat('vi-VN', {
        style: 'currency',
        currency: 'VND',
    }).format(price);
}

function toggleStock(food: StaffFoodStock): void {
    processingFoodId.value = food.id;

    router.patch(
        route('staff.stock.update', food.id),
        {
            is_out_of_stock: !food.is_out_of_stock,
        },
        {
            preserveScroll: true,
            onFinish: () => {
                processingFoodId.value = null;
            },
        },
    );
}

let successTimeout: ReturnType<typeof setTimeout>;
let errorTimeout: ReturnType<typeof setTimeout>;

watch(
    () => page.props.flash.success,
    (message) => {
        if (message) {
            showSuccess.value = true;
            clearTimeout(successTimeout);
            successTimeout = setTimeout(() => {
                showSuccess.value = false;
            }, 3000);
        }
    },
    { immediate: true },
);

watch(
    () => page.props.flash.error,
    (message) => {
        if (message) {
            showError.value = true;
            clearTimeout(errorTimeout);
            errorTimeout = setTimeout(() => {
                showError.value = false;
            }, 3000);
        }
    },
    { immediate: true },
);
</script>
