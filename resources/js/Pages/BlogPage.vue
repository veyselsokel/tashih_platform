// resources/js/Pages/BlogPage.vue
<script setup>
import { Head } from '@inertiajs/vue3';
import BlogSection from '@/Components/BlogView/BlogSection.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';

// Props tanımlaması ekleniyor
defineProps({
    posts: {
        type: Object,
        required: true
    },
    title: {
        type: String,
        required: true
    }
});
</script>

<template>
    <GuestLayout :title="title">
        <div class="bg-stone-50 min-h-screen">
            <header class="relative text-center px-6 py-24 md:py-32">
                 <div class="max-w-4xl mx-auto">
                    <h1 class="text-4xl md:text-5xl font-extrabold text-gray-800 leading-tight mb-4">
                        Blog
                    </h1>
                    <p class="text-lg text-gray-600 max-w-3xl mx-auto">
                        Sektördeki yenilikler, dil ve yazım üzerine notlar ve daha fazlası.
                    </p>
                </div>
            </header>
            <main class="pb-24">
                <BlogSection :posts="posts" />
            </main>
        </div>
    </GuestLayout>
</template>