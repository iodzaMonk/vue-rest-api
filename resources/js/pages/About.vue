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
      
      <section class="mx-auto max-w-3xl ">
    <h1 class="text-3xl font-semibold tracking-tight text-slate-800 text-center">About this website</h1>

    <!-- General Information -->
    <div class="mt-6 rounded-lg border border-slate-200 bg-slate-50 p-6 shadow-sm">
      <h2 class="text-xl font-medium text-slate-700">General Information</h2>
      <dl class="mt-4 divide-y divide-slate-200">
        <div class="grid grid-cols-1 gap-2 py-3 sm:grid-cols-3">
          <dt class="text-sm font-medium text-slate-500">Names</dt>
          <dd class="sm:col-span-2 text-sm text-slate-800">Vitali Melnik and Nazimzhon Mulubaev</dd>
        </div>
        <div class="grid grid-cols-1 gap-2 py-3 sm:grid-cols-3">
          <dt class="text-sm font-medium text-slate-500">Course</dt>
          <dd class="sm:col-span-2 text-sm text-slate-800">
            4205H6MO — Applications Web transactionnelles
          </dd>
        </div>
        <div class="grid grid-cols-1 gap-2 py-3 sm:grid-cols-3">
          <dt class="text-sm font-medium text-slate-500">Semester</dt>
          <dd class="sm:col-span-2 text-sm text-slate-800">Fall 2025, Collège Montmorency</dd>
        </div>
      </dl>
    </div>

    <!-- Typical Usage Steps -->
    <div class="mt-6 rounded-lg border border-slate-200 bg-slate-50 p-6 shadow-sm">
      <h2 class="text-xl font-medium text-slate-700">Typical Usage Steps</h2>
      <ol class="mt-4 space-y-4">
        <li class="rounded-md bg-white p-4 ring-1 ring-slate-200">
          <p class="text-sm text-slate-800">
            <strong>Register:</strong> Open the form via the “Register” button.
          </p>
          <p class="mt-1 text-sm text-slate-600">
            Expected result: redirect to the Login popup with the Login Form.
          </p>
        </li>
        <li class="rounded-md bg-white p-4 ring-1 ring-slate-200">
          <p class="text-sm text-slate-800">
            <strong>Login:</strong> Open the form via the “Login” button.
          </p>
          <p class="mt-1 text-sm text-slate-600">
            Expected result: redirect to the main page with the user's words displayed.
          </p>
        </li>
        <li class="rounded-md bg-white p-4 ring-1 ring-slate-200">
          <p class="text-sm text-slate-800">
            <strong>Logout:</strong> Use the “Logout” button.
          </p>
          <p class="mt-1 text-sm text-slate-600">
            Expected result: Logs out the user and clears the tokens.
          </p>
        </li>
        <li class="rounded-md bg-white p-4 ring-1 ring-slate-200">
          <p class="text-sm text-slate-800">
            <strong>Add Data:</strong> Click “Add” to open the form.
          </p>
          <p class="mt-1 text-sm text-slate-600">
            Expected result: the new item appears in the list.
          </p>
        </li>
        <li class="rounded-md bg-white p-4 ring-1 ring-slate-200">
          <p class="text-sm text-slate-800">
            <strong>Edit:</strong> Click “Edit” to update the fields and image, then save.
          </p>
          <p class="mt-1 text-sm text-slate-600">
            Expected result: updated values are displayed immediately.
          </p>
        </li>
      </ol>
      <p class="mt-4 text-sm text-slate-500">
        If an action fails: a contextual error message is displayed.
      </p>
    </div>
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
