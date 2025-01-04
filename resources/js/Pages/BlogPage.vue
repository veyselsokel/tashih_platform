// resources/js/Pages/BlogPage.vue
<script setup>
import { Head } from '@inertiajs/vue3';
import BlogSection from '@/Components/Blog/BlogSection.vue';
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

        <Head title="Blog" />
        <BlogSection :posts="posts" />
    </GuestLayout>
</template>