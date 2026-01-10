<template>
    <Head title="Chào mừng đến với Thương Ốc" />

    <div class="relative flex min-h-screen flex-col items-center justify-center overflow-hidden bg-orange-50 p-4">
        <div class="animate-blob absolute -top-10 -left-10 filter h-40 w-40 rounded-full bg-orange-200 opacity-70 mix-blend-multiply blur-xl"></div>
        <div
            class="animate-blob animation-delay-2000 absolute -right-10 -bottom-10 filter h-40 w-40 rounded-full bg-yellow-200 opacity-70 mix-blend-multiply blur-xl"
        ></div>

        <div class="relative z-10 w-full max-w-md">
            <div class="mb-10 text-center">
                <div class="mb-4 inline-block rounded-full bg-white p-1 shadow-lg">
                    <img class="h-40 w-40 rounded-full max-[400px]:h-24 max-[400px]:w-24" :src="settings.app_logo ?? ''" alt="Logo" />
                </div>
                <h1 class="mb-2 font-serif text-4xl font-extrabold tracking-tight text-gray-800">Thương Ốc</h1>
                <p class="font-medium text-orange-600 italic">Hương vị biển, thắm tình quê</p>
            </div>

            <div class="rounded-3xl border border-white/20 bg-white/80 p-8 text-center shadow-xl backdrop-blur-sm">
                <h2 class="mb-2 text-2xl font-bold text-gray-800">Bạn đang đói bụng?</h2>
                <p class="mb-8 text-gray-500">Hãy đặt món ngay để thưởng thức những món ốc tươi ngon nhất!</p>

                <div class="space-y-4">
                    <div
                        class="group rounded-2xl border-2 border-dashed border-orange-200 bg-orange-50 p-6 transition-colors hover:border-orange-400"
                    >
                        <div class="flex flex-col items-center gap-3">
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke-width="1.5"
                                stroke="currentColor"
                                class="h-10 w-10 text-orange-500 transition-transform group-hover:scale-110"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 013.75 9.375v-4.5zM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 01-1.125-1.125v-4.5zM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0113.5 9.375v-4.5z"
                                />
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M6.75 6.75h.75v.75h-.75v-.75zM6.75 16.5h.75v.75h-.75v-.75zM16.5 6.75h.75v.75h-.75v-.75zM13.5 13.5h.75v.75h-.75v-.75zM13.5 19.5h.75v.75h-.75v-.75zM19.5 13.5h.75v.75h-.75v-.75zM19.5 19.5h.75v.75h-.75v-.75zM16.5 16.5h.75v.75h-.75v-.75z"
                                />
                            </svg>
                            <span class="font-bold text-gray-700">Quét mã QR trên bàn</span>
                        </div>
                    </div>

                    <p class="text-sm text-gray-500">Sử dụng camera điện thoại hoặc Zalo để quét mã QR được dán trên bàn của bạn.</p>
                </div>
            </div>

            <div class="mt-12 text-center">
                <div class="mb-2 flex justify-center gap-4 text-orange-800 opacity-60">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-6 w-6">
                        <path
                            fill-rule="evenodd"
                            d="M1.5 4.5a3 3 0 013-3h1.372c.86 0 1.61.586 1.819 1.42l1.105 4.423a1.875 1.875 0 01-.694 1.955l-1.293.97c-.135.101-.164.249-.126.352a11.285 11.285 0 006.697 6.697c.103.038.25.009.352-.126l.97-1.293a1.875 1.875 0 011.955-.694l4.423 1.105c.834.209 1.42.959 1.42 1.82V19.5a3 3 0 01-3 3h-2.25C8.552 22.5 1.5 15.448 1.5 4.5z"
                            clip-rule="evenodd"
                        />
                    </svg>
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-6 w-6">
                        <path
                            fill-rule="evenodd"
                            d="M12 2.25c-5.385 0-9.75 4.365-9.75 9.75s4.365 9.75 9.75 9.75 9.75-4.365 9.75-9.75S17.385 2.25 12 2.25zM12.75 6a.75.75 0 00-1.5 0v6c0 .414.336.75.75.75h4.5a.75.75 0 000-1.5h-3.75V6z"
                            clip-rule="evenodd"
                        />
                    </svg>
                </div>
                <p class="text-sm text-orange-900/60">Mở cửa: 16:00 - 23:00</p>
                <p class="text-sm text-orange-900/60">Wifi: ThuongOc / MatKhau: 88888888</p>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { AppSettings } from '@/types';
import { Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const settings = computed(() => page.props.app_settings as AppSettings);
</script>

<style scoped>
@keyframes blob {
    0% {
        transform: translate(0px, 0px) scale(1);
    }
    33% {
        transform: translate(30px, -50px) scale(1.1);
    }
    66% {
        transform: translate(-20px, 20px) scale(0.9);
    }
    100% {
        transform: translate(0px, 0px) scale(1);
    }
}
.animate-blob {
    animation: blob 7s infinite;
}
.animation-delay-2000 {
    animation-delay: 2s;
}
</style>
