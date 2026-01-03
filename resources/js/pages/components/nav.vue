<template>
    <div class="relative flex w-full items-center justify-between bg-primary p-4">
        <div class="flex items-center gap-4">
            <button v-if="props.use_back_button" class="btn btn-circle btn-sm" @click="goBack">
                <ArrowLeftIcon class="size-5" />
            </button>
            <p class="text-lg font-bold text-white">{{ page.props.name_vi }}</p>
        </div>
        <p class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 text-xl font-bold text-white">{{ props.center_text }}</p>
        <div class="dropdown dropdown-end">
            <div tabindex="0" role="button" class="btn m-1 rounded-4xl">{{ user.username }}</div>
            <ul tabindex="-1" class="dropdown-content menu z-1 w-52 rounded-box bg-base-100 p-2 shadow-sm">
                <li><a @click="logout">Đăng xuất</a></li>
            </ul>
        </div>
    </div>
</template>
<script setup lang="ts">
import { AppPageProps } from '@/types';
import { ArrowLeftIcon } from '@heroicons/vue/24/outline';
import { usePage, router } from '@inertiajs/vue3';
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
