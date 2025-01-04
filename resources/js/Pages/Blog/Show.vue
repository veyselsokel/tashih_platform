<script setup>
import { Head } from '@inertiajs/vue3';
import GuestLayout from '@/Layouts/GuestLayout.vue';

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

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('tr-TR', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
};

// İçeriği paragraflar halinde formatlayan fonksiyon
const formatContent = (content) => {
    if (!content) return '';
    return content.split('\n\n')
        .filter(paragraph => paragraph.trim() !== '')
        .map(paragraph => `<p>${paragraph.replace(/\n/g, '<br>')}</p>`)
        .join('');
};
</script>

<template>
    <GuestLayout :title="title">

        <Head :title="post.title" />

        <!-- Hero Section with Featured Image -->
        <div v-if="post.featured_image" class="w-full h-[40vh] relative">
            <img :src="`/storage/${post.featured_image}`" :alt="post.title" class="w-full h-full object-cover">
            <div class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent"></div>
            <div class="absolute bottom-0 left-0 right-0 text-white p-8 max-w-4xl mx-auto">
                <h1 class="text-5xl font-bold mb-4">{{ post.title }}</h1>
                <div class="flex items-center gap-4 text-gray-200">
                    <span>{{ formatDate(post.created_at) }}</span>
                    <span>•</span>
                    <span>{{ post.user?.name }}</span>
                </div>
            </div>
        </div>

        <!-- Title Section (when no featured image) -->
        <div v-else class="max-w-4xl mx-auto px-4 pt-24 pb-12">
            <h1 class="text-5xl font-bold mb-4 text-gray-900">{{ post.title }}</h1>
            <div class="flex items-center gap-4 text-gray-600">
                <span>{{ formatDate(post.created_at) }}</span>
                <span>•</span>
                <span>{{ post.user?.name }}</span>
            </div>
        </div>

        <!-- Main Content -->
        <div class="max-w-4xl mx-auto px-4 py-12">
            <!-- Tags -->
            <div v-if="post.tags?.length" class="mb-12 flex flex-wrap gap-2">
                <span v-for="tag in post.tags" :key="tag"
                    class="bg-orange-50 text-orange-800 px-4 py-1.5 rounded-full text-sm font-medium hover:bg-orange-100 transition-colors">
                    {{ tag }}
                </span>
            </div>

            <!-- Article Content -->
            <div class="prose prose-lg prose-orange mx-auto" :style="{
                fontFamily: post.formatting?.font,
                textAlign: post.formatting?.textAlign,
                color: post.formatting?.color,
                lineHeight: post.formatting?.lineHeight
            }" v-html="formatContent(post.content)">
            </div>

            <!-- Article Footer -->
            <div class="mt-16 pt-8 border-t border-gray-200">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <div class="text-sm">
                            <p class="text-gray-500">Yazan</p>
                            <p class="font-medium text-gray-900">{{ post.user?.name }}</p>
                        </div>
                    </div>
                    <div class="text-sm text-gray-500">
                        {{ formatDate(post.created_at) }}
                    </div>
                </div>
            </div>
        </div>
    </GuestLayout>
</template>

<style>
.prose {
    max-width: none;
}

.prose h2 {
    @apply text-3xl font-bold text-gray-900 mt-12 mb-6;
}

.prose h3 {
    @apply text-2xl font-bold text-gray-900 mt-10 mb-4;
}

.prose p {
    @apply text-gray-700 leading-relaxed mb-6 whitespace-pre-wrap;
}

.prose img {
    @apply rounded-lg my-8 shadow-lg;
}

.prose a {
    @apply text-orange-600 hover:text-orange-700 no-underline border-b-2 border-orange-200 hover:border-orange-600 transition-colors;
}

.prose ul {
    @apply my-6 list-disc list-inside;
}

.prose ol {
    @apply my-6 list-decimal list-inside;
}

.prose blockquote {
    @apply border-l-4 border-orange-500 pl-4 italic my-8 text-gray-700;
}

.prose code {
    @apply bg-gray-100 text-gray-900 px-1.5 py-0.5 rounded text-sm;
}

.prose pre {
    @apply bg-gray-900 text-gray-100 rounded-lg p-4 my-6 overflow-x-auto;
}
</style>