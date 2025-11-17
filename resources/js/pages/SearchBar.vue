<template>
    <form class="relative w-full max-w-md" @submit.prevent="handleSubmit">
        <input
            v-model="query"
            type="text"
            @input="handleInput"
            placeholder="Search words"
            class="w-full rounded-xl border border-slate-300 px-4 py-2 text-slate-800 shadow-sm transition focus:border-sky-400 focus:ring-2 focus:ring-sky-200 focus:outline-none"
        />
    </form>
</template>

<script setup lang="ts">
import axios from 'axios';
import { ref, watch } from 'vue';

type SearchSuggestion = {
    title: string;
    id?: number | string;
    slug?: string;
};

const emit = defineEmits<{
    (e: 'search', term: string): void;
}>();

const props = defineProps<{
    userId?: string | number | null;
}>();

const query = ref('');
const suggestions = ref<SearchSuggestion[]>([]);
const selectedUserId = ref<string | number | null>(props.userId ?? null);
const dropdownOpen = ref(false);
watch(
    () => props.userId,
    (val) => {
        selectedUserId.value = val ?? null;
    },
);

const fetchSuggestions = async () => {
    if (query.value.trim().length < 2) {
        suggestions.value = [];
        return;
    }

    try {
        const { data } = await axios.get('/api/words/autocomplete', {
            params: {
                query: query.value,
                user_id: selectedUserId.value,
            },
        });

        const list = (data && (data.data ?? data)) || [];
        suggestions.value = list.map((item: any) =>
            typeof item === 'string' ? ({ title: item } as SearchSuggestion) : { title: item.title ?? `${item}`, ...item },
        );
    } catch (error) {
        console.error('Failed to load suggestions', error);
        suggestions.value = [];
    }
};

const selectSuggestion = (suggestion: SearchSuggestion) => {
    query.value = suggestion.title;
    suggestions.value = [];
    dropdownOpen.value = false;

    emit('search', suggestion.title);
};

const handleSubmit = async () => {
    await fetchSuggestions();

    if (suggestions.value.length) {
        selectSuggestion(suggestions.value[0]);
    } else {
        emit('search', query.value);
    }
};

const handleInput = async () => {
    emit('search', query.value);
    await fetchSuggestions();
};
</script>
