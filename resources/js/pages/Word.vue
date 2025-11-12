<script setup lang="ts">
import Header from '@/components/Header.vue';
import WordForm from '@/components/WordForm.vue';
import { Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const successMessage = ref('');

const handleCreated = () => {
    successMessage.value = 'Word created successfully. Redirecting to list...';
    setTimeout(() => {
        router.visit('/');
    }, 1200);
};
</script>

<template>
    <Header />
    <main class="relative min-h-screen overflow-hidden bg-gradient-to-b from-slate-100 via-white to-white px-4 py-16 text-slate-800">
        <div
            class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_top,_rgba(148,163,184,0.12),_transparent_60%),radial-gradient(circle_at_bottom,_rgba(125,211,252,0.18),_transparent_65%)]"
        />
        <div class="pointer-events-none absolute -top-32 left-1/3 h-72 w-72 -translate-x-1/2 rounded-full bg-sky-100 blur-3xl" />
        <div class="pointer-events-none absolute right-16 -bottom-24 h-64 w-64 rounded-full bg-slate-200 blur-3xl" />

        <section class="mx-auto flex w-full max-w-4xl flex-col gap-8">
            <div class="flex flex-col gap-3">
                <Link
                    href="/"
                    class="w-max rounded-full border border-slate-200 px-5 py-2 text-sm font-semibold tracking-wide text-slate-700 uppercase transition hover:bg-slate-100 focus-visible:ring-2 focus-visible:ring-slate-300 focus-visible:outline-none"
                >
                    Back to list
                </Link>
                <p v-if="successMessage" class="text-sm text-green-600">{{ successMessage }}</p>
            </div>
            <WordForm @word-created="handleCreated" />
        </section>
    </main>
</template>
