<script setup>
import { ref, onMounted } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import NavBar from '@/Components/Welcome/NavBar.vue';

const posts = ref([]);
const loading = ref(true);
const error = ref(null);

onMounted(async () => {
    try {
        const response = await fetch('/api/posts');
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        const data = await response.json();
        posts.value = data;
    } catch (error) {
        console.error('Error fetching posts:', error);
        error.value = 'Failed to load posts. Please try again later.';
    } finally {
        loading.value = false;
    }
});
</script>

<template>

    <Head title="Blog" />
    <NavBar :can-login="canLogin" :can-register="canRegister" />
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                <div v-if="loading" class="text-center">
                    <p class="text-gray-500">Loading posts...</p>
                </div>
                <div v-else-if="error" class="text-center text-red-600">
                    {{ error }}
                </div>
                <div v-else-if="posts.length === 0" class="text-center">
                    <p class="text-gray-500">No posts found.</p>
                </div>
                <div v-else>
                    <div v-for="post in posts" :key="post.id" class="mb-8 border-b pb-4">
                        <h3 class="text-2xl font-bold mb-2">
                            <Link :href="route('posts.show', post.id)" class="text-blue-600 hover:underline">
                            {{ post.title }}
                            </Link>
                        </h3>
                        <p class="text-gray-600 mb-2">
                            Posted by {{ post.user?.name || 'Unknown' }}
                        </p>
                        <p class="text-gray-800">
                            {{ post.content.length > 200 ? post.content.substring(0, 200) + '...' : post.content }}
                        </p>
                        <Link :href="route('posts.show', post.id)"
                            class="text-blue-600 hover:underline mt-2 inline-block">
                        Read more
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>