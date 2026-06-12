<template>
    <div class="grid w-full grid-cols-[minmax(0,1fr)_auto_minmax(0,1fr)] items-center bg-primary p-4">
        <div class="flex min-w-0 items-center gap-4">
            <button v-if="props.use_back_button" class="btn btn-circle btn-sm" @click="goBack">
                <ArrowLeftIcon class="size-5" />
            </button>
            <p class="truncate text-lg font-bold text-white">{{ page.props.name_vi }}</p>
        </div>
        <p class="max-w-[40vw] truncate text-xl font-bold text-white">{{ props.center_text }}</p>
        <div class="flex min-w-0 items-center justify-end gap-2">
            <slot name="actions"></slot>
            <div class="dropdown dropdown-end">
                <div tabindex="0" role="button" class="btn m-1 rounded-4xl">{{ user.username }}</div>
                <ul tabindex="-1" class="dropdown-content menu z-1 w-52 rounded-box bg-base-100 p-2 shadow-sm">
                    <li><a @click="logout">Đăng xuất</a></li>
                </ul>
            </div>
        </div>
    </div>
</template>
<script setup lang="ts">
import { AppPageProps } from '@/types';
import { ArrowLeftIcon } from '@heroicons/vue/24/outline';
import { router, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

interface Props {
    center_text: string;
    use_back_button?: boolean;
    backUrl?: string;
}

const props = defineProps<Props>();

const page = usePage<AppPageProps>();
const user = computed(() => page.props.auth.user);

const goBack = () => {
    if (props.backUrl) {
        router.visit(props.backUrl);
    } else {
        window.history.back();
    }
};

const logout = () => {
    router.post('/logout');
};
</script>
