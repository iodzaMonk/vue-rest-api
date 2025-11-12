<script setup lang="ts">
const props = defineProps<{
    title?: string;
    message?: string;
    confirmLabel?: string;
    cancelLabel?: string;
    loading?: boolean;
}>();

const emit = defineEmits<{
    (e: 'confirm'): void;
    (e: 'cancel'): void;
}>();
</script>

<template>
    <div class="flex flex-col gap-4">
        <div>
            <h3 class="text-xl font-semibold text-slate-900">{{ title ?? 'Are you sure?' }}</h3>
            <p class="mt-2 text-sm text-slate-600">{{ message ?? 'This action cannot be undone.' }}</p>
        </div>
        <div class="flex justify-end gap-3">
            <button
                type="button"
                class="rounded-full border border-slate-300 px-5 py-2 text-sm font-semibold text-slate-600 transition hover:bg-slate-100"
                @click="emit('cancel')"
                :disabled="loading"
            >
                {{ props.cancelLabel ?? 'Cancel' }}
            </button>
            <button
                type="button"
                class="rounded-full bg-red-500 px-5 py-2 text-sm font-semibold text-white transition hover:bg-red-600 focus-visible:ring-2 focus-visible:ring-red-300 focus-visible:outline-none disabled:cursor-not-allowed disabled:bg-red-300"
                @click="emit('confirm')"
                :disabled="loading"
            >
                {{ loading ? 'Deleting…' : props.confirmLabel ?? 'Delete' }}
            </button>
        </div>
    </div>
</template>
