<template>
    <Head title="Login" />
    <div class="flex min-h-screen items-center justify-center bg-base-200 px-4 py-12 sm:px-6 lg:px-8">
        <div class="card w-full max-w-sm shrink-0 bg-base-100 shadow-2xl rounded-xl">
            <form class="card-body" @submit.prevent="submit">
                <h2 class="text-center text-lg leading-9 font-bold tracking-tight">Thương ốc</h2>
                <h2 class="mb-4 text-center text-xl leading-9 font-bold tracking-tight">Đăng nhập vào tài khoản</h2>

                <div class="form-control">
                    <label class="label" for="username">
                        <span class="label-text">Tên đăng nhập</span>
                    </label>
                    <input
                        id="username"
                        v-model="form.username"
                        type="text"
                        autocomplete="username"
                        required
                        class="input w-full border outline-primary focus:border-primary mt-1"
                        :class="{ 'input-error': form.errors.username }"
                    />
                    <label class="label" v-if="form.errors.username">
                        <span class="label-text-alt text-error">{{ form.errors.username }}</span>
                    </label>
                </div>

                <div class="form-control">
                    <label class="label" for="password">
                        <span class="label-text">Mật khẩu</span>
                    </label>
                    <input
                        id="password"
                        v-model="form.password"
                        type="password"
                        autocomplete="current-password"
                        required
                        class="input w-full border outline-primary focus:border-primary mt-1"
                        :class="{ 'input-error': form.errors.password }"
                    />
                    <label class="label" v-if="form.errors.password">
                        <span class="label-text-alt text-error">{{ form.errors.password }}</span>
                    </label>
                </div>

                <div class="form-control">
                    <label class="label cursor-pointer justify-start gap-3">
                        <input id="remember-me" v-model="form.remember" type="checkbox" class="checkbox checkbox-primary" />
                        <span class="label-text">Ghi nhớ đăng nhập</span>
                    </label>
                </div>

                <div class="form-control mt-6">
                    <button type="submit" :disabled="form.processing" class="btn w-full btn-primary">
                        <span v-if="form.processing" class="loading loading-spinner"></span>
                        Đăng nhập
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';

const form = useForm({
    username: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post('/login');
};
</script>
