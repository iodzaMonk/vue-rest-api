<script setup lang="ts">
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import LoginForm from '@/components/forms/LoginForm.vue';
import RegisterForm from '@/components/forms/RegisterForm.vue';
import Header from '@/components/Header.vue';
import ModalShell from '@/components/ModalShell.vue';
import WordForm from '@/components/WordForm.vue';
import WordList from '@/components/WordList.vue';
import type { Word } from '@/types/word';
import axios from 'axios';
import { computed, onMounted, ref } from 'vue';

const words = ref<Word[]>([]);
const isLoading = ref(false);
const errorMessage = ref('');
const showRegisterModal = ref(false);
const showLoginModal = ref(false);
const showCreateWordModal = ref(false);
const editingWord = ref<Word | null>(null);
const toastMessage = ref('');
const wordPendingDeletion = ref<Word | null>(null);
const isDeleting = ref(false);
const authToken = ref<string | null>(typeof window === 'undefined' ? null : window.localStorage.getItem('auth_token'));
const isAuthenticated = computed(() => Boolean(authToken.value));

const setWords = (list: Word[]) => {
    words.value = list;
};

const setToast = (message: string) => {
    toastMessage.value = message;
    setTimeout(() => {
        toastMessage.value = '';
    }, 3500);
};

const ensureAuthenticated = () => {
    if (authToken.value) {
        return true;
    }

    errorMessage.value = 'Please log in to continue.';
    showLoginModal.value = true;
    return false;
};

const fetchWords = async () => {
    if (!authToken.value) {
        words.value = [];
        errorMessage.value = 'Please log in to see your words.';
        return;
    }

    isLoading.value = true;
    errorMessage.value = '';

    try {
        const { data } = await axios.get('/api/words');
        const list: Word[] = data?.data?.data ?? [];
        setWords(list);
    } catch (error: any) {
        if (error?.response?.status === 401) {
            authToken.value = null;
            delete axios.defaults.headers.common.Authorization;
            words.value = [];
            errorMessage.value = 'Session expired. Please log in again.';
            showLoginModal.value = true;
        } else {
            errorMessage.value = error?.response?.data?.message ?? 'Failed to load words.';
        }
    } finally {
        isLoading.value = false;
    }
};

const openCreateWordModal = () => {
    if (!ensureAuthenticated()) {
        return;
    }
    showCreateWordModal.value = true;
};

const startEditing = (word: Word) => {
    if (!ensureAuthenticated()) {
        return;
    }
    editingWord.value = word;
};

const handleWordUpdated = (word: Word) => {
    words.value = words.value.map((item) => (item.id === word.id ? word : item));
    editingWord.value = null;
    setToast('Word updated successfully.');
};

const handleWordCreated = (word: Word) => {
    words.value = [word, ...words.value];
    showCreateWordModal.value = false;
    setToast('Word created successfully.');
};

const handleRegisterSuccess = () => {
    showRegisterModal.value = false;
    showLoginModal.value = true;
    setToast('Account created. Please log in.');
};

const handleLoginSuccess = () => {
    showLoginModal.value = false;
    const token = window.localStorage.getItem('auth_token');
    authToken.value = token;
    if (token) {
        axios.defaults.headers.common.Authorization = `Bearer ${token}`;
    }
    fetchWords();
    setToast('Logged in successfully.');
};

const requestDeleteWord = (word: Word) => {
    if (!ensureAuthenticated()) {
        return;
    }
    wordPendingDeletion.value = word;
};

const confirmDeleteWord = async () => {
    if (!wordPendingDeletion.value) {
        return;
    }

    isDeleting.value = true;

    try {
        await axios.delete(`/api/words/${wordPendingDeletion.value.id}`);
        words.value = words.value.filter((item) => item.id !== wordPendingDeletion.value?.id);
        setToast('Word deleted successfully.');
        wordPendingDeletion.value = null;
    } catch (error: any) {
        if (error?.response?.status === 401) {
            authToken.value = null;
            delete axios.defaults.headers.common.Authorization;
            showLoginModal.value = true;
            errorMessage.value = 'Session expired. Please log in again.';
        } else {
            setToast('Unable to delete word.');
        }
    } finally {
        isDeleting.value = false;
    }
};

const handleLogout = () => {
    showCreateWordModal.value = false;
    editingWord.value = null;
    authToken.value = null;
    window.localStorage.removeItem('auth_token');
    delete axios.defaults.headers.common.Authorization;
    words.value = [];
    errorMessage.value = 'You are logged out.';
    setToast('Logged out successfully.');
};

onMounted(fetchWords);
</script>
<template>
    <Header
        :is-authenticated="isAuthenticated"
        @open-register="showRegisterModal = true"
        @open-login="showLoginModal = true"
        @open-create-word="openCreateWordModal"
        @logout="handleLogout"
    />

    <main class="relative min-h-screen overflow-hidden bg-gradient-to-b from-slate-100 via-white to-white px-4 py-16 text-slate-800">
        <div
            class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_top,_rgba(148,163,184,0.12),_transparent_60%),radial-gradient(circle_at_bottom,_rgba(125,211,252,0.18),_transparent_65%)]"
        />
        <div class="pointer-events-none absolute -top-32 left-1/3 h-72 w-72 -translate-x-1/2 rounded-full bg-sky-100 blur-3xl" />
        <div class="pointer-events-none absolute right-16 -bottom-24 h-64 w-64 rounded-full bg-slate-200 blur-3xl" />
        <section class="mx-auto flex w-full max-w-6xl flex-col gap-10">
            <div class="flex items-center justify-between">
                <h2 class="text-2xl font-semibold text-slate-900">Vocabulary</h2>
                <div class="flex items-center gap-4">
                    <span v-if="!isAuthenticated" class="text-sm text-slate-500">Log in to add or edit your words.</span>
                    <button
                        type="button"
                        :disabled="!isAuthenticated"
                        class="rounded-full bg-sky-500 px-6 py-3 text-sm font-semibold tracking-wide text-white uppercase transition hover:bg-sky-600 focus-visible:ring-2 focus-visible:ring-sky-300 focus-visible:outline-none disabled:cursor-not-allowed disabled:bg-slate-400"
                        @click="openCreateWordModal"
                    >
                        Add new word
                    </button>
                </div>
            </div>
            <WordList :words="words" :is-loading="isLoading" :error-message="errorMessage" @edit="startEditing" @delete="requestDeleteWord" />
        </section>
    </main>

    <div
        v-if="toastMessage"
        class="fixed bottom-5 left-1/2 z-50 -translate-x-1/2 rounded-full bg-slate-900/90 px-6 py-3 text-sm font-semibold text-white shadow-xl"
    >
        {{ toastMessage }}
    </div>

    <ModalShell v-if="showRegisterModal" title="Create an account" @close="showRegisterModal = false">
        <RegisterForm @registered="handleRegisterSuccess" />
    </ModalShell>

    <ModalShell v-if="showLoginModal" title="Welcome back" @close="showLoginModal = false">
        <LoginForm @logged-in="handleLoginSuccess" />
    </ModalShell>

    <ModalShell v-if="showCreateWordModal" title="Add a new word" @close="showCreateWordModal = false">
        <WordForm @word-created="handleWordCreated" />
    </ModalShell>

    <ModalShell v-if="editingWord" title="Edit word" @close="editingWord = null">
        <WordForm mode="edit" :initial-word="editingWord" @word-updated="handleWordUpdated" @cancel="editingWord = null" />
    </ModalShell>

    <ModalShell v-if="wordPendingDeletion" title="Delete word" @close="wordPendingDeletion = null">
        <ConfirmDialog
            :title="`Delete ${wordPendingDeletion.word}?`"
            message="This action permanently removes the word and its image."
            confirm-label="Delete"
            cancel-label="Keep"
            :loading="isDeleting"
            @confirm="confirmDeleteWord"
            @cancel="wordPendingDeletion = null"
        />
    </ModalShell>
</template>
