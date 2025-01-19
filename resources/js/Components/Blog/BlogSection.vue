<script setup>
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    posts: {
        type: Object,
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

const truncateContent = (content, maxLength = 150) => {
    return content.length > maxLength
        ? content.substring(0, maxLength) + '...'
        : content;
};
</script>

<template>

    <Head title="Blog" />

    <div class="w-full h-screen px-4 py-12 sm:py-16 md:py-20 bg-gradient-to-b from-green-50 to-white">
        <div class="max-w-4xl mx-auto">
            <h1 class="text-3xl sm:text-4xl md:text-5xl font-bold mb-8 sm:mb-10 md:mb-12 text-gray-900 text-center">
                Blog Yazıları
            </h1>

            <div v-if="!posts.data || posts.data.length === 0" class="text-center py-16">
                <p class="text-xl sm:text-2xl text-gray-500">Henüz yazı bulunmamaktadır.</p>
            </div>

            <div v-else class="space-y-8 sm:space-y-10 md:space-y-12">
                <article v-for="post in posts.data" :key="post.id" class="group">
                    <!-- Featured Image Section -->
                    <div v-if="post.featured_image" class="mb-4 sm:mb-6 overflow-hidden rounded-lg shadow-md relative">
                        <img :src="`/storage/${post.featured_image}`" :alt="post.title"
                            class="w-full h-48 sm:h-56 md:h-64 object-cover transition-transform duration-300 group-hover:scale-105">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent"></div>
                    </div>

                    <!-- Post Content -->
                    <div>
                        <h2 class="text-2xl sm:text-3xl font-bold mb-3 sm:mb-4">
                            <Link :href="route('posts.show', post.slug)"
                                class="text-gray-900 hover:text-orange-600 transition-colors">
                            {{ post.title }}
                            </Link>
                        </h2>

                        <div
                            class="flex flex-wrap items-center gap-2 sm:gap-4 text-gray-600 mb-3 sm:mb-4 text-sm sm:text-base">
                            <span>{{ formatDate(post.created_at) }}</span>
                            <span class="hidden sm:inline">•</span>
                            <span>{{ post.user?.name }}</span>
                        </div>

                        <div class="text-gray-700 leading-relaxed mb-4 sm:mb-6 text-sm sm:text-base"
                            v-html="truncateContent(post.content)"></div>

                        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                            <Link :href="route('posts.show', post.slug)" class="bg-orange-50 text-orange-800 px-4 py-2 rounded-full text-sm font-medium 
                                       hover:bg-orange-100 transition-colors inline-flex items-center self-start">
                            Devamını Oku →
                            </Link>

                            <div class="flex flex-wrap gap-2 justify-start sm:justify-end w-full sm:w-auto">
                                <span v-for="tag in post.tags" :key="tag"
                                    class="bg-orange-50 text-orange-800 px-3 py-1 rounded-full text-xs font-medium">
                                    {{ tag }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Separator -->
                    <div v-if="post.id !== posts.data[posts.data.length - 1].id"
                        class="border-t border-gray-200 my-8 sm:my-10 md:my-12"></div>
                </article>
            </div>

            <!-- Pagination -->
            <div v-if="posts.last_page > 1" class="mt-8 sm:mt-10 md:mt-12 flex flex-wrap justify-center gap-2">
                <template v-for="link in posts.links" :key="link.label">
                    <Link v-if="link.url" :href="link.url"
                        class="px-3 py-1.5 sm:px-4 sm:py-2 border rounded-md text-sm sm:text-base" :class="{
                            'bg-orange-500 text-white': link.active,
                            'text-gray-700 hover:bg-gray-100': !link.active
                        }" v-html="link.label">
                    </Link>
                </template>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Responsive adjustments can be added here if needed */
</style>