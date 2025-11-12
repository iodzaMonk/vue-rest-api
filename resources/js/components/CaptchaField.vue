<script setup lang="ts">
import axios from 'axios';
import { onMounted, ref } from 'vue';

const answerModel = defineModel<string>('answer', { default: '' });
const tokenModel = defineModel<string>('token', { default: '' });

const question = ref('');
const isLoading = ref(false);
const errorMessage = ref('');

const fetchCaptcha = async () => {
    isLoading.value = true;
    errorMessage.value = '';
    try {
        const { data } = await axios.get('/api/captcha');
        question.value = data.question;
        tokenModel.value = data.token;
        answerModel.value = '';
    } catch (error: any) {
        errorMessage.value = error?.response?.data?.message ?? 'Unable to load captcha. Try again.';
    } finally {
        isLoading.value = false;
    }
};

onMounted(fetchCaptcha);

defineExpose({ refresh: fetchCaptcha });
</script>

<template>
    <div class="flex flex-col gap-2 rounded-2xl border border-slate-200 bg-slate-50 p-4">
        <div class="flex items-center justify-between">
            <span class="text-xs font-semibold uppercase tracking-wide text-slate-500">Captcha</span>
            <button
                type="button"
                class="text-xs font-semibold text-sky-600 hover:underline"
                @click="fetchCaptcha"
                :disabled="isLoading"
            >
                {{ isLoading ? 'Refreshing…' : 'Refresh' }}
            </button>
        </div>
        <p class="text-sm font-medium text-slate-800">{{ question || 'Loading question…' }}</p>
        <input
            v-model="answerModel"
            type="text"
            placeholder="Type your answer"
            class="rounded-xl border border-slate-300 px-4 py-2 text-base text-slate-900 focus:border-sky-400 focus:outline-none"
        />
        <p v-if="errorMessage" class="text-xs text-red-600">{{ errorMessage }}</p>
    </div>
</template>
