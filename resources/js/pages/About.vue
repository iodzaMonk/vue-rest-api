<script setup lang="ts">
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import LoginForm from '@/components/forms/LoginForm.vue';
import RegisterForm from '@/components/forms/RegisterForm.vue';
import Header from '@/components/Header.vue';
import ModalShell from '@/components/ModalShell.vue';
import WordForm from '@/components/WordForm.vue';
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
const schemaPreview = ref<{ src: string; title: string } | null>(null);
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

const openSchemaPreview = (src: string, title: string) => {
    schemaPreview.value = { src, title };
};

const closeSchemaPreview = () => {
    schemaPreview.value = null;
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
        <section class="mx-auto max-w-3xl">
            <h1 class="text-center text-3xl font-semibold tracking-tight text-slate-800">About this website</h1>

            <!-- General Information -->
            <div class="mt-6 rounded-lg border border-slate-200 bg-slate-50 p-6 shadow-sm">
                <h2 class="text-xl font-medium text-slate-700">General Information</h2>
                <dl class="mt-4 divide-y divide-slate-200">
                    <div class="grid grid-cols-1 gap-2 py-3 sm:grid-cols-3">
                        <dt class="text-sm font-medium text-slate-500">Names</dt>
                        <dd class="text-sm text-slate-800 sm:col-span-2">Vitali Melnik and Nazimzhon Mulubaev</dd>
                    </div>
                    <div class="grid grid-cols-1 gap-2 py-3 sm:grid-cols-3">
                        <dt class="text-sm font-medium text-slate-500">Course</dt>
                        <dd class="text-sm text-slate-800 sm:col-span-2">4205H6MO — Applications Web transactionnelles</dd>
                    </div>
                    <div class="grid grid-cols-1 gap-2 py-3 sm:grid-cols-3">
                        <dt class="text-sm font-medium text-slate-500">Semester</dt>
                        <dd class="text-sm text-slate-800 sm:col-span-2">Fall 2025, Collège Montmorency</dd>
                    </div>
                </dl>
            </div>

            <!-- Typical Usage Steps -->
            <div class="mt-6 rounded-lg border border-slate-200 bg-slate-50 p-6 shadow-sm">
                <h2 class="text-xl font-medium text-slate-700">Typical Usage Steps</h2>
                <ol class="mt-4 space-y-4">
                    <li class="rounded-md bg-white p-4 ring-1 ring-slate-200">
                        <p class="text-sm text-slate-800">
                            <strong>Popups:</strong> All of the buttons open a popup apart <strong>about</strong> which has it's own endpoint
                        </p>
                        <p class="mt-1 text-sm text-slate-600">
                            Expected result: Opens a popup in the middle of the screen using <strong>ModalShell.vue</strong>
                        </p>
                    </li>
                    <li class="rounded-md bg-white p-4 ring-1 ring-slate-200">
                        <p class="text-sm text-slate-800">
                            <strong>Autocomplete:</strong> After 2 characters it starts to autocomplete the words by searching inside the database.
                        </p>
                        <p class="mt-1 text-sm text-slate-600">Expected result: A combo box with a list of autocompleted words.</p>
                    </li>
                    <li class="rounded-md bg-white p-4 ring-1 ring-slate-200">
                        <p class="text-sm text-slate-800"><strong>Register:</strong> Open the form via the “Register” button.</p>
                        <p class="mt-1 text-sm text-slate-600">Expected result: redirect to the Login popup with the Login Form.</p>
                    </li>
                    <li class="rounded-md bg-white p-4 ring-1 ring-slate-200">
                        <p class="text-sm text-slate-800"><strong>Login:</strong> Open the form via the “Login” button.</p>
                        <p class="mt-1 text-sm text-slate-600">Expected result: redirect to the main page with the user's words displayed.</p>
                    </li>
                    <li class="rounded-md bg-white p-4 ring-1 ring-slate-200">
                        <p class="text-sm text-slate-800"><strong>Logout:</strong> Use the “Logout” button.</p>
                        <p class="mt-1 text-sm text-slate-600">Expected result: Logs out the user and clears the tokens.</p>
                    </li>
                    <li class="rounded-md bg-white p-4 ring-1 ring-slate-200">
                        <p class="text-sm text-slate-800"><strong>Add Data:</strong> Click “Add” to open the form.</p>
                        <p class="mt-1 text-sm text-slate-600">Expected result: the new item appears in the list.</p>
                    </li>
                    <li class="rounded-md bg-white p-4 ring-1 ring-slate-200">
                        <p class="text-sm text-slate-800"><strong>Edit:</strong> Click “Edit” to update the fields and image, then save.</p>
                        <p class="mt-1 text-sm text-slate-600">Expected result: updated values are displayed immediately.</p>
                    </li>
                    <li class="rounded-md bg-white p-4 ring-1 ring-slate-200">
                        <p class="text-sm text-slate-800"><strong>Detailed View:</strong> Click on the word card to see a detailed view.</p>
                        <p class="mt-1 text-sm text-slate-600">Expected result: A popup of the detailed view appears in the middle of the screen.</p>
                    </li>
                </ol>
                <p class="mt-4 text-sm text-slate-500">If an action fails: a contextual error message is displayed.</p>
            </div>

            <div class="mt-8 rounded-2xl border border-slate-200 bg-white/80 p-6 shadow-sm">
                <div class="flex items-center justify-between gap-3">
                    <h2 class="text-xl font-medium text-slate-700">System Schemas</h2>
                </div>

                <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2">
                    <figure
                        class="group relative overflow-hidden rounded-xl border border-slate-200 bg-gradient-to-br from-slate-50 via-white to-slate-100 shadow transition hover:-translate-y-1 hover:shadow-lg"
                        role="button"
                        tabindex="0"
                        @click="openSchemaPreview('schema1.png', 'User and Word')"
                        @keydown.enter.prevent="openSchemaPreview('schema1.png', 'User and Word')"
                    >
                        <div class="absolute inset-0 bg-sky-500/5 opacity-0 transition group-hover:opacity-100" />
                        <div class="flex items-center justify-between px-4 pt-4">
                            <div>
                                <p class="text-xs font-semibold tracking-wide text-slate-500 uppercase">User and word</p>
                            </div>
                            <span class="rounded-full bg-sky-100 px-3 py-1 text-[11px] font-semibold text-sky-700">v1.0</span>
                        </div>
                        <img
                            src="schema1.png"
                            alt="Schema showing overall app flow"
                            class="mt-3 h-72 w-full object-contain px-4 pb-5 transition group-hover:scale-[1.01]"
                        />
                    </figure>

                    <figure
                        class="group relative overflow-hidden rounded-xl border border-slate-200 bg-gradient-to-br from-slate-50 via-white to-slate-100 shadow transition hover:-translate-y-1 hover:shadow-lg"
                        role="button"
                        tabindex="0"
                        @click="openSchemaPreview('schema2.png', 'Cache')"
                        @keydown.enter.prevent="openSchemaPreview('schema2.png', 'Cache')"
                    >
                        <div class="absolute inset-0 bg-emerald-500/5 opacity-0 transition group-hover:opacity-100" />
                        <div class="flex items-center justify-between px-4 pt-4">
                            <div>
                                <p class="text-xs font-semibold tracking-wide text-slate-500 uppercase">Cache</p>
                            </div>
                            <span class="rounded-full bg-emerald-100 px-3 py-1 text-[11px] font-semibold text-emerald-700">v1.0</span>
                        </div>
                        <img
                            src="schema2.png"
                            alt="Schema showing data model and API connections"
                            class="mt-3 h-72 w-full object-contain px-4 pb-5 transition group-hover:scale-[1.01]"
                        />
                    </figure>
                </div>
            </div>
        </section>
    </main>

    <div
        v-if="toastMessage"
        class="fixed bottom-5 left-1/2 z-50 -translate-x-1/2 rounded-full bg-slate-900/90 px-6 py-3 text-sm font-semibold text-white shadow-xl"
    >
        {{ toastMessage }}
    </div>

    <ModalShell v-if="schemaPreview" :title="schemaPreview.title" @close="closeSchemaPreview">
        <div class="flex flex-col gap-3">
            <div class="max-h-[70vh] overflow-auto rounded-2xl border border-slate-200 bg-slate-50 p-3">
                <img :src="schemaPreview.src" :alt="`${schemaPreview.title} `" class="mx-auto h-full max-h-[65vh] w-full max-w-5xl object-contain" />
            </div>
        </div>
    </ModalShell>

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
