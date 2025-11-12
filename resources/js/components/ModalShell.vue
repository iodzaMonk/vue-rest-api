<script setup lang="ts">
defineProps<{
    title?: string;
    sizeClass?: string;
}>();

const emit = defineEmits<{
    (e: 'close'): void;
}>();
</script>

<template>
    <div class="fixed inset-0 z-50 flex items-center justify-center px-4 py-8">
        <div class="absolute inset-0 bg-slate-900/50" @click="emit('close')" />
        <div class="relative z-10 max-h-[90vh] w-full overflow-y-auto rounded-3xl bg-white p-8 shadow-2xl" :class="sizeClass ?? 'max-w-3xl'">
            <div class="mb-6 flex items-center justify-between">
                <h2 v-if="title" class="text-2xl font-semibold text-slate-900">{{ title }}</h2>
                <button
                    type="button"
                    class="rounded-full p-2 text-slate-500 transition hover:bg-slate-100 hover:text-slate-700"
                    aria-label="Close"
                    @click="emit('close')"
                >
                    ✕
                </button>
            </div>
            <slot />
        </div>
    </div>
</template>
