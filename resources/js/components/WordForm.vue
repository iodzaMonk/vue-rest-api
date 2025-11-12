<script setup lang="ts">
import CaptchaField from '@/components/CaptchaField.vue';
import type { Word } from '@/types/word';
import { ensureCsrfCookie } from '@/utils/csrf';
import axios from 'axios';
import { computed, reactive, ref, watch } from 'vue';

const props = withDefaults(
    defineProps<{
        mode?: 'create' | 'edit';
        initialWord?: Word | null;
    }>(),
    {
        mode: 'create',
        initialWord: null,
    },
);

const emit = defineEmits<{
    (e: 'word-created', word: Word): void;
    (e: 'word-updated', word: Word): void;
    (e: 'cancel'): void;
}>();

type WordFormFields = {
    word: string;
    pronunciation: string;
    part_of_speech: string;
    meaning: string;
    example_sentence: string;
    difficulty: 'easy' | 'medium' | 'hard';
};

const defaultFields = (): WordFormFields => ({
    word: '',
    pronunciation: '',
    part_of_speech: '',
    meaning: '',
    example_sentence: '',
    difficulty: 'easy',
});

const fields = reactive<WordFormFields>(defaultFields());
const isSubmitting = ref(false);
const submitError = ref('');
const submitSuccess = ref('');
const imageFile = ref<File | null>(null);
const imagePreview = ref<string | null>(null);
const isDragging = ref(false);
const shouldRemoveExistingImage = ref(false);
const captchaToken = ref('');
const captchaAnswer = ref('');
const captchaRef = ref<InstanceType<typeof CaptchaField> | null>(null);

const title = computed(() => (props.mode === 'edit' ? 'Edit word' : 'Add a new word'));
const subtitle = computed(() =>
    props.mode === 'edit' ? 'Update the details below and save your changes.' : 'Fill out the details below to add a vocabulary entry.',
);
const submitLabel = computed(() => (props.mode === 'edit' ? 'Update word' : 'Create word'));

const storageUrl = (path?: string | null) => {
    if (!path) {
        return null;
    }

    if (path.startsWith('http://') || path.startsWith('https://')) {
        return path;
    }

    return `/${path.replace(/^\/+/, '')}`;
};

const revokePreview = () => {
    if (imagePreview.value && imagePreview.value.startsWith('blob:')) {
        URL.revokeObjectURL(imagePreview.value);
    }
};

const resetForm = () => {
    Object.assign(fields, defaultFields());
    imageFile.value = null;
    revokePreview();
    imagePreview.value = null;
    shouldRemoveExistingImage.value = false;
    if (props.mode === 'create') {
        captchaAnswer.value = '';
        captchaToken.value = '';
        captchaRef.value?.refresh?.();
    }
};

const applyWordToFields = (word: Word | null) => {
    if (!word) {
        Object.assign(fields, defaultFields());
        imageFile.value = null;
        revokePreview();
        imagePreview.value = null;
        return;
    }

    fields.word = word.word;
    fields.pronunciation = word.pronunciation;
    fields.part_of_speech = word.part_of_speech;
    fields.meaning = word.meaning;
    fields.example_sentence = word.example_sentence;
    fields.difficulty = word.difficulty;
    imageFile.value = null;
    revokePreview();
    imagePreview.value = storageUrl(word.image);
    shouldRemoveExistingImage.value = false;
    if (props.mode === 'create') {
        captchaAnswer.value = '';
        captchaToken.value = '';
    }
};

watch(
    () => props.initialWord,
    (word) => {
        if (props.mode === 'edit') {
            applyWordToFields(word);
        } else {
            resetForm();
        }
    },
    { immediate: true },
);

const requireToken = (): string | null => {
    const token = window.localStorage.getItem('auth_token');
    if (!token) {
        submitError.value = 'You must be logged in to perform this action.';
        isSubmitting.value = false;
        return null;
    }
    return token;
};

const selectFile = (file: File | undefined) => {
    if (!file) {
        return;
    }

    imageFile.value = file;
    revokePreview();
    imagePreview.value = URL.createObjectURL(file);
    shouldRemoveExistingImage.value = false;
};

const handleImageChange = (event: Event) => {
    const target = event.target as HTMLInputElement;
    const file = target.files?.[0];
    if (!file) {
        if (props.mode === 'edit' && props.initialWord) {
            imagePreview.value = storageUrl(props.initialWord.image);
            shouldRemoveExistingImage.value = false;
        } else {
            imagePreview.value = null;
        }
        imageFile.value = null;
        return;
    }

    selectFile(file);
};

const clearImage = () => {
    imageFile.value = null;
    revokePreview();
    imagePreview.value = null;
    if (props.mode === 'edit' && props.initialWord?.image) {
        shouldRemoveExistingImage.value = true;
    } else {
        shouldRemoveExistingImage.value = false;
    }
};

const onDragOver = (event: DragEvent) => {
    event.preventDefault();
    isDragging.value = true;
};

const onDragLeave = (event: DragEvent) => {
    event.preventDefault();
    isDragging.value = false;
};

const onDrop = (event: DragEvent) => {
    event.preventDefault();
    isDragging.value = false;
    const file = event.dataTransfer?.files?.[0];
    if (file) {
        selectFile(file);
    }
};

const buildFormData = () => {
    const formData = new FormData();
    Object.entries(fields).forEach(([key, value]) => {
        formData.append(key, value);
    });

    if (imageFile.value) {
        formData.append('image', imageFile.value);
    }

    if (shouldRemoveExistingImage.value) {
        formData.append('remove_image', '1');
    }

    if (props.mode === 'create') {
        formData.append('captcha_token', captchaToken.value);
        formData.append('captcha_answer', captchaAnswer.value);
    }

    return formData;
};

const handleSubmit = async () => {
    submitError.value = '';
    submitSuccess.value = '';
    isSubmitting.value = true;

    const token = requireToken();
    if (!token) {
        return;
    }

    try {
        await ensureCsrfCookie();
        const formData = buildFormData();
        const config = {
            headers: {
                Authorization: `Bearer ${token}`,
            },
        };

        if (props.mode === 'edit' && props.initialWord) {
            formData.append('_method', 'PUT');
            const { data } = await axios.post(`/api/words/update/${props.initialWord.id}`, formData, config);
            const updated: Word | undefined = data?.data;
            if (updated) {
                emit('word-updated', updated);
                submitSuccess.value = 'Word updated successfully.';
                imagePreview.value = storageUrl(updated.image);
            }
        } else {
            const { data } = await axios.post('/api/words', formData, config);
            const created: Word | undefined = data?.data;
            if (created) {
                emit('word-created', created);
                submitSuccess.value = 'Word created successfully.';
                resetForm();
            }
        }
    } catch (error: any) {
        submitError.value = error?.response?.data?.message ?? 'Unable to save word. Please try again.';
    } finally {
        isSubmitting.value = false;
        if (props.mode === 'create') {
            captchaRef.value?.refresh?.();
        }
    }
};
</script>

<template>
    <section class="rounded-[2rem] border border-slate-200 bg-white p-8 shadow-sm">
        <header class="mb-6 text-center">
            <h2 class="text-2xl font-semibold text-slate-900">{{ title }}</h2>
            <p class="text-sm text-slate-500">
                {{ subtitle }}
            </p>
        </header>

        <form class="grid gap-6" @submit.prevent="handleSubmit">
            <div class="grid gap-4 md:grid-cols-2">
                <label class="flex flex-col gap-2">
                    <span class="text-xs font-semibold tracking-wide text-slate-500 uppercase">Word</span>
                    <input
                        v-model="fields.word"
                        type="text"
                        class="rounded-2xl border border-slate-200 px-4 py-3 text-base text-slate-900 focus:border-sky-400 focus:outline-none"
                        placeholder="serendipity"
                    />
                </label>
                <label class="flex flex-col gap-2">
                    <span class="text-xs font-semibold tracking-wide text-slate-500 uppercase">Pronunciation</span>
                    <input
                        v-model="fields.pronunciation"
                        type="text"
                        class="rounded-2xl border border-slate-200 px-4 py-3 text-base text-slate-900 focus:border-sky-400 focus:outline-none"
                        placeholder="seh·ruhn·di·puh·tee"
                    />
                </label>
                <label class="flex flex-col gap-2">
                    <span class="text-xs font-semibold tracking-wide text-slate-500 uppercase">Part of speech</span>
                    <input
                        v-model="fields.part_of_speech"
                        type="text"
                        class="rounded-2xl border border-slate-200 px-4 py-3 text-base text-slate-900 focus:border-sky-400 focus:outline-none"
                        placeholder="noun"
                    />
                </label>
                <label class="flex flex-col gap-2">
                    <span class="text-xs font-semibold tracking-wide text-slate-500 uppercase">Difficulty</span>
                    <select
                        v-model="fields.difficulty"
                        class="rounded-2xl border border-slate-200 px-4 py-3 text-base text-slate-900 focus:border-sky-400 focus:outline-none"
                    >
                        <option value="easy">Easy</option>
                        <option value="medium">Medium</option>
                        <option value="hard">Hard</option>
                    </select>
                </label>
                <div class="flex flex-col gap-2 md:col-span-2">
                    <span class="text-xs font-semibold tracking-wide text-slate-500 uppercase">Image</span>
                    <label
                        class="flex cursor-pointer flex-col items-center gap-2 rounded-2xl border-2 border-dashed px-6 py-10 text-center text-sm transition"
                        :class="[isDragging ? 'border-sky-400 bg-sky-50 text-sky-700' : 'border-slate-300 bg-slate-50 text-slate-500']"
                        @dragover="onDragOver"
                        @dragleave="onDragLeave"
                        @drop="onDrop"
                    >
                        <input type="file" accept="image/*" class="hidden" @change="handleImageChange" />
                        <span class="text-base font-semibold text-slate-800">Drop an image here or click to browse</span>
                        <span class="text-xs text-slate-500">PNG, JPG, GIF up to 5MB</span>
                    </label>
                    <div v-if="imagePreview" class="relative mt-3 overflow-hidden rounded-2xl border border-slate-200 bg-slate-50">
                        <img :src="imagePreview" alt="Word image preview" class="h-48 w-full object-cover" />
                        <button
                            type="button"
                            class="absolute top-4 right-4 rounded-full bg-white/80 px-3 py-1 text-xs font-semibold text-slate-700 uppercase shadow"
                            @click="clearImage"
                        >
                            Remove
                        </button>
                    </div>
                </div>
                <CaptchaField
                    v-if="mode === 'create'"
                    ref="captchaRef"
                    v-model:token="captchaToken"
                    v-model:answer="captchaAnswer"
                    class="md:col-span-2"
                />
            </div>

            <label class="flex flex-col gap-2">
                <span class="text-xs font-semibold tracking-wide text-slate-500 uppercase">Meaning</span>
                <textarea
                    v-model="fields.meaning"
                    rows="3"
                    class="rounded-2xl border border-slate-200 px-4 py-3 text-base text-slate-900 focus:border-sky-400 focus:outline-none"
                    placeholder="An unexpected pleasant discovery."
                />
            </label>

            <label class="flex flex-col gap-2">
                <span class="text-xs font-semibold tracking-wide text-slate-500 uppercase">Example sentence</span>
                <textarea
                    v-model="fields.example_sentence"
                    rows="3"
                    class="rounded-2xl border border-slate-200 px-4 py-3 text-base text-slate-900 focus:border-sky-400 focus:outline-none"
                    placeholder="Discovering her favorite author lived nearby was pure serendipity."
                />
            </label>

            <p v-if="submitError" class="text-sm text-red-600">{{ submitError }}</p>
            <p v-if="submitSuccess" class="text-sm text-green-600">{{ submitSuccess }}</p>

            <div class="flex items-center justify-end gap-3">
                <button
                    v-if="mode === 'edit'"
                    type="button"
                    class="rounded-full border border-slate-200 px-6 py-3 text-sm font-semibold tracking-wide text-slate-600 uppercase transition hover:bg-slate-100 focus-visible:ring-2 focus-visible:ring-slate-200 focus-visible:outline-none"
                    @click="emit('cancel')"
                >
                    Cancel
                </button>
                <button
                    type="submit"
                    :disabled="isSubmitting"
                    class="rounded-full bg-sky-500 px-6 py-3 text-sm font-semibold tracking-wide text-white uppercase transition hover:bg-sky-600 focus-visible:ring-2 focus-visible:ring-sky-300 focus-visible:outline-none disabled:cursor-not-allowed disabled:bg-slate-300"
                >
                    {{ isSubmitting ? (mode === 'edit' ? 'Updating...' : 'Saving...') : submitLabel }}
                </button>
            </div>
        </form>
    </section>
</template>
