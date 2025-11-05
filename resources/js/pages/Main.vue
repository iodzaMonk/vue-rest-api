<script setup lang="ts">
import Header from '@/components/Header.vue';
import axios from 'axios';
import { onMounted, ref } from 'vue';

type Word = {
    word_id: string;
    word: string;
    pronunciation: string;
    part_of_speech: string;
    example: string;
    meaning: string;
    difficulty: string;
};

const words = ref<Word[]>([]);

function setWords(list: Word[]) {
    words.value = list;
}
const fetchWords = async () => {
    const { data } = await axios.get('/api/words');
    setWords(data);
};

onMounted(fetchWords);
</script>
<template>
    <Header />

    <main class="relative min-h-screen overflow-hidden bg-gradient-to-b from-slate-100 via-white to-white px-4 py-16 text-slate-800">
        <div
            class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_top,_rgba(148,163,184,0.12),_transparent_60%),radial-gradient(circle_at_bottom,_rgba(125,211,252,0.18),_transparent_65%)]"
        />
        <div class="pointer-events-none absolute -top-32 left-1/3 h-72 w-72 -translate-x-1/2 rounded-full bg-sky-100 blur-3xl" />
        <div class="pointer-events-none absolute right-16 -bottom-24 h-64 w-64 rounded-full bg-slate-200 blur-3xl" />
        <section class="mx-20 flex flex-col gap-5">
            <header class="mb-10 text-center">
                <h1 class="mt-4 text-3xl leading-snug font-semibold text-slate-900 md:text-4xl">List of Words</h1>
            </header>
            <div v-for="word in words" :key="word.word_id" class="grid gap-6 md:grid-cols-3">
                <card class="flex items-center justify-center rounded-2xl bg-slate-200 p-5">
                    <div class="w-full max-w-md rounded-xl bg-white p-6 shadow-lg transition-shadow duration-300 hover:shadow-xl">
                        <h1 class="mb-4 text-2xl font-bold text-gray-800">{{ word.word }}</h1>
                        <p class="text-gray-600"><span class="font-semibold text-gray-700">Pronunciation:</span> {{ word.pronunciation }}</p>
                        <p class="text-gray-600"><span class="font-semibold text-gray-700">Part of Speech:</span> {{ word.part_of_speech }}</p>
                        <p class="text-gray-600"><span class="font-semibold text-gray-700">Example:</span> "{{ word.example }}"</p>
                        <p class="text-gray-600"><span class="font-semibold text-gray-700">Meaning:</span> "{{ word.meaning }}"</p>
                        <p class="text-gray-600"><span class="font-semibold text-gray-700">Difficulty:</span> "{{ word.difficulty }}"</p>
                        <button
                            class="rounded-lg bg-sky-500 px-4 py-2 font-semibold text-white hover:bg-sky-600 focus:ring-2 focus:ring-sky-400 focus:outline-none"
                        >
                            Edit
                        </button>
                    </div>
                </card>
            </div>
        </section>
    </main>
</template>
