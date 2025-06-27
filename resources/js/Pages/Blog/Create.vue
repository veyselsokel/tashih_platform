<script setup>
import { ref, computed, onMounted, onUnmounted, nextTick } from 'vue';
import { useForm, usePage, Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import FormattingToolbar from './FormattingToolbar.vue';
import AdvancedFormattingToolbar from './AdvancedFormattingToolbar.vue';
import ImageUploader from './ImageUploader.vue';
import TagManager from './TagManager.vue';
import SeoPanel from './SeoPanel.vue';
import MarkdownPreview from './MarkdownPreview.vue';
import { defaultFormatting } from '@/constants/formatting';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
// import MarkdownIt from 'markdown-it'; // Not used in current script, can be removed if not needed by MarkdownPreview

// const md = new MarkdownIt(); // Not used in current script

const props = defineProps({
    categories: Array, // Should be passed from BlogController@create
    user: Object,      // Should be passed from BlogController@create
    can: Object,       // Should be passed from BlogController@create
    errors: Object,    // Inertia automatically provides errors
});

// Form State
const form = useForm({
    title: '',
    content: '',
    formatting: { ...defaultFormatting }, // Initialize with defaults
    featured_image: null,
    meta_description: '',
    tags: [],
    category_ids: [],
    scheduled_at: '',
    status: 'draft',
    gallery: []
});

// Refs
const autoSaveInterval = ref(null);
const lastSaved = ref(null);
const showMarkdownPreview = ref(false);
const contentEditorRef = ref(null);

// Content Stats
const contentStats = computed(() => {
    if (!form.content) return { wordCount: 0, charCount: 0, readingTime: 0, paragraphs: 0 };
    const words = form.content.trim().split(/\s+/).filter(Boolean);
    const wordCount = words.length;
    const charCount = form.content.length;
    const readingTime = Math.ceil(wordCount / 200);
    const paragraphs = form.content.split(/\n\n+/).filter(Boolean).length || (form.content ? 1 : 0);

    return { wordCount, charCount, readingTime, paragraphs };
});

// Methods
const handleFormatText = (formatter) => {
    if (!contentEditorRef.value) return;
    const textarea = contentEditorRef.value;
    const start = textarea.selectionStart;
    const end = textarea.selectionEnd;
    const selectedText = form.content.substring(start, end);

    const formattedText = formatter(selectedText, form.content, start, end);

    form.content = form.content.substring(0, start) + formattedText + form.content.substring(end + (formattedText.length - selectedText.length));
    nextTick(() => {
        textarea.focus();
        textarea.setSelectionRange(start, start + formattedText.length);
    });
};

// Otomatik Kaydetmeyi Başlat
const startAutoSave = () => {
    autoSaveInterval.value = setInterval(() => {
        if (form.isDirty && form.title) {
            saveDraft();
        }
    }, 60000); // Her 60 saniyede bir
};

// Taslak Olarak Kaydet
const saveDraft = async () => {
    if (!form.title) return;

    let submissionScheduledAt = null;
    if (form.scheduled_at) {
        submissionScheduledAt = new Date(form.scheduled_at).toISOString().slice(0, 19).replace('T', ' ');
    }

    // Determine the correct route for saving drafts.
    // The original Create.vue used route('blog.draft')
    // The admin setup uses route('admin.blog.store')
    // For /blog/create/new, the route name is 'blog.store' (from BlogController)
    // Let's assume 'blog.store' is the target for now, and it handles drafts.
    // Or, if you have a specific draft route for non-admin, use that.
    // For this example, I'll keep it generic and assume the store route handles it.

    form.transform(data => {
        const { gallery, ...restOfData } = data;
        return {
            ...restOfData,
            status: 'draft', // Explicitly set as draft
            scheduled_at: submissionScheduledAt,
            gallery_images: gallery.map(item => item.file).filter(file => file instanceof File),
            gallery_captions: gallery.map(item => item.caption || ''),
            gallery_alts: gallery.map(item => item.alt_text || ''),
        };
    }).post(route('blog.store'), { // Adjusted to blog.store, assuming it handles drafts
        preserveScroll: true,
        preserveState: true, // Keep component state on success for drafts
        onSuccess: (page) => {
            lastSaved.value = new Date().toLocaleTimeString();
            // If the backend returns the created/updated post ID, you might want to update form.id
            // For now, we just indicate it's saved.
            // form.reset('content', 'title'); // Or just clear dirty state
            if (page.props.flash?.success) {
                // console.log(page.props.flash.success);
            }
            // To prevent "unsaved changes" message if form is no longer dirty:
            if (form.wasSuccessful) {
                form.recentlySuccessful = false; // Manually reset if needed, or rely on Inertia's dirty state
            }
        },
        onError: (errors) => {
            console.error('Taslak kaydetme hatası:', errors);
        }
    });
};


const submit = () => {
    if (form.processing) return;

    let submissionScheduledAt = null;
    if (form.scheduled_at) {
        try {
            submissionScheduledAt = new Date(form.scheduled_at).toISOString().slice(0, 19).replace('T', ' ');
        } catch (e) {
            console.error("Geçersiz tarih formatı:", form.scheduled_at);
            form.setError('scheduled_at', 'Geçerli bir tarih ve saat giriniz.');
            return;
        }
    }
    if (form.status === 'published' && submissionScheduledAt && new Date(submissionScheduledAt) < new Date()) {
        submissionScheduledAt = null;
    }

    form.transform(data => {
        const { gallery, ...restOfData } = data;
        return {
            ...restOfData,
            scheduled_at: submissionScheduledAt,
            gallery_images: gallery.map(item => item.file).filter(file => file instanceof File),
            gallery_captions: gallery.map(item => item.caption || ''),
            gallery_alts: gallery.map(item => item.alt_text || ''),
        };
    }).post(route('blog.store'), { // Using 'blog.store' from BlogController for /blog/create/new
        preserveScroll: true,
        onSuccess: () => {
            // BlogPostController@store (or BlogController@store) handles redirection.
        },
        onError: (errors) => {
            console.error('Form gönderme hatası:', errors);
        }
    });
};

// Lifecycle
onMounted(() => {
    startAutoSave();
});

onUnmounted(() => {
    if (autoSaveInterval.value) {
        clearInterval(autoSaveInterval.value);
    }
});

const submitButtonText = computed(() => {
    if (form.status === 'published') {
        return form.scheduled_at ? 'Zamanla' : 'Yayınla';
    }
    return 'Taslak Olarak Kaydet';
});

</script>

<template>

    <Head title="Yeni Blog Yazısı" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Yeni Blog Yazısı Oluştur
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-4xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6 md:p-8">
                        <form @submit.prevent="submit" class="space-y-6">
                            <div>
                                <InputLabel for="title" value="Başlık" />
                                <TextInput id="title" v-model="form.title" type="text" class="mt-1 block w-full"
                                    required />
                                <InputError class="mt-2" :message="form.errors.title" />
                            </div>

                            <FormattingToolbar
                                v-model="form.formatting"
                                :content="form.content"
                                :onFormatText="handleFormatText"
                                @update:content="form.content = $event"
                            />

                            <div class="relative">
                                <div class="mb-2 flex items-center justify-between">
                                    <InputLabel for="content-editor" value="İçerik" />
                                    <button type="button" @click="showMarkdownPreview = !showMarkdownPreview"
                                        class="text-sm text-orange-500 hover:text-orange-600">
                                        {{ showMarkdownPreview ? 'Düzenleme Modu' : 'Markdown Önizleme' }}
                                    </button>
                                </div>

                                <div v-if="!showMarkdownPreview">
                                    <textarea id="content-editor" ref="contentEditorRef" v-model="form.content"
                                        rows="15"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 whitespace-pre-wrap"
                                        :style="{
                                            fontFamily: form.formatting?.font, /* Added optional chaining */
                                            fontSize: form.formatting?.fontSize, /* Added optional chaining */
                                            textAlign: form.formatting?.textAlign, /* Added optional chaining */
                                            color: form.formatting?.color, /* Added optional chaining */
                                            lineHeight: form.formatting?.lineHeight /* Added optional chaining */
                                        }" required></textarea>
                                </div>
                                <MarkdownPreview v-else :content="form.content" :formatting="form.formatting" />
                                <InputError class="mt-2" :message="form.errors.content" />

                                <div class="mt-2 flex flex-wrap gap-x-4 gap-y-1 text-sm text-gray-600">
                                    <span>{{ contentStats.wordCount }} kelime</span>
                                    <span>{{ contentStats.readingTime }} dk okuma</span>
                                    <span>{{ contentStats.paragraphs }} paragraf</span>
                                    <span>{{ contentStats.charCount }} karakter</span>
                                </div>
                            </div>

                            <div>
                                <InputLabel for="categories" value="Kategoriler" />
                                <div v-if="categories && categories.length > 0"
                                    class="mt-1 grid grid-cols-2 gap-4 sm:grid-cols-3 md:grid-cols-4">
                                    <div v-for="category in categories" :key="category.id" class="flex items-center">
                                        <input :id="'category-' + category.id" type="checkbox" :value="category.id"
                                            v-model="form.category_ids"
                                            class="h-4 w-4 rounded border-gray-300 text-orange-500 focus:ring-orange-500">
                                        <label :for="'category-' + category.id" class="ml-2 text-sm text-gray-700">{{
                                            category.name }}</label>
                                    </div>
                                </div>
                                <p v-else class="mt-1 text-sm text-gray-500">Yüklenecek kategori bulunamadı.</p>
                                <InputError class="mt-2" :message="form.errors.category_ids" />
                            </div>

                            <ImageUploader v-model:featured-image="form.featured_image"
                                v-model:gallery="form.gallery" />
                            <InputError class="mt-2" :message="form.errors.featured_image" />
                            <InputError class="mt-2" :message="form.errors.gallery_images" />

                            <TagManager v-model="form.tags" />
                            <InputError class="mt-2" :message="form.errors.tags" />

                            <SeoPanel v-model:meta-description="form.meta_description" :title="form.title"
                                :content="form.content" :tags="form.tags" :has-featured-image="!!form.featured_image" />
                            <InputError class="mt-2" :message="form.errors.meta_description" />

                            <div class="space-y-4 rounded-md border border-gray-200 p-4">
                                <h3 class="text-lg font-medium leading-6 text-gray-900">Yayınlama Seçenekleri</h3>
                                <fieldset class="mt-2">
                                    <legend class="sr-only">Yayın durumu</legend>
                                    <div class="space-y-2 sm:flex sm:items-center sm:space-x-10 sm:space-y-0">
                                        <div class="flex items-center">
                                            <input id="status_draft" value="draft" type="radio" v-model="form.status"
                                                class="h-4 w-4 border-gray-300 text-orange-500 focus:ring-orange-500">
                                            <label for="status_draft"
                                                class="ml-3 block text-sm font-medium text-gray-700">Taslak</label>
                                        </div>
                                        <div class="flex items-center">
                                            <input id="status_published" value="published" type="radio"
                                                v-model="form.status"
                                                class="h-4 w-4 border-gray-300 text-orange-500 focus:ring-orange-500">
                                            <label for="status_published"
                                                class="ml-3 block text-sm font-medium text-gray-700">Yayınla /
                                                Zamanla</label>
                                        </div>
                                    </div>
                                    <InputError class="mt-2" :message="form.errors.status" />
                                </fieldset>

                                <div v-if="form.status === 'published'">
                                    <InputLabel for="scheduled_at" value="Yayınlanma Tarihi (İsteğe Bağlı)" />
                                    <TextInput id="scheduled_at" type="datetime-local"
                                        class="mt-1 block w-full sm:w-auto" v-model="form.scheduled_at" />
                                    <p class="mt-1 text-sm text-gray-500">Boş bırakırsanız hemen yayınlanır. İleri bir
                                        tarih
                                        seçerseniz o tarihte yayınlanır.</p>
                                    <InputError class="mt-2" :message="form.errors.scheduled_at" />
                                </div>
                            </div>

                            <div v-if="lastSaved" class="text-sm text-gray-500">
                                Son otomatik taslak kayıt: {{ lastSaved }}
                            </div>
                            <div v-if="form.isDirty && form.title" class="text-sm text-orange-600">
                                Kaydedilmemiş değişiklikler var.
                            </div>

                            <div class="flex items-center justify-end space-x-4 border-t border-gray-200 pt-5">
                                <button type="button"
                                    class="rounded-md bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50"
                                    @click="$inertia.visit(route('admin.blog.index'))"> İptal
                                </button>
                                <PrimaryButton :disabled="form.processing || !form.title"
                                    :class="{ 'opacity-25': form.processing || !form.title }">
                                    {{ form.processing ? 'Kaydediliyor...' : submitButtonText }}
                                </PrimaryButton>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
