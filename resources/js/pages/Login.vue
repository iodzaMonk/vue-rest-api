<script setup lang="ts">
import Header from '@/components/Header.vue';
</script>

<template>
    <Header />
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

            <form class="grid gap-8" @submit.prevent="">
                <div class="grid gap-6 md:grid-cols-2">
                    <label
                        class="group relative flex flex-col gap-2 rounded-2xl border border-slate-200 bg-white px-6 py-5 transition focus-within:border-sky-300 hover:border-sky-200"
                    >
                        <span class="text-xs font-medium tracking-wider text-slate-400 uppercase">Username</span>
                        <input
                            type="text"
                            placeholder="john doe"
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
                            type="password"
                            placeholder="******"
                            class="w-full bg-transparent text-lg font-semibold text-slate-900 transition duration-300 ease-out placeholder:text-slate-400 focus:outline-none"
                        />
                        <span
                            class="pointer-events-none absolute inset-x-6 bottom-5 h-px origin-left scale-x-0 rounded bg-sky-400 transition-transform duration-300 ease-out group-focus-within:scale-x-100 group-hover:scale-x-100"
                        />
                    </label>
                </div>

                <div class="flex items-center justify-center rounded-2xl bg-white px-6 py-5">
                    <button
                        type="submit"
                        class="inline-flex items-center gap-2 rounded-full bg-sky-500 px-6 py-2 text-sm font-semibold tracking-wide text-white uppercase transition hover:bg-sky-600 focus-visible:ring-2 focus-visible:ring-sky-300 focus-visible:outline-none"
                    >
                        Save Word
                    </button>
                </div>
            </form>
        </section>
    </main>
</template>
