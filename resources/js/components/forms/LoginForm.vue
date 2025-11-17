<script setup lang="ts">
import RecaptchaV2 from '@/components/RecaptchaV2.vue';
import { ensureCsrfCookie } from '@/utils/csrf';
import axios from 'axios';
import { reactive, ref } from 'vue';

const emit = defineEmits<{
    (e: 'logged-in'): void;
}>();

const fields = reactive({
    email: '',
    password: '',
});

const isSubmitting = ref(false);
const submitError = ref('');
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
        await ensureCsrfCookie();
        const response = await axios.post('/api/login', {
            ...fields,
            recaptcha_token: recaptchaToken.value,
        });
        const token = response.data?.data?.token ?? '';
        localStorage.setItem('auth_token', token);
        if (token) {
            axios.defaults.headers.common.Authorization = `Bearer ${token}`;
        }
        Object.assign(fields, { email: '', password: '' });
        recaptchaRef.value?.reset();
        emit('logged-in');
    } catch (error: any) {
        submitError.value = error?.response?.data?.message ?? 'Invalid credentials. Please try again.';
    } finally {
        isSubmitting.value = false;
    }
};
</script>

<template>
    <form class="grid gap-6" @submit.prevent="handleSubmit">
        <div class="grid gap-4 md:grid-cols-2">
            <label class="flex flex-col gap-2">
                <span class="text-xs font-semibold tracking-wide text-slate-500 uppercase">Email</span>
                <input
                    v-model="fields.email"
                    type="email"
                    class="rounded-2xl border border-slate-200 px-4 py-3 text-base text-slate-900 focus:border-sky-400 focus:outline-none"
                    placeholder="john@example.com"
                />
            </label>
            <label class="flex flex-col gap-2">
                <span class="text-xs font-semibold tracking-wide text-slate-500 uppercase">Password</span>
                <input
                    v-model="fields.password"
                    type="password"
                    class="rounded-2xl border border-slate-200 px-4 py-3 text-base text-slate-900 focus:border-sky-400 focus:outline-none"
                    placeholder="••••••••"
                />
            </label>
        </div>

        <RecaptchaV2 ref="recaptchaRef" v-model:token="recaptchaToken" />

        <p v-if="submitError" class="text-sm text-red-600">{{ submitError }}</p>

        <div class="flex justify-end">
            <button
                type="submit"
                :disabled="isSubmitting"
                class="rounded-full bg-sky-500 px-6 py-3 text-sm font-semibold tracking-wide text-white uppercase transition hover:bg-sky-600 focus-visible:ring-2 focus-visible:ring-sky-300 focus-visible:outline-none disabled:cursor-not-allowed disabled:bg-slate-300"
            >
                {{ isSubmitting ? 'Signing in...' : 'Login' }}
            </button>
        </div>
    </form>
</template>
