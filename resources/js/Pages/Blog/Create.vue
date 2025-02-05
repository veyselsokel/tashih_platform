// pages/Blog/Create.vue
<script setup>
import { ref, computed, onMounted, onUnmounted, nextTick } from 'vue';
import { useForm, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import FormattingToolbar from './FormattingToolbar.vue';
import ImageUploader from './ImageUploader.vue';
import TagManager from './TagManager.vue';
import SeoPanel from './SeoPanel.vue';
import MarkdownPreview from './MarkdownPreview.vue';
import { defaultFormatting } from '@/constants/formatting';
import MarkdownIt from 'markdown-it';

const md = new MarkdownIt();

// Form State
const form = useForm({
    title: '',
    content: '',
    formatting: { ...defaultFormatting },
    featured_image: null,
    meta_description: '',
    tags: [],
    is_published: false,
    gallery: []
});

// Refs
const imagePreview = ref(null);
const autoSave = ref(null);
const lastSaved = ref(null);
const showMarkdownPreview = ref(false);

// Content Stats
const contentStats = computed(() => {
    const wordCount = form.content.trim().split(/\s+/).length;
    const charCount = form.content.length;
    const readingTime = Math.ceil(wordCount / 200);
    const paragraphs = form.content.split('\n\n').length;

    return {
        wordCount,
        charCount,
        readingTime,
        paragraphs
    };
});

// Methods
const handleFormatText = (formatter) => {
    const textarea = document.querySelector('#content-editor');
    const start = textarea.selectionStart;
    const end = textarea.selectionEnd;
    const selectedText = form.content.substring(start, end);

    const formattedText = formatter(selectedText);

    form.content = form.content.substring(0, start) + formattedText + form.content.substring(end);
};

const handleEnterKey = (e) => {
    // Eğer Shift+Enter basılırsa 2 satır boşluk bırak (paragraf)
    if (e.shiftKey) {
        e.preventDefault();
        const cursorPosition = e.target.selectionStart;
        const contentBefore = form.content.substring(0, cursorPosition);
        const contentAfter = form.content.substring(e.target.selectionEnd);

        form.content = contentBefore + '\n\n' + contentAfter;
        nextTick(() => {
            e.target.selectionStart = e.target.selectionEnd = cursorPosition + 2;
        });
    } else {
        // Normal enter - sadece tek satır boşluk
        e.preventDefault();
        const cursorPosition = e.target.selectionStart;
        const contentBefore = form.content.substring(0, cursorPosition);
        const contentAfter = form.content.substring(e.target.selectionEnd);

        form.content = contentBefore + '\n' + contentAfter;
        nextTick(() => {
            e.target.selectionStart = e.target.selectionEnd = cursorPosition + 1;
        });
    }
};


const startAutoSave = () => {
    autoSave.value = setInterval(() => {
        if (form.isDirty) {
            saveAsDraft();
        }
    }, 30000);
};

const saveAsDraft = async () => {
    try {
        await form.post(route('blog.draft'), {
            preserveScroll: true,
            onSuccess: () => {
                const page = usePage();
                if (page.props.flash?.success) {
                }
                lastSaved.value = new Date();
            },
            onError: (errors) => {
                console.error('Otomatik kaydetme hatası:', errors);
            }
        });
    } catch (error) {
        console.error('Otomatik kaydetme hatası:', error);
    }
};

const submit = (e) => {
    e.preventDefault();  // Ekstra önlem
    if (form.processing) return;  // İşlem devam ediyorsa çık

    form.post(route('blog.store'), {
        preserveScroll: true,
        onSuccess: () => {
            // Başarılı işlem
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
    if (autoSave.value) {
        clearInterval(autoSave.value);
    }
});

</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Yeni Blog Yazısı
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <form @submit.prevent="submit" class="space-y-6">
                            <!-- Başlık -->
                            <div>
                                <label class="mb-2 block text-sm font-medium text-gray-700">
                                    Başlık
                                </label>
                                <input v-model="form.title" type="text"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500"
                                    required />
                            </div>

                            <!-- Formatlama Araç Çubuğu -->
                            <FormattingToolbar v-model="form.formatting" :onFormatText="handleFormatText"
                                @click.prevent />

                            <!-- İçerik Editörü -->
                            <div class="relative">
                                <div class="flex justify-between items-center mb-2">
                                    <label class="text-sm font-medium text-gray-700">
                                        İçerik
                                    </label>
                                    <button type="button" @click="showMarkdownPreview = !showMarkdownPreview"
                                        class="text-sm text-orange-500 hover:text-orange-600">
                                        {{ showMarkdownPreview ? 'Düzenleme Modu' : 'Önizleme' }}
                                    </button>
                                </div>

                                <div v-if="!showMarkdownPreview">
                                    <textarea id="content-editor" v-model="form.content" rows="10"
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500"
                                        :style="{
                                            fontFamily: form.formatting.font,
                                            fontSize: form.formatting.fontSize,
                                            textAlign: form.formatting.textAlign,
                                            color: form.formatting.color,
                                            lineHeight: form.formatting.lineHeight
                                        }" @keydown.enter="handleEnterKey" required></textarea>
                                </div>
                                <MarkdownPreview v-else :content="form.content" :formatting="form.formatting" />

                                <!-- İçerik İstatistikleri -->
                                <div class="mt-2 flex space-x-4 text-sm text-gray-600">
                                    <span>{{ contentStats.wordCount }} kelime</span>
                                    <span>{{ contentStats.readingTime }} dk okuma süresi</span>
                                    <span>{{ contentStats.paragraphs }} paragraf</span>
                                    <span>{{ contentStats.charCount }} karakter</span>
                                </div>
                            </div>

                            <!-- Görsel Yükleyici -->
                            <ImageUploader v-model:featured-image="form.featured_image" v-model:gallery="form.gallery"
                                :preview="imagePreview" />

                            <!-- Etiket Yöneticisi -->
                            <TagManager v-model="form.tags" />

                            <!-- SEO Paneli -->
                            <SeoPanel v-model:meta-description="form.meta_description" :title="form.title"
                                :content="form.content" :tags="form.tags" :has-featured-image="!!form.featured_image" />

                            <!-- Yayınlama Seçenekleri -->
                            <div class="flex items-center">
                                <input type="checkbox" v-model="form.is_published" id="is_published"
                                    class="h-4 w-4 rounded border-gray-300 text-orange-500 focus:ring-orange-500" />
                                <label for="is_published" class="ml-2 block text-sm text-gray-700">
                                    Yayınla
                                </label>
                                <span class="ml-2 text-sm text-gray-500">
                                    (İşaretlemezseniz taslak olarak kaydedilecektir)
                                </span>
                            </div>

                            <!-- Otomatik Kaydetme Bilgisi -->
                            <div v-if="lastSaved" class="text-sm text-gray-500">
                                Son otomatik kayıt: {{ new Date(lastSaved).toLocaleTimeString() }}
                            </div>

                            <!-- Form Butonları -->
                            <div class="flex justify-end space-x-4">
                                <button type="button"
                                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-orange-500"
                                    @click="$inertia.visit(route('dashboard'))">
                                    İptal
                                </button>
                                <button type="submit" :disabled="form.processing"
                                    class="px-4 py-2 text-sm font-medium text-white bg-orange-500 border border-transparent rounded-md hover:bg-orange-600 focus:outline-none focus:ring-2 focus:ring-orange-500">
                                    {{ form.is_published ? 'Yayınla' : 'Taslak Olarak Kaydet' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>