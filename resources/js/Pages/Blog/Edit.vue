<script setup>
import { ref, computed, onMounted, nextTick } from 'vue';
import { useForm, Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import FormattingToolbar from './FormattingToolbar.vue';
import AdvancedFormattingToolbar from './AdvancedFormattingToolbar.vue';
import ImageUploader from './ImageUploader.vue';
import TagManager from './TagManager.vue';
import SeoPanel from './SeoPanel.vue';
import MarkdownPreview from './MarkdownPreview.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import Modal from '@/Components/Modal.vue';


const props = defineProps({
    blogPost: Object, // BlogPostController@edit'ten gelen post verisi
    categories: Array, // Tüm kategoriler
    can: Object, // Yetkiler
});

const form = useForm({
    _method: 'PUT',
    title: props.blogPost.title,
    content: props.blogPost.content,
    formatting: props.blogPost.formatting || {
        font: 'Arial',
        fontSize: '16px',
        lineHeight: '1.5',
        textAlign: 'left',
        color: '#333333'
    },
    meta_description: props.blogPost.meta_description || '',
    tags: props.blogPost.tags || [],
    category_ids: props.blogPost.category_ids || [],
    scheduled_at: props.blogPost.scheduled_at ? props.blogPost.scheduled_at.replace(' ', 'T').substring(0, 16) : '',
    status: props.blogPost.status || 'draft',
    featured_image: null,
    remove_featured_image: false,
    gallery: props.blogPost.gallery?.map(item => ({
        id: item.id,
        image_url: item.image_url,
        preview: item.image_url,
        caption: item.caption || '',
        alt_text: item.alt_text || '',
        order: item.order,
        markedForRemoval: false,
    })) || [],
});

const showMarkdownPreview = ref(false);
const contentEditorRef = ref(null);
const confirmingPostDeletion = ref(false);


// İçerik İstatistikleri
const contentStats = computed(() => {
    if (!form.content) return { wordCount: 0, charCount: 0, readingTime: 0, paragraphs: 0 };
    const words = form.content.trim().split(/\s+/).filter(Boolean);
    const wordCount = words.length;
    const charCount = form.content.length;
    const readingTime = Math.ceil(wordCount / 200);
    const paragraphs = form.content.split(/\n\n+/).filter(Boolean).length || (form.content ? 1 : 0);
    return { wordCount, charCount, readingTime, paragraphs };
});

// Metin Formatlama
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

// Formu Gönderme
const submit = (publish = false) => {
    if (form.processing) return;

    form.status = publish ? 'published' : 'draft';

    let submissionScheduledAt = null;
    if (form.scheduled_at) {
        try {
            submissionScheduledAt = new Date(form.scheduled_at).toISOString().slice(0, 19).replace('T', ' ');
        } catch (e) {
            form.setError('scheduled_at', 'Geçerli bir tarih ve saat giriniz.');
            return;
        }
    }

    if (form.status === 'published' && submissionScheduledAt && new Date(submissionScheduledAt) < new Date()) {
        submissionScheduledAt = null;
    }

    // ImageUploader'dan gelen form.gallery dizisini backend'in beklediği formata dönüştür
    form.transform(data => {
        const { gallery, ...restOfData } = data; // Orijinal gallery'yi ayır
        const transformedData = {
            ...restOfData,
            scheduled_at: submissionScheduledAt,
            gallery_images: [], // Yeni yüklenecek dosyalar
            gallery_captions: [], // Yeni dosyaların başlıkları
            gallery_alts: [], // Yeni dosyaların alt metinleri
            remove_gallery_images: [], // Silinecek mevcut görsellerin ID'leri
        };

        gallery.forEach(item => {
            if (item.file instanceof File) { // Yeni yüklenen görsel
                transformedData.gallery_images.push(item.file);
                transformedData.gallery_captions.push(item.caption || '');
                transformedData.gallery_alts.push(item.alt_text || '');
            } else if (item.id && item.markedForRemoval) { // Silinmek üzere işaretlenmiş mevcut görsel
                transformedData.remove_gallery_images.push(item.id);
            }
        });
        return transformedData;
    });

    form.post(route('blog.update', props.blogPost.slug), {
        preserveScroll: true,
        onSuccess: () => {
            // Başarı mesajı controller'dan flash message olarak gelebilir.
            // Formun dirty state'i otomatik sıfırlanır.
        },
        onError: (errors) => {
            console.error('Form güncelleme hatası:', errors);
            // Hata durumunda transform'u geri almak gerekebilir veya transform'u sadece gönderim anında yapmak.
            // Inertia normalde transform'u kalıcı yapmaz, her istek için yeniden çalıştırır.
        }
    });
};

const saveAsDraft = () => {
    submit(false);
};

const publishPost = () => {
    submit(true);
};



const confirmPostDeletion = () => {
    confirmingPostDeletion.value = true;
};

const deletePost = () => {
    form.delete(route('blog.destroy', props.blogPost.slug), {
        preserveScroll: true,
        onSuccess: () => {
            confirmingPostDeletion.value = false;
            // Yönlendirme controller'da yapılıyor.
        },
        onError: () => {
            // Hata mesajı
        },
        onFinish: () => {
            // form.reset(); // Gerekirse
        },
    });
};

</script>

<template>

    <Head :title="'Yazı Düzenle: ' + form.title" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Yazıyı Düzenle: <span class="italic">{{ props.blogPost.title }}</span>
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
                                <div class="mt-1 grid grid-cols-2 gap-4 sm:grid-cols-3 md:grid-cols-4">
                                    <div v-for="category in categories" :key="category.id" class="flex items-center">
                                        <input :id="'category-' + category.id" type="checkbox" :value="category.id"
                                            v-model="form.category_ids"
                                            class="h-4 w-4 rounded border-gray-300 text-orange-500 focus:ring-orange-500">
                                        <label :for="'category-' + category.id" class="ml-2 text-sm text-gray-700">{{
                                            category.name }}</label>
                                    </div>
                                </div>
                                <InputError class="mt-2" :message="form.errors.category_ids" />
                            </div>

                            <ImageUploader v-model:featured-image="form.featured_image" v-model:gallery="form.gallery"
                                :initial-featured-image-url="props.blogPost.featured_image_url"
                                @remove-featured-image="form.remove_featured_image = true" />
                            <InputError class="mt-2" :message="form.errors.featured_image" />
                            <InputError class="mt-2"
                                :message="form.errors.gallery_images || form.errors.remove_gallery_images" />


                            <TagManager v-model="form.tags" />
                            <InputError class="mt-2" :message="form.errors.tags" />

                            <SeoPanel v-model:meta-description="form.meta_description" :title="form.title"
                                :content="form.content" :tags="form.tags"
                                :has-featured-image="!!(props.blogPost.featured_image_url || form.featured_image)" />
                            <InputError class="mt-2" :message="form.errors.meta_description" />

                            
                            <div class="space-y-4 rounded-md border border-gray-200 p-4">
                                <h3 class="text-lg font-medium leading-6 text-gray-900">Yayınlama Seçenekleri</h3>
                                <div>
                                    <InputLabel for="scheduled_at_edit" value="Yayınlanma Tarihi (İsteğe Bağlı)" />
                                    <TextInput id="scheduled_at_edit" type="datetime-local"
                                        class="mt-1 block w-full sm:w-auto" v-model="form.scheduled_at" />
                                    <p class="mt-1 text-sm text-gray-500">Boş bırakırsanız hemen yayınlanır/güncellenir.
                                        İleri
                                        bir tarih seçerseniz o tarihte yayınlanır.</p>
                                    <InputError class="mt-2" :message="form.errors.scheduled_at" />
                                </div>
                            </div>
                            <div v-if="form.isDirty" class="text-sm text-orange-600">
                                Kaydedilmemiş değişiklikler var.
                            </div>


                            <div class="flex items-center justify-between border-t border-gray-200 pt-5">
                                <div>
                                    <DangerButton @click="confirmPostDeletion" v-if="can.delete_blog_post"
                                        type="button">
                                        Yazıyı Sil
                                    </DangerButton>
                                </div>
                                <div class="flex space-x-4">
                                    <Link :href="route('blog.index')"
                                        class="rounded-md bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                                    İptal
                                    </Link>
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
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <Modal :show="confirmingPostDeletion" @close="confirmingPostDeletion = false">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900">
                    Yazıyı Silmek İstediğinizden Emin misiniz?
                </h2>
                <p class="mt-1 text-sm text-gray-600">
                    Bu işlem geri alınamaz. Yazı kalıcı olarak silinecektir.
                </p>
                <div class="mt-6 flex justify-end">
                    <button @click="confirmingPostDeletion = false" type="button"
                        class="rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                        İptal
                    </button>
                    <DangerButton class="ml-3" @click="deletePost" :disabled="form.processing">
                        Evet, Sil
                    </DangerButton>
                </div>
            </div>
        </Modal>

    </AuthenticatedLayout>
</template>
