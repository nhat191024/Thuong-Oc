<template>
    <nav class="flex items-center justify-between bg-primary px-4 py-1">
        <img class="h-14 w-14 rounded-full max-[400px]:h-12 max-[400px]:w-12" :src="settings.app_logo ?? ''" alt="Logo" />
        <div class="flex items-center gap-3">
            <p class="text-2xl font-semibold text-white">Bàn {{ props.tableNumber }}</p>

            <div v-if="user" class="dropdown dropdown-end">
                <div tabindex="0" role="button" class="btn avatar btn-circle btn-ghost">
                    <div class="w-10 rounded-full border-2 border-white">
                        <img v-if="user.avatar" :src="user.avatar" alt="Avatar" />
                    </div>
                </div>
                <ul tabindex="0" class="dropdown-content menu z-50 mt-3 w-52 menu-sm rounded-box bg-base-100 p-2 shadow">
                    <li>
                        <a class="font-bold">Chào mừng: {{ user.name || user.username }}</a>
                    </li>
                    <li>
                        <a class="font-bold">Điểm: {{ user.points }}</a>
                    </li>
                    <li><Link href="/logout" method="post" as="button">Đăng xuất</Link></li>
                </ul>
            </div>

            <div v-else class="dropdown dropdown-end">
                <div tabindex="0" role="button" class="placeholder btn avatar btn-circle btn-ghost">
                    <div class="bg-neutral-focus flex w-10 items-center justify-center rounded-full border-2 border-white text-neutral-content">
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke-width="1.5"
                            stroke="currentColor"
                            class="h-6 w-6"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"
                            />
                        </svg>
                    </div>
                </div>
                <ul tabindex="0" class="dropdown-content menu z-50 mt-3 w-52 menu-sm rounded-box bg-base-100 p-2 text-base-content shadow">
                    <li><Link href="/login">Đăng nhập</Link></li>
                    <li><Link href="/register">Đăng ký</Link></li>
                </ul>
            </div>
        </div>
    </nav>
</template>

<script setup lang="ts">
import { AppSettings, Auth } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const settings = computed(() => page.props.app_settings as AppSettings);
const user = computed(() => (page.props.auth as Auth).user);

interface Props {
    tableNumber: number;
}

const props = defineProps<Props>();
</script>
