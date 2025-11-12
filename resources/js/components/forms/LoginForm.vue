<script setup lang="ts">
import CaptchaField from '@/components/CaptchaField.vue';
import axios from 'axios';
import { reactive, ref } from 'vue';
import { ensureCsrfCookie } from '@/utils/csrf';

const emit = defineEmits<{
    (e: 'logged-in'): void;
}>();

const fields = reactive({
    email: '',
    password: '',
});

const isSubmitting = ref(false);
const submitError = ref('');
const captchaToken = ref('');
const captchaAnswer = ref('');
const captchaRef = ref<InstanceType<typeof CaptchaField> | null>(null);

const handleSubmit = async () => {
    submitError.value = '';
    isSubmitting.value = true;

    try {
        await ensureCsrfCookie();
        const response = await axios.post('/api/login', {
            ...fields,
            captcha_token: captchaToken.value,
            captcha_answer: captchaAnswer.value,
        });
        const token = response.data?.data?.token ?? '';
        localStorage.setItem('auth_token', token);
        if (token) {
            axios.defaults.headers.common.Authorization = `Bearer ${token}`;
        }
        Object.assign(fields, { email: '', password: '' });
        captchaAnswer.value = '';
        captchaRef.value?.refresh();
        emit('logged-in');
    } catch (error: any) {
        submitError.value = error?.response?.data?.message ?? 'Invalid credentials. Please try again.';
    } finally {
        isSubmitting.value = false;
        captchaRef.value?.refresh();
    }
};
</script>

<template>
    <form class="grid gap-6" @submit.prevent="handleSubmit">
        <div class="grid gap-4 md:grid-cols-2">
            <label class="flex flex-col gap-2">
                <span class="text-xs font-semibold uppercase tracking-wide text-slate-500">Email</span>
                <input
                    v-model="fields.email"
                    type="email"
                    class="rounded-2xl border border-slate-200 px-4 py-3 text-base text-slate-900 focus:border-sky-400 focus:outline-none"
                    placeholder="john@example.com"
                />
            </label>
            <label class="flex flex-col gap-2">
                <span class="text-xs font-semibold uppercase tracking-wide text-slate-500">Password</span>
                <input
                    v-model="fields.password"
                    type="password"
                    class="rounded-2xl border border-slate-200 px-4 py-3 text-base text-slate-900 focus:border-sky-400 focus:outline-none"
                    placeholder="••••••••"
                />
            </label>
        </div>

        <CaptchaField ref="captchaRef" v-model:token="captchaToken" v-model:answer="captchaAnswer" />

        <p v-if="submitError" class="text-sm text-red-600">{{ submitError }}</p>

        <div class="flex justify-end">
            <button
                type="submit"
                :disabled="isSubmitting"
                class="rounded-full bg-sky-500 px-6 py-3 text-sm font-semibold uppercase tracking-wide text-white transition hover:bg-sky-600 focus-visible:ring-2 focus-visible:ring-sky-300 focus-visible:outline-none disabled:cursor-not-allowed disabled:bg-slate-300"
            >
                {{ isSubmitting ? 'Signing in...' : 'Login' }}
            </button>
        </div>
    </form>
</template>
