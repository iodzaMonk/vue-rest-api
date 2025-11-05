<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <header class="flex w-full justify-center">
        <div class="flex w-4xl items-center justify-between gap-5 p-2">
            <div class="flex items-center justify-center gap-5">
                <h1 class="tracking-widest uppercase">Winter update</h1>
                <Link href="/">
                    <img src="/img/logo.svg" class="size-15" alt="logo" />
                </Link>
            </div>

            <div class="space-x-5">
                <Link href="/register" class="rounded bg-sky-200 px-5 py-3">Register</Link>
                <Link href="/login" class="rounded bg-sky-200 px-5 py-3">Login</Link>
            </div>
        </div>
    </header>
</template>
