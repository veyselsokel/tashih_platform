<script setup>
import { useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import FormattingToolbar from './FormattingToolbar.vue';
import ImageUploader from './ImageUploader.vue';
import TagManager from './TagManager.vue';
import SeoPanel from './SeoPanel.vue';
import MarkdownPreview from './MarkdownPreview.vue';
import { ref, computed, onMounted } from 'vue';

const props = defineProps({
    post: {
        type: Object,
        required: true
    },
    title: {
        type: String,
        required: true
    }
});

const form = useForm({
    title: props.post.title,
    content: props.post.content,
    formatting: props.post.formatting || {
        font: 'Arial, sans-serif',
        fontSize: '16px',
        lineHeight: '1.5',
        textAlign: 'left',
        color: '#000000'
    },
    featured_image: null,
    current_featured_image: props.post.featured_image,
    featured_image_url: props.post.featured_image_url,
    meta_description: props.post.meta_description || '',
    tags: props.post.tags || [],
    is_published: props.post.is_published || false,
    gallery: props.post.gallery?.map(item => ({
        id: item.id,
        image: item.image,
        image_url: item.image_url,
        preview: item.image_url,
        caption: item.caption || '',
        alt_text: item.alt_text || '',
        order: item.order
    })) || []
});

const showMarkdownPreview = ref(false);
const imagePreview = ref(props.post.featured_image_url);

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

const handleFormatText = (formatter) => {
    const textarea = document.querySelector('#content-editor');
    const start = textarea.selectionStart;
    const end = textarea.selectionEnd;
    const selectedText = form.content.substring(start, end);
    const formattedText = formatter(selectedText);
    form.content = form.content.substring(0, start) + formattedText + form.content.substring(end);
};

const submit = () => {
    form._method = 'PUT';

    const formData = new FormData();
    formData.append('_method', 'PUT');
    formData.append('title', form.title);
    formData.append('content', form.content);
    formData.append('formatting', JSON.stringify(form.formatting));
    formData.append('meta_description', form.meta_description);
    formData.append('tags', JSON.stringify(form.tags));
    formData.append('is_published', form.is_published ? '1' : '0');

    // Mevcut featured image'i koru
    if (props.post.featured_image) {
        formData.append('current_featured_image', props.post.featured_image);
    }

    // Yeni featured image varsa ekle
    if (form.featured_image instanceof File) {
        formData.append('featured_image', form.featured_image);
    }

    // Mevcut ve yeni galeri görsellerini ekle
    if (form.gallery && form.gallery.length > 0) {
        form.gallery.forEach((item, index) => {
            if (item.id) {
                // Mevcut galeri öğeleri
                formData.append(`gallery[${index}][id]`, item.id);
                formData.append(`gallery[${index}][image]`, item.image);
                formData.append(`gallery[${index}][caption]`, item.caption || '');
                formData.append(`gallery[${index}][alt_text]`, item.alt_text || '');
                formData.append(`gallery[${index}][order]`, index);
            } else if (item.file instanceof File) {
                // Yeni yüklenen görseller
                formData.append(`gallery[${index}][file]`, item.file);
                formData.append(`gallery[${index}][caption]`, item.caption || '');
                formData.append(`gallery[${index}][alt_text]`, item.alt_text || '');
                formData.append(`gallery[${index}][order]`, index);
            }
        });
    }

    form.post(route('blog.update', props.post.slug), {
        data: formData,
        preserveScroll: true,
        forceFormData: true,
        onSuccess: () => {
            // Başarı mesajı
        },
    });
};

onMounted(() => {
    if (props.post.gallery?.length) {
        form.gallery = props.post.gallery.map(item => ({
            ...item,
            preview: item.image_url,
            caption: item.caption || '',
            altText: item.alt_text || ''
        }));
    }
});
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Yazıyı Düzenle
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
                                <div class="flex items-center justify-between mb-2">
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
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 whitespace-pre-wrap"
                                        :style="{
                                            fontFamily: form.formatting.font,
                                            fontSize: form.formatting.fontSize,
                                            textAlign: form.formatting.textAlign,
                                            color: form.formatting.color,
                                            lineHeight: form.formatting.lineHeight
                                        }" required></textarea>
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
                                :preview="form.featured_image_url" :initial-gallery="form.gallery" />

                            <!-- Etiket Yöneticisi -->
                            <TagManager v-model="form.tags" />

                            <!-- SEO Paneli -->
                            <SeoPanel v-model:meta-description="form.meta_description" :title="form.title"
                                :content="form.content" :tags="form.tags"
                                :has-featured-image="!!form.current_featured_image || !!form.featured_image" />

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

                            <!-- Form Butonları -->
                            <div class="flex justify-end space-x-4">
                                <button type="button"
                                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-orange-500"
                                    @click="$inertia.visit(route('dashboard'))">
                                    İptal
                                </button>
                                <button type="submit" :disabled="form.processing"
                                    class="px-4 py-2 text-sm font-medium text-white bg-orange-500 border border-transparent rounded-md hover:bg-orange-600 focus:outline-none focus:ring-2 focus:ring-orange-500">
                                    {{ form.is_published ? 'Güncelle ve Yayınla' : 'Güncelle' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>