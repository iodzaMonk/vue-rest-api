<script setup lang="ts">
import RecaptchaV2 from '@/components/RecaptchaV2.vue';
import { router } from '@inertiajs/vue3';
import axios from 'axios';
import { reactive, ref } from 'vue';

const fields = reactive({
    email: '',
    password: '',
});
const submitError = ref('');
const isSubmitting = ref(false);
const recaptchaToken = ref('');
const recaptchaRef = ref<InstanceType<typeof RecaptchaV2> | null>(null);

const handleSubmit = async () => {
    submitError.value = '';
    isSubmitting.value = true;
    try {
        if (!recaptchaToken.value) {
            submitError.value = 'Please complete the captcha.';
            isSubmitting.value = false;
            return;
        }
        const response = await axios.post('/api/login', { ...fields, recaptcha_token: recaptchaToken.value });
        localStorage.setItem('auth_token', response.data?.data?.token ?? '');
        recaptchaRef.value?.reset();
        router.visit('/');
    } catch (error: any) {
        submitError.value = error?.response?.data?.message ?? error?.message ?? 'Invalid credentials. Please try again.';
    } finally {
        isSubmitting.value = false;
    }
};
</script>

<template>
    <main class="relative min-h-screen overflow-hidden bg-gradient-to-b from-slate-100 via-white to-white px-4 py-16 text-slate-800">
        <div
            class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_top,_rgba(148,163,184,0.12),_transparent_60%),radial-gradient(circle_at_bottom,_rgba(125,211,252,0.18),_transparent_65%)]"
        />
        <div class="pointer-events-none absolute -top-32 left-1/3 h-72 w-72 -translate-x-1/2 rounded-full bg-sky-100 blur-3xl" />
        <div class="pointer-events-none absolute right-16 -bottom-24 h-64 w-64 rounded-full bg-slate-200 blur-3xl" />

        <section class="relative mx-auto flex w-full max-w-3xl flex-col rounded-[2.5rem] border border-slate-200 bg-white p-10">
            <header class="mb-10 text-center">
                <h1 class="mt-4 text-3xl leading-snug font-semibold text-slate-900 md:text-4xl">Login</h1>
            </header>

            <form class="grid gap-8" @submit.prevent="handleSubmit">
                <div class="grid gap-6 md:grid-cols-2">
                    <label
                        class="group relative flex flex-col gap-2 rounded-2xl border border-slate-200 bg-white px-6 py-5 transition focus-within:border-sky-300 hover:border-sky-200"
                    >
                        <span class="text-xs font-medium tracking-wider text-slate-400 uppercase">Email</span>
                        <input
                            v-model="fields.email"
                            type="text"
                            placeholder="john@example.com"
                            class="w-full bg-transparent text-lg font-semibold text-slate-900 transition duration-300 ease-out placeholder:text-slate-400 focus:outline-none"
                        />
                        <span
                            class="pointer-events-none absolute inset-x-6 bottom-5 h-px origin-left scale-x-0 rounded bg-sky-400 transition-transform duration-300 ease-out group-focus-within:scale-x-100 group-hover:scale-x-100"
                        />
                    </label>

                    <label
                        class="group relative flex flex-col gap-2 rounded-2xl border border-slate-200 bg-white px-6 py-5 transition focus-within:border-sky-300 hover:border-sky-200"
                    >
                        <span class="text-xs font-medium tracking-wider text-slate-400 uppercase">password</span>
                        <input
                            v-model="fields.password"
                            type="password"
                            placeholder="******"
                            class="w-full bg-transparent text-lg font-semibold text-slate-900 transition duration-300 ease-out placeholder:text-slate-400 focus:outline-none"
                        />
                        <span
                            class="pointer-events-none absolute inset-x-6 bottom-5 h-px origin-left scale-x-0 rounded bg-sky-400 transition-transform duration-300 ease-out group-focus-within:scale-x-100 group-hover:scale-x-100"
                        />
                    </label>
                </div>

                <p class="text-center text-sm text-red-600" v-if="submitError">{{ submitError }}</p>

                <RecaptchaV2 ref="recaptchaRef" v-model:token="recaptchaToken" />

                <div class="flex flex-col items-center gap-4 rounded-2xl bg-white px-6 py-5">
                    <button
                        type="submit"
                        :disabled="isSubmitting"
                        class="inline-flex items-center gap-2 rounded-full bg-sky-500 px-6 py-2 text-sm font-semibold tracking-wide text-white uppercase transition hover:bg-sky-600 focus-visible:ring-2 focus-visible:ring-sky-300 focus-visible:outline-none disabled:cursor-not-allowed disabled:bg-slate-300"
                    >
                        {{ isSubmitting ? 'Signing in...' : 'Login' }}
                    </button>
                    <button type="button" class="text-sm font-semibold text-sky-600 hover:underline" @click="router.visit('/register')">
                        Need an account? Register
                    </button>
                </div>
            </form>
        </section>
    </main>
</template>
