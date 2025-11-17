<script setup lang="ts">
import { onMounted, ref, watch } from 'vue';

const tokenModel = defineModel<string>('token', { default: '' });

const siteKey = import.meta.env.VITE_RECAPTCHA_SITE_KEY;

const containerRef = ref<HTMLElement | null>(null);
const widgetId = ref<number | null>(null);
const isLoading = ref(false);
const errorMessage = ref('');

let loader: Promise<void> | null = null;
const scheduleVisibilityCheck = () => {
    // After a short delay, if the widget never rendered, surface a message.
    setTimeout(() => {
        if (!widgetId.value && !isLoading.value) {
            errorMessage.value = 'Failed to load captcha. Please refresh and try again.';
        }
    }, 3000);
};

const loadScript = () => {
    if (loader) return loader;

    loader = new Promise<void>((resolve, reject) => {
        if (window.grecaptcha?.render) {
            return resolve();
        }

        const existing = document.querySelector<HTMLScriptElement>('script[data-recaptcha-script]');
        if (existing) {
            existing.addEventListener('load', () => resolve());
            existing.addEventListener('error', () => reject(new Error('Failed to load reCAPTCHA.')));
            return;
        }

        const script = document.createElement('script');
        script.src = 'https://www.google.com/recaptcha/api.js?render=explicit';
        script.async = true;
        script.defer = true;
        script.dataset.recaptchaScript = 'true';
        script.onload = () => resolve();
        script.onerror = () => reject(new Error('Failed to load reCAPTCHA.'));
        document.head.appendChild(script);
    });

    return loader;
};

const renderWidget = () => {
    if (!siteKey) {
        errorMessage.value = 'Missing reCAPTCHA site key.';
        return;
    }
    if (!containerRef.value || widgetId.value !== null || !window.grecaptcha?.render) return;

    widgetId.value = window.grecaptcha.render(containerRef.value, {
        sitekey: siteKey,
        callback: (token: string) => {
            tokenModel.value = token;
            errorMessage.value = '';
        },
        'expired-callback': () => {
            // keep the widget visible, just clear token
            reset(false);
        },
        'error-callback': () => {
            errorMessage.value = 'Captcha failed to load. Please try again.';
            // clear and attempt a rerender
            reset(true);
        },
    });

    scheduleVisibilityCheck();
};

const reset = (reRender = false) => {
    tokenModel.value = '';
    if (widgetId.value !== null && window.grecaptcha?.reset) {
        window.grecaptcha.reset(widgetId.value);
    }

    if (reRender) {
        widgetId.value = null;
        if (containerRef.value) {
            containerRef.value.innerHTML = '';
        }
        renderWidget();
    }
};

onMounted(async () => {
    isLoading.value = true;
    errorMessage.value = '';
    try {
        await loadScript();
        renderWidget();
        scheduleVisibilityCheck();
    } catch (error: any) {
        errorMessage.value = error?.message ?? 'Unable to load captcha.';
    } finally {
        isLoading.value = false;
    }
});

watch(
    () => siteKey,
    () => {
        // if site key changes during HMR, reset render
        reset();
        renderWidget();
    },
);

defineExpose({ reset });
</script>

<template>
    <div class="flex flex-col gap-2">
        <div ref="containerRef" class="flex justify-center"></div>
        <p v-if="isLoading" class="text-center text-xs text-slate-500">Loading captcha…</p>
        <p v-if="errorMessage" class="text-center text-xs text-red-600">{{ errorMessage }}</p>
    </div>
</template>
