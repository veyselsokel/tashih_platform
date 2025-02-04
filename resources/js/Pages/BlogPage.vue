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
        <div class="bg-gradient-to-b from-green-50 to-white min-h-screen flex items-center justify-center">

            <Head title="Blog" />
            <BlogSection :posts="posts" />
        </div>
    </GuestLayout>
</template>