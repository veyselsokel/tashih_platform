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

// Props definition corrected to accept 'blogPost'
const props = defineProps({
    blogPost: {
        type: Object,
        required: true
    }
});

// Lightbox'ı açan fonksiyon
const openLightbox = (index) => {
    if (props.blogPost.gallery && props.blogPost.gallery.length > 0) {
        selectedImageIndex.value = index;
        showLightbox.value = true;
    }
};
// Markdown yapılandırması
const md = new MarkdownIt({
    html: true,
    breaks: true,
    linkify: true,
    typographer: true,
    quotes: '""\'\'',
});

md.renderer.rules.html_inline = (tokens, idx) => {
    return tokens[idx].content;
};

md.renderer.rules.html_block = (tokens, idx) => {
    return tokens[idx].content;
};
// Markdown kurallarını özelleştir
// Satır sonu işlemesini özelleştirelim
md.renderer.rules.softbreak = () => '\n';
md.renderer.rules.hardbreak = () => '\n';

// Paragraf işleme kurallarını güncelleyelim
md.renderer.rules.paragraph_open = () => '<p class="whitespace-pre-line mb-4">';
md.renderer.rules.heading_open = (tokens, idx) => {
    const level = tokens[idx].tag;
    const classes = {
        h1: 'text-4xl font-bold mb-6 mt-8',
        h2: 'text-3xl font-bold mb-4 mt-6',
        h3: 'text-2xl font-bold mb-3 mt-5'
    };
    return `<${level} class="${classes[level] || ''}">`
};

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

// İçeriği formatlayan fonksiyon sadeleştirildi
const formatContent = (content) => {
    if (!content) return '';
    return md.render(content);
};

const sharePost = async (platform) => {
    const url = window.location.href;
    // Corrected to use blogPost.title
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
                // Burada bir başarı bildirimi gösterilebilir
            } catch (err) {
                console.error('URL kopyalanamadı:', err);
            }
            break;
    }
    showShareMenu.value = false;
};

// Progress bar için değişkenler ve fonksiyonlar
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
    <!-- Corrected to use blogPost.title -->
    <GuestLayout :title="blogPost.title">

        <!-- Corrected to use blogPost.title -->

        <Head :title="blogPost.title" />

        <!-- Reading Progress Bar -->
        <div class="fixed top-0 left-0 w-full h-1 bg-gray-200 z-50">
            <div class="h-full bg-orange-500 transition-all duration-150" :style="{ width: `${readingProgress}%` }">
            </div>
        </div>

        <!-- Hero Section -->
        <!-- All instances of 'post' changed to 'blogPost' -->
        <div v-if="blogPost.featured_image_url"
            class="relative w-full h-[60vh] sm:h-[70vh] overflow-hidden mt-14 sm:mt-20">
            <img :src="blogPost.featured_image_url" :alt="blogPost.title"
                class="w-full h-full object-cover transform hover:scale-105 transition-transform duration-700" />
            <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/30 to-transparent"></div>

            <!-- Back Button -->
            <Link href="/blog"
                class="absolute top-8 left-8 flex items-center text-white hover:text-orange-300 transition-colors">
            <ArrowLeft class="w-5 h-5 mr-2" />
            Geri Dön
            </Link>

            <!-- Title Section in Hero -->
            <div class="absolute bottom-0 left-0 right-0 p-8">
                <div class="max-w-4xl mx-auto">
                    <h1 class="text-4xl sm:text-5xl lg:text-6xl font-bold text-white mb-6">{{ blogPost.title }}</h1>
                    <div class="flex flex-wrap items-center gap-6 text-gray-200">
                        <div class="flex items-center">
                            <Calendar class="w-5 h-5 mr-2" />
                            {{ formatDate(blogPost.published_at || blogPost.created_at) }}
                        </div>
                        <div class="flex items-center">
                            <User class="w-5 h-5 mr-2" />
                            {{ blogPost.user?.name }}
                        </div>
                        <div class="flex items-center">
                            <Clock class="w-5 h-5 mr-2" />
                            {{ getReadingTime(blogPost.content) }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Title Section (when no featured image) -->
        <div v-else class="max-w-4xl mx-auto px-4 pt-32 pb-12">
            <!-- Back Button -->
            <Link href="/blog"
                class="inline-flex items-center text-gray-600 hover:text-orange-600 transition-colors mb-8">
            <ArrowLeft class="w-5 h-5 mr-2" />
            Geri Dön
            </Link>

            <h1 class="text-4xl sm:text-5xl font-bold text-gray-900 mb-6">{{ blogPost.title }}</h1>
            <div class="flex flex-wrap items-center gap-6 text-gray-600">
                <div class="flex items-center">
                    <Calendar class="w-5 h-5 mr-2" />
                    {{ formatDate(blogPost.published_at || blogPost.created_at) }}
                </div>
                <div class="flex items-center">
                    <User class="w-5 h-5 mr-2" />
                    {{ blogPost.user?.name }}
                </div>
                <div class="flex items-center">
                    <Clock class="w-5 h-5 mr-2" />
                    {{ getReadingTime(blogPost.content) }}
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="max-w-4xl mx-auto px-4 py-12">
            <!-- Meta Description -->
            <div v-if="blogPost.meta_description" class="mb-12">
                <p class="text-xl text-gray-600 italic border-l-4 border-orange-500 pl-4">
                    {{ blogPost.meta_description }}
                </p>
            </div>

            <!-- Tags -->
            <div v-if="blogPost.tags?.length" class="mb-12 flex flex-wrap gap-2">
                <span v-for="tag in blogPost.tags" :key="tag"
                    class="inline-flex items-center px-4 py-1.5 bg-orange-50 text-orange-800 rounded-full text-sm font-medium hover:bg-orange-100 transition-colors">
                    <Tag class="w-4 h-4 mr-1.5" />
                    {{ tag }}
                </span>
            </div>

            <!-- Article Content -->
            <article class="prose prose-lg prose-orange mx-auto blog-content" :style="{
                fontFamily: blogPost.formatting?.font,
                textAlign: blogPost.formatting?.textAlign,
                color: blogPost.formatting?.color,
                lineHeight: blogPost.formatting?.lineHeight
            }" v-html="formatContent(blogPost.content)">
            </article>
            <!-- Blog yazısı içeriğinden sonra -->
            <div v-if="blogPost.gallery?.length" class="mt-12 space-y-8">
                <h3 class="text-2xl font-bold text-gray-900">Galeri</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div v-for="(image, index) in blogPost.gallery" :key="image.id"
                        class="group relative overflow-hidden rounded-lg shadow-lg cursor-pointer hover:shadow-xl transition-all"
                        @click="openLightbox(index)">
                        <div class="aspect-w-16 aspect-h-9">
                            <img :src="image.image_url" :alt="image.alt_text"
                                class="w-full h-full object-cover transition duration-300 group-hover:scale-105" />
                        </div>

                        <div
                            class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/30 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                            <div class="absolute bottom-0 left-0 right-0 p-4">
                                <p class="text-white text-sm">{{ image.caption }}</p>
                            </div>
                        </div>

                        <!-- Zoom Icon -->
                        <div
                            class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                            <div class="bg-black/50 p-3 rounded-full">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v6m3-3H7" />
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Lightbox Component -->
                <ImageLightbox :images="blogPost.gallery" :is-open="showLightbox" :initial-index="selectedImageIndex"
                    @close="showLightbox = false" />
            </div>

            <!-- Share Section -->
            <div class="relative mt-12 pt-8 border-t border-gray-200">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <div class="text-sm">
                            <p class="text-gray-500">Yazan</p>
                            <!-- Corrected to use blogPost.user?.name -->
                            <p class="font-medium text-gray-900">{{ blogPost.user?.name }}</p>
                        </div>
                    </div>

                    <!-- Share Button -->
                    <div class="relative">
                        <button @click="showShareMenu = !showShareMenu"
                            class="flex items-center space-x-2 text-gray-600 hover:text-orange-600 transition-colors">
                            <Share2 class="w-5 h-5" />
                            <span>Paylaş</span>
                        </button>

                        <!-- Share Menu -->
                        <div v-if="showShareMenu"
                            class="absolute right-0 bottom-full mb-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 py-2">
                            <button @click="sharePost('twitter')"
                                class="w-full px-4 py-2 text-left hover:bg-gray-50 text-gray-700 flex items-center">
                                <i class="fab fa-twitter w-5 h-5 mr-2"></i>
                                Twitter
                            </button>
                            <button @click="sharePost('facebook')"
                                class="w-full px-4 py-2 text-left hover:bg-gray-50 text-gray-700 flex items-center">
                                <i class="fab fa-facebook w-5 h-5 mr-2"></i>
                                Facebook
                            </button>
                            <button @click="sharePost('linkedin')"
                                class="w-full px-4 py-2 text-left hover:bg-gray-50 text-gray-700 flex items-center">
                                <i class="fab fa-linkedin w-5 h-5 mr-2"></i>
                                LinkedIn
                            </button>
                            <button @click="sharePost('copy')"
                                class="w-full px-4 py-2 text-left hover:bg-gray-50 text-gray-700 flex items-center">
                                <i class="fas fa-link w-5 h-5 mr-2"></i>
                                Linki Kopyala
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </GuestLayout>
</template>

<style scoped>
.gallery-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1.5rem;
    margin: 2rem 0;
}

.gallery-item {
    position: relative;
    overflow: hidden;
    border-radius: 0.5rem;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}

.gallery-item:hover .gallery-caption {
    transform: translateY(0);
}

.gallery-caption {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    background: linear-gradient(to top, rgba(0, 0, 0, 0.8), transparent);
    padding: 1rem;
    transform: translateY(100%);
    transition: transform 0.3s ease;
}

/* Gallery styles */
.aspect-w-16 {
    position: relative;
    padding-bottom: 56.25%;
    /* 16:9 aspect ratio */
}

.aspect-w-16>img {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
}

/* Hover effects */
.group:hover .group-hover\:opacity-100 {
    opacity: 1;
}

.group:hover .group-hover\:scale-105 {
    transform: scale(1.05);
}

.aspect-w-16.aspect-h-9 {
    position: relative;
    padding-top: 56.25%;
}

.aspect-w-16.aspect-h-9 img {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
}

:deep(.blog-content) {
    /* Base styles */
    max-width: none;

    /* Headings */
    h1 {
        @apply text-4xl font-bold mb-6 mt-8 text-gray-900;
    }

    h2 {
        @apply text-3xl font-bold mb-4 mt-6 text-gray-900;
    }

    h3 {
        @apply text-2xl font-bold mb-3 mt-5 text-gray-900;
    }

    /* Text elements */
    p {
        @apply text-gray-700 leading-relaxed mb-6;
        white-space: pre-line;
        /* Bu önemli */
    }

    strong {
        @apply font-bold text-gray-900;
    }

    em {
        @apply italic text-gray-700;
    }

    /* Lists */
    ul {
        @apply list-disc pl-6 my-4 space-y-2;
    }

    ol {
        @apply list-decimal pl-6 my-4 space-y-2;
    }

    li {
        @apply mb-2;
    }

    /* Quotes and Code */
    blockquote {
        @apply border-l-4 border-orange-500 pl-4 italic my-6 text-gray-700 bg-orange-50 py-2;
    }

    code {
        @apply bg-gray-100 text-gray-900 px-2 py-1 rounded font-mono text-sm;
    }

    pre {
        @apply bg-gray-900 text-gray-100 p-4 rounded-lg my-6 overflow-x-auto;

        code {
            @apply bg-transparent text-inherit p-0;
        }
    }

    /* Links and Images */
    a {
        @apply text-orange-600 hover:text-orange-700 underline transition-colors;
    }

    img {
        @apply rounded-lg shadow-lg my-8 mx-auto;
    }

    /* Tables */
    table {
        @apply w-full border-collapse my-8;
    }

    thead {
        @apply bg-gray-50;
    }

    th,
    td {
        @apply border border-gray-200 p-3 text-left;
    }

    /* Figures */
    figure {
        @apply my-8;
    }

    figcaption {
        @apply text-center text-sm text-gray-600 mt-2;
    }
}
</style>
