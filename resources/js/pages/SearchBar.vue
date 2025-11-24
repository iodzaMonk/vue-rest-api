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
const blurTimeout = ref<number>();
watch(
    () => props.userId,
    (val) => {
        selectedUserId.value = val ?? null;
    },
);

const updateDropdownVisibility = () => {
    dropdownOpen.value = suggestions.value.length > 0;
};

const fetchSuggestions = async () => {
    if (query.value.trim().length < 2) {
        suggestions.value = [];
        dropdownOpen.value = false;
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
        updateDropdownVisibility();
    } catch (error) {
        console.error('Failed to load suggestions', error);
        suggestions.value = [];
        dropdownOpen.value = false;
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

const handleFocus = () => {
    updateDropdownVisibility();
};

const handleBlur = () => {
    window.clearTimeout(blurTimeout.value);
    blurTimeout.value = window.setTimeout(() => {
        dropdownOpen.value = false;
    }, 120);
};
</script>

<template>
    <form class="relative w-full max-w-md" @submit.prevent="handleSubmit">
        <input
            v-model="query"
            type="text"
            role="combobox"
            :aria-expanded="dropdownOpen"
            :aria-controls="'search-suggestions'"
            @input="handleInput"
            @focus="handleFocus"
            @blur="handleBlur"
            placeholder="Search words"
            class="w-full rounded-xl border border-slate-300 px-4 py-2 text-slate-800 shadow-sm transition focus:border-sky-400 focus:ring-2 focus:ring-sky-200 focus:outline-none"
        />

        <transition name="fade">
            <ul
                v-if="dropdownOpen && suggestions.length"
                id="search-suggestions"
                role="listbox"
                class="absolute left-0 right-0 z-10 mt-2 max-h-64 overflow-auto rounded-xl border border-slate-200 bg-white shadow-lg"
            >
                <li v-for="(suggestion, index) in suggestions" :key="`${suggestion.title}-${index}`" role="option">
                    <button
                        type="button"
                        class="flex w-full items-center justify-between px-4 py-2 text-left text-slate-800 transition hover:bg-slate-100"
                        @mousedown.prevent="selectSuggestion(suggestion)"
                    >
                        <span class="font-medium">{{ suggestion.title }}</span>
                        <span v-if="suggestion.slug" class="text-xs text-slate-500">{{ suggestion.slug }}</span>
                    </button>
                </li>
            </ul>
        </transition>
    </form>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 120ms ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
