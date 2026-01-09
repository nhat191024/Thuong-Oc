<template>
    <Head title="Đăng ký" />
    <div class="flex min-h-screen items-center justify-center bg-base-200 px-4 py-12 sm:px-6 lg:px-8">
        <div class="card w-full max-w-sm shrink-0 bg-base-100 shadow-2xl rounded-xl">
            <form class="card-body" @submit.prevent="submit">
                <h2 class="text-center text-lg leading-9 font-bold tracking-tight">Thương ốc</h2>
                <h2 class="mb-4 text-center text-xl leading-9 font-bold tracking-tight">Đăng ký thành viên</h2>

                <div class="form-control">
                    <label class="label" for="name">
                        <span class="label-text">Họ và tên</span>
                    </label>
                    <input
                        id="name"
                        v-model="form.name"
                        type="text"
                        required
                        class="input w-full border outline-primary focus:border-primary mt-1"
                        :class="{ 'input-error': form.errors.name }"
                    />
                    <label class="label" v-if="form.errors.name">
                        <span class="label-text-alt text-error">{{ form.errors.name }}</span>
                    </label>
                </div>

                <div class="form-control">
                    <label class="label" for="phone">
                        <span class="label-text">Số điện thoại</span>
                    </label>
                    <input
                        id="phone"
                        v-model="form.phone"
                        type="tel"
                        required
                        class="input w-full border outline-primary focus:border-primary mt-1"
                        :class="{ 'input-error': form.errors.phone }"
                    />
                    <label class="label" v-if="form.errors.phone">
                        <span class="label-text-alt text-error">{{ form.errors.phone }}</span>
                    </label>
                </div>

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
                        autocomplete="new-password"
                        required
                        class="input w-full border outline-primary focus:border-primary mt-1"
                        :class="{ 'input-error': form.errors.password }"
                    />
                    <label class="label" v-if="form.errors.password">
                        <span class="label-text-alt text-error">{{ form.errors.password }}</span>
                    </label>
                </div>

                <div class="form-control">
                    <label class="label" for="password_confirmation">
                        <span class="label-text">Xác nhận mật khẩu</span>
                    </label>
                    <input
                        id="password_confirmation"
                        v-model="form.password_confirmation"
                        type="password"
                        autocomplete="new-password"
                        required
                        class="input w-full border outline-primary focus:border-primary mt-1"
                        :class="{ 'input-error': form.errors.password_confirmation }"
                    />
                </div>

                <div class="form-control mt-6">
                    <button type="submit" :disabled="form.processing" class="btn w-full btn-primary">
                        <span v-if="form.processing" class="loading loading-spinner"></span>
                        Đăng ký
                    </button>
                </div>

                <div class="mt-4 text-center">
                    <span class="text-sm">Đã có tài khoản? </span>
                    <Link href="/login" class="link link-primary no-underline font-semibold">Đăng nhập ngay</Link>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup lang="ts">
import { Head, useForm, Link } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    phone: '',
    username: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post('/register', {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>
