<script setup lang="ts">
import type { Word } from '@/types/word';
import { computed } from 'vue';

const props = defineProps<{
    word: Word;
}>();

const imageUrl = computed(() => {
    const path = props.word.image;
    if (!path) return null;
    if (path.startsWith('http://') || path.startsWith('https://')) {
        return path;
    }
    return `/${path.replace(/^\/+/, '')}`;
});
</script>

<template>
    <article class="flex flex-col gap-6">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Vocabulary card</p>
                <h2 class="mt-1 text-3xl font-bold text-slate-900">{{ word.word }}</h2>
                <p class="text-sm text-slate-500">Pronunciation: {{ word.pronunciation }}</p>
            </div>
            <span class="inline-flex items-center rounded-full bg-slate-100 px-4 py-1 text-xs font-semibold uppercase text-slate-700">
                {{ word.difficulty }}
            </span>
        </div>

        <div v-if="imageUrl" class="overflow-hidden rounded-2xl border border-slate-100 shadow-sm">
            <img :src="imageUrl" :alt="`Image for ${word.word}`" class="h-64 w-full object-cover" />
        </div>

        <dl class="grid grid-cols-1 gap-4 rounded-2xl bg-slate-50/60 p-5 text-slate-800 md:grid-cols-2">
            <div class="space-y-1">
                <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Part of speech</dt>
                <dd class="text-base font-medium">{{ word.part_of_speech }}</dd>
            </div>
            <div class="space-y-1">
                <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Meaning</dt>
                <dd class="text-base leading-relaxed">{{ word.meaning }}</dd>
            </div>
            <div class="space-y-1 md:col-span-2">
                <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Example sentence</dt>
                <dd class="text-base leading-relaxed italic">“{{ word.example_sentence }}”</dd>
            </div>
        </dl>
    </article>
</template>
