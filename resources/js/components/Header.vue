<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

defineProps<{
    isAuthenticated: boolean;
}>();

const emit = defineEmits<{
    (e: 'open-register'): void;
    (e: 'open-login'): void;
    (e: 'open-create-word'): void;
    (e: 'logout'): void;
}>();
</script>

<template>
    <header class="flex w-full justify-center">
        <div class="flex w-4xl items-center justify-between gap-5 p-2">
            <div class="flex items-center justify-center gap-5">
                <h1 class="tracking-widest uppercase">Winter dictionary</h1>
                <Link href="/">
                    <img src="/img/logo.svg" class="size-15" alt="logo" />
                </Link>
            </div>
            <div class="flex items-center gap-3">
                <Link href="/about" class="rounded bg-slate-900 px-5 py-3 text-sm font-semibold text-white transition hover:bg-slate-700">About</Link>
                <template v-if="!isAuthenticated">
                    
                    <button
                        type="button"
                        class="rounded bg-slate-900 px-5 py-3 text-sm font-semibold text-white transition hover:bg-slate-800"
                        @click="emit('open-register')"
                    >
                        Register
                    </button>
                    <button
                        type="button"
                        class="rounded bg-slate-200 px-5 py-3 text-sm font-semibold text-slate-900 transition hover:bg-slate-300"
                        @click="emit('open-login')"
                    >
                        Login
                    </button>
                </template>
                
                <button
                    v-else
                    type="button"
                    class="rounded bg-slate-900 px-5 py-3 text-sm font-semibold text-white transition hover:bg-red-600"
                    @click="emit('logout')"
                >
                    Logout
                </button>
            </div>
        </div>
    </header>
</template>
