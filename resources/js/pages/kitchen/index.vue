<template>
    <div class="flex h-screen flex-col">
        <Nav :center_text="'Chi nhánh ' + props.branchName" />

        <div class="flex-1 overflow-hidden bg-gray-100">
            <div class="h-full overflow-y-auto p-4">
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
                    <Link
                        class="flex cursor-pointer flex-row items-center gap-4 rounded-xl border-2 border-gray-200 bg-white p-6 text-gray-400 shadow-sm transition-all duration-200 hover:border-primary hover:text-primary hover:shadow-md"
                        :href="route('kitchen.print-station')"
                    >
                        <PrinterIcon class="h-12 w-12" />
                        <span class="text-xl font-bold">Trạm in</span>
                    </Link>

                    <article
                        v-for="kitchen in props.kitchens"
                        :key="kitchen.id"
                        class="flex flex-col gap-4 rounded-xl border-2 border-gray-200 bg-white p-5 text-gray-500 shadow-sm transition-all duration-200 hover:border-primary hover:shadow-md"
                    >
                        <Link :href="route('kitchen.show', { kitchen: kitchen.id })" class="flex min-w-0 items-center gap-4 hover:text-primary">
                            <FireIcon class="h-12 w-12 shrink-0" />
                            <span class="truncate text-xl font-bold">{{ kitchen.name }}</span>
                        </Link>

                        <div class="grid gap-3 border-t border-gray-100 pt-3">
                            <label class="flex items-center justify-between gap-3">
                                <span class="text-sm font-semibold text-gray-600">Tự động in</span>
                                <input
                                    v-model="kitchen.auto_print"
                                    type="checkbox"
                                    class="toggle toggle-primary"
                                    :disabled="savingKitchenIds.has(kitchen.id)"
                                    @change="saveKitchenPrintSettings(kitchen)"
                                />
                            </label>

                            <label class="grid gap-1">
                                <span class="text-sm font-semibold text-gray-600">Máy in</span>
                                <select
                                    v-model="kitchen.printer_id"
                                    class="select select-bordered h-10 min-h-10 w-full"
                                    :disabled="savingKitchenIds.has(kitchen.id)"
                                    @change="saveKitchenPrintSettings(kitchen)"
                                >
                                    <option :value="null">Chưa chọn</option>
                                    <option v-for="printer in props.printers" :key="printer.id" :value="printer.id">
                                        {{ printer.name }}
                                    </option>
                                </select>
                            </label>
                        </div>
                    </article>
                </div>
            </div>
        </div>
    </div>
</template>
<script setup lang="ts">
import { FireIcon, PrinterIcon } from '@heroicons/vue/24/outline';
import { Link } from '@inertiajs/vue3';
import axios from 'axios';
import { ref } from 'vue';
import { toast } from 'vue3-toastify';
import Nav from '../components/nav.vue';

interface Kitchen {
    id: number;
    name: string;
    branch_id: number;
    printer_id: number | null;
    auto_print: boolean;
}

interface Printer {
    id: number;
    name: string;
}

interface Props {
    kitchens: Kitchen[];
    branchName: string;
    printers: Printer[];
}

const props = defineProps<Props>();
const savingKitchenIds = ref(new Set<number>());

const saveKitchenPrintSettings = async (kitchen: Kitchen) => {
    if (savingKitchenIds.value.has(kitchen.id)) {
        return;
    }

    savingKitchenIds.value.add(kitchen.id);

    try {
        await axios.patch(route('kitchen.print-settings.update', { kitchen: kitchen.id }), {
            auto_print: kitchen.auto_print,
            printer_id: kitchen.printer_id,
        });

        toast.success('Đã lưu cài đặt in.');
    } catch (error) {
        toast.error(getErrorMessage(error));
    } finally {
        savingKitchenIds.value.delete(kitchen.id);
    }
};

const getErrorMessage = (error: unknown) => {
    if (axios.isAxiosError(error)) {
        const responseMessage = error.response?.data?.message;

        if (typeof responseMessage === 'string' && responseMessage.length > 0) {
            return responseMessage;
        }
    }

    return 'Không thể lưu cài đặt in.';
};
</script>
