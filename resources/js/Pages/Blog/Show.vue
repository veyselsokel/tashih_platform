// resources/js/Pages/Blog/Show.vue

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { Calendar, Clock, User, Tag, Share2, ArrowLeft } from 'lucide-vue-next';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { ref, onMounted } from 'vue';
import MarkdownIt from 'markdown-it';
import ImageLightbox from './ImageLightbox.vue';

const showLightbox = ref(false);
const selectedImageIndex = ref(0);

const props = defineProps({
    blogPost: {
        type: Object,
        required: true
    }
});

const openLightbox = (index) => {
    if (props.blogPost.gallery && props.blogPost.gallery.length > 0) {
        selectedImageIndex.value = index;
        showLightbox.value = true;
    }
};

const md = new MarkdownIt({
    html: true,
    breaks: true,
    linkify: true,
    typographer: true,
});

const showShareMenu = ref(false);

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('tr-TR', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
};

const getReadingTime = (content) => {
    const wordsPerMinute = 200;
    const words = content?.split(/\s+/)?.length || 0;
    const minutes = Math.ceil(words / wordsPerMinute);
    return `${minutes} dk okuma`;
};

const formatContent = (content) => {
    if (!content) return '';
    return md.render(content);
};

const sharePost = async (platform) => {
    const url = window.location.href;
    const text = props.blogPost.title;

    switch (platform) {
        case 'twitter':
            window.open(`https://twitter.com/intent/tweet?url=${url}&text=${text}`, '_blank');
            break;
        case 'facebook':
            window.open(`https://www.facebook.com/sharer/sharer.php?u=${url}`, '_blank');
            break;
        case 'linkedin':
            window.open(`https://www.linkedin.com/sharing/share-offsite/?url=${url}`, '_blank');
            break;
        case 'copy':
            try {
                await navigator.clipboard.writeText(url);
            } catch (err) {
                console.error('URL kopyalanamadı:', err);
            }
            break;
    }
    showShareMenu.value = false;
};

const readingProgress = ref(0);
const updateReadingProgress = () => {
    const element = document.documentElement;
    const scrollTop = element.scrollTop || document.body.scrollTop;
    const scrollHeight = element.scrollHeight || document.body.scrollHeight;
    const clientHeight = element.clientHeight;
    const windowHeight = scrollHeight - clientHeight;
    const progress = scrollTop / windowHeight * 100;
    readingProgress.value = Math.min(100, Math.max(0, progress));
};

onMounted(() => {
    window.addEventListener('scroll', updateReadingProgress);
    return () => window.removeEventListener('scroll', updateReadingProgress);
});
</script>

<template>
    <GuestLayout :title="blogPost.title">

        <Head :title="blogPost.title" />

        <div class="fixed top-0 left-0 w-full h-1.5 bg-gray-200 z-50">
            <div class="h-full bg-orange-500 transition-all duration-150" :style="{ width: `${readingProgress}%` }">
            </div>
        </div>

        <article class="bg-stone-50">
            <!-- Hero Section -->
            <header v-if="blogPost.featured_image_url" class="relative w-full h-[50vh] sm:h-[60vh] overflow-hidden">
                <img :src="blogPost.featured_image_url" :alt="blogPost.title" class="w-full h-full object-cover" />
                <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-black/20 to-transparent"></div>
                <div class="absolute bottom-0 left-0 right-0 p-8 text-white">
                    <div class="max-w-4xl mx-auto">
                        <Link href="/blog"
                            class="inline-flex items-center text-white hover:text-orange-300 transition-colors mb-4">
                        <ArrowLeft class="w-5 h-5 mr-2" />
                        Tüm Yazılara Geri Dön
                        </Link>
                        <h1 class="text-4xl sm:text-5xl font-bold mb-4">{{ blogPost.title }}</h1>
                        <div class="flex flex-wrap items-center gap-x-6 gap-y-2 text-gray-200">
                            <div class="flex items-center">
                                <Calendar class="w-5 h-5 mr-2" />{{ formatDate(blogPost.published_at ||
                                    blogPost.created_at) }}
                            </div>
                            <div class="flex items-center">
                                <User class="w-5 h-5 mr-2" />{{ blogPost.user?.name }}
                            </div>
                            <div class="flex items-center">
                                <Clock class="w-5 h-5 mr-2" />{{ getReadingTime(blogPost.content) }}
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Main Content -->
            <div class="max-w-4xl mx-auto px-4 py-28">
                <header v-if="!blogPost.featured_image_url" class="mb-12">
                    <Link href="/blog"
                        class="inline-flex items-center text-gray-600 hover:text-orange-600 transition-colors mb-8">
                    <ArrowLeft class="w-5 h-5 mr-2" />
                    Tüm Yazılara Geri Dön
                    </Link>
                    <h1 class="text-4xl sm:text-5xl font-bold text-gray-900 mb-6">{{ blogPost.title }}</h1>
                    <div class="flex flex-wrap items-center gap-6 text-gray-600">
                        <div class="flex items-center">
                            <Calendar class="w-5 h-5 mr-2" />{{ formatDate(blogPost.published_at || blogPost.created_at)
                            }}
                        </div>
                        <div class="flex items-center">
                            <User class="w-5 h-5 mr-2" />{{ blogPost.user?.name }}
                        </div>
                        <div class="flex items-center">
                            <Clock class="w-5 h-5 mr-2" />{{ getReadingTime(blogPost.content) }}
                        </div>
                    </div>
                </header>

                <!-- Meta Description -->
                <p v-if="blogPost.meta_description"
                    class="text-xl text-gray-600 italic border-l-4 border-orange-400 pl-6 mb-12">
                    {{ blogPost.meta_description }}
                </p>

                <!-- Article Content -->
                <div class="prose prose-lg max-w-none blog-content" :style="{
                    fontFamily: blogPost.formatting?.font,
                    textAlign: blogPost.formatting?.textAlign,
                    color: blogPost.formatting?.color,
                    lineHeight: blogPost.formatting?.lineHeight
                }" v-html="formatContent(blogPost.content)"></div>

                <!-- Tags -->
                <div v-if="blogPost.tags?.length" class="mt-12 flex flex-wrap gap-3">
                    <span v-for="tag in blogPost.tags" :key="tag"
                        class="inline-flex items-center px-4 py-1.5 bg-orange-100 text-orange-800 rounded-full text-sm font-medium">
                        <Tag class="w-4 h-4 mr-1.5" />
                        {{ tag }}
                    </span>
                </div>

                <!-- Gallery -->
                <div v-if="blogPost.gallery?.length" class="mt-16">
                    <h3 class="text-3xl font-bold text-gray-800 mb-8">Galeri</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <div v-for="(image, index) in blogPost.gallery" :key="image.id"
                            class="group relative overflow-hidden rounded-xl shadow-lg cursor-pointer hover:shadow-2xl transition-all"
                            @click="openLightbox(index)">
                            <div class="aspect-w-16 aspect-h-9">
                                <img :src="image.image_url" :alt="image.alt_text"
                                    class="w-full h-full object-cover transition duration-300 group-hover:scale-105" />
                            </div>
                            <div
                                class="absolute inset-0 bg-gradient-to-t from-black/70 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-end p-4">
                                <p class="text-white text-sm">{{ image.caption }}</p>
                            </div>
                        </div>
                    </div>
                    <ImageLightbox :images="blogPost.gallery" :is-open="showLightbox"
                        :initial-index="selectedImageIndex" @close="showLightbox = false" />
                </div>

                <!-- Share Section -->
                <div class="relative mt-16 pt-8 border-t border-gray-200 flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <div class="text-sm">
                            <p class="text-gray-500">Yazan</p>
                            <p class="font-medium text-gray-900">{{ blogPost.user?.name }}</p>
                        </div>
                    </div>
                    <div class="relative">
                        <button @click="showShareMenu = !showShareMenu"
                            class="flex items-center space-x-2 text-gray-600 hover:text-orange-600 transition-colors p-2 rounded-full hover:bg-gray-100">
                            <Share2 class="w-5 h-5" />
                            <span>Paylaş</span>
                        </button>
                        <div v-if="showShareMenu"
                            class="absolute right-0 bottom-full mb-2 w-48 bg-white rounded-lg shadow-lg border border-gray-100 py-2 z-10">
                            <button @click="sharePost('twitter')"
                                class="w-full px-4 py-2 text-left hover:bg-gray-50 text-gray-700 flex items-center">Twitter</button>
                            <button @click="sharePost('facebook')"
                                class="w-full px-4 py-2 text-left hover:bg-gray-50 text-gray-700 flex items-center">Facebook</button>
                            <button @click="sharePost('linkedin')"
                                class="w-full px-4 py-2 text-left hover:bg-gray-50 text-gray-700 flex items-center">LinkedIn</button>
                            <button @click="sharePost('copy')"
                                class="w-full px-4 py-2 text-left hover:bg-gray-50 text-gray-700 flex items-center">Linki
                                Kopyala</button>
                        </div>
                    </div>
                </div>
            </div>
        </article>
    </GuestLayout>
</template>

<style>
.blog-content h1,
.blog-content h2,
.blog-content h3,
.blog-content h4,
.blog-content h5,
.blog-content h6 {
    @apply font-bold text-gray-800;
}

.blog-content p {
    @apply text-gray-700 leading-relaxed;
}

.blog-content a {
    @apply text-orange-600 hover:underline;
}

.blog-content ul {
    @apply list-disc list-inside my-4;
}

.blog-content ol {
    @apply list-decimal list-inside my-4;
}

.blog-content blockquote {
    @apply border-l-4 border-orange-400 pl-4 italic text-gray-600 my-4;
}

.blog-content pre {
    @apply bg-gray-800 text-white p-4 rounded-lg my-4 overflow-x-auto;
}

.blog-content code {
    @apply bg-gray-200 text-gray-800 px-1 rounded;
}

.blog-content pre code {
    @apply bg-transparent text-white;
}
</style>
