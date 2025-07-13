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
    formatting: { ...defaultFormatting },
    featured_image: null,
    meta_description: '',
    tags: [],
    category_ids: [],
    scheduled_at: '',
    status: 'draft',
    gallery: []
});

// Refs

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
    
    // Get the selected text exactly as it appears
    const selectedText = form.content.substring(start, end);
    
    // Only proceed if there's actually selected text
    if (start === end) return;

    // Apply the formatting function
    const formattedText = formatter(selectedText, form.content, start, end);

    // Replace the selected text with the formatted version
    const beforeText = form.content.substring(0, start);
    const afterText = form.content.substring(end);
    
    form.content = beforeText + formattedText + afterText;
    
    // Restore focus and selection
    nextTick(() => {
        textarea.focus();
        // Place cursor at the end of the formatted text
        const newCursorPosition = start + formattedText.length;
        textarea.setSelectionRange(newCursorPosition, newCursorPosition);
    });
};

// Manual save draft function





const submit = (publish = false) => {
    if (form.processing) return;

    form.status = publish ? 'published' : 'draft';

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
    }).post(route('blog.store'), { // Using correct blog route
        preserveScroll: true,
        onSuccess: () => {
            // BlogPostController@store handles redirection.
        },
        onError: (errors) => {
            console.error('Form gönderme hatası:', errors);
        }
    });
};

const saveAsDraft = () => {
    submit(false);
};

const publishPost = () => {
    submit(true);
};

// Lifecycle
onMounted(() => {
    // No auto-save initialization
});

onUnmounted(() => {
    // No cleanup needed for auto-save
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
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 whitespace-pre-wrap resize-y"
                                        :style="{
                                            fontFamily: form.formatting?.font || 'Arial',
                                            fontSize: form.formatting?.fontSize || '16px',
                                            textAlign: form.formatting?.textAlign || 'left',
                                            color: form.formatting?.color || '#333333',
                                            lineHeight: form.formatting?.lineHeight || '1.5',
                                            minHeight: '200px'
                                        }" 
                                        placeholder="Blog içeriğinizi buraya yazın... Markdown formatını destekler."
                                        required></textarea>
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
                                <div>
                                    <InputLabel for="scheduled_at" value="Yayınlanma Tarihi (İsteğe Bağlı)" />
                                    <TextInput id="scheduled_at" type="datetime-local"
                                        class="mt-1 block w-full sm:w-auto" v-model="form.scheduled_at" />
                                    <p class="mt-1 text-sm text-gray-500">Boş bırakırsanız hemen yayınlanır. İleri bir
                                        tarih
                                        seçerseniz o tarihte yayınlanır.</p>
                                    <InputError class="mt-2" :message="form.errors.scheduled_at" />
                                </div>
                            </div>

                            <div v-if="form.isDirty && form.title" class="text-sm text-orange-600">
                                Kaydedilmemiş değişiklikler var.
                            </div>

                            <div class="flex items-center justify-end space-x-4 border-t border-gray-200 pt-5">
                                <button type="button"
                                    class="rounded-md bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50"
                                    @click="$inertia.visit(route('blog.index'))"> İptal
                                </button>
                                <PrimaryButton :disabled="form.processing || !form.title"
                                    :class="{ 'opacity-25': form.processing || !form.title }"
                                    @click="saveAsDraft">
                                    {{ form.processing ? 'Kaydediliyor...' : 'Taslak Kaydet' }}
                                </PrimaryButton>
                                <PrimaryButton :disabled="form.processing || !form.title"
                                    :class="{ 'opacity-25': form.processing || !form.title }"
                                    @click="publishPost">
                                    {{ form.processing ? 'Kaydediliyor...' : 'Yayınla' }}
                                </PrimaryButton>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
