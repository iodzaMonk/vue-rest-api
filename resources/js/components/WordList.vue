<script setup lang="ts">
import type { Word } from '@/types/word';

const props = defineProps<{
    words: Word[];
    isLoading: boolean;
    errorMessage: string;
}>();

const emit = defineEmits<{
    (e: 'edit', word: Word): void;
    (e: 'delete', word: Word): void;
}>();

const imageUrl = (path?: string | null): string | undefined => {
    if (!path) {
        return undefined;
    }

    if (path.startsWith('http://') || path.startsWith('https://')) {
        return path;
    }

    return `/${path.replace(/^\/+/, '')}`;
};
</script>

<template>
    <section class="flex flex-col gap-5">
        <header class="text-center">
            <h1 class="mt-4 text-3xl leading-snug font-semibold text-slate-900 md:text-4xl">List of Words</h1>
        </header>

        <p v-if="errorMessage" class="text-center text-sm text-red-600">{{ errorMessage }}</p>
        <p v-else-if="isLoading" class="text-center text-sm text-slate-500">Loading words...</p>
        <p v-else-if="!props.words.length" class="text-center text-sm text-slate-500">No entries yet. Add your first word above.</p>

        <div v-else class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <article
                v-for="word in props.words"
                :key="word.id"
                class="flex h-full flex-col overflow-hidden rounded-3xl border border-slate-100 bg-white shadow-lg ring-1 ring-slate-100 transition hover:-translate-y-1 hover:shadow-xl"
            >
                <div v-if="imageUrl(word.image)" class="relative h-48 w-full overflow-hidden bg-slate-100">
                    <img :src="imageUrl(word.image)" :alt="`Image for ${word.word}`" class="h-full w-full object-cover" />
                    <span class="absolute top-4 left-4 rounded-full bg-white/80 px-3 py-1 text-xs font-semibold text-slate-700 uppercase shadow">
                        {{ word.difficulty }}
                    </span>
                </div>
                <div
                    v-else
                    class="flex h-48 w-full items-center justify-center bg-gradient-to-br from-slate-100 to-slate-200 text-5xl font-bold text-slate-400"
                >
                    {{ word.word.charAt(0).toUpperCase() }}
                </div>
                <div class="flex flex-1 flex-col gap-4 p-6">
                    <header>
                        <h2 class="text-2xl font-bold text-gray-900">{{ word.word }}</h2>
                        <p class="text-sm text-slate-500">Pronunciation: {{ word.pronunciation }}</p>
                    </header>
                    <div class="space-y-2 text-sm text-slate-600">
                        <p><span class="font-semibold text-slate-800">Part of Speech:</span> {{ word.part_of_speech }}</p>
                        <p><span class="font-semibold text-slate-800">Meaning:</span> {{ word.meaning }}</p>
                        <p><span class="font-semibold text-slate-800">Example:</span> "{{ word.example_sentence }}"</p>
                    </div>
                    <div class="mt-auto flex justify-end gap-3">
                        <button
                            type="button"
                            class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-600 transition hover:bg-slate-100 focus-visible:ring-2 focus-visible:ring-slate-200 focus-visible:outline-none"
                            @click="emit('delete', word)"
                        >
                            Delete
                        </button>
                        <button
                            type="button"
                            class="rounded-lg bg-sky-500 px-4 py-2 text-sm font-semibold text-white transition hover:bg-sky-600 focus-visible:ring-2 focus-visible:ring-sky-300 focus-visible:outline-none"
                            @click="emit('edit', word)"
                        >
                            Edit
                        </button>
                    </div>
                </div>
            </article>
        </div>
    </section>
</template>
