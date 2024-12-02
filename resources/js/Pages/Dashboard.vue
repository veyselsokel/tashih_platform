<!-- resources/js/Pages/Dashboard.vue -->
<template>
    <AppLayout title="Dashboard">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Dashboard
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <!-- Correction Requests Section -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <h3 class="text-lg font-medium mb-4">Correction Requests</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead>
                                    <tr>
                                        <th
                                            class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Date</th>
                                        <th
                                            class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Client</th>
                                        <th
                                            class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Status</th>
                                        <th
                                            class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="request in correctionRequests" :key="request.id">
                                        <td class="px-6 py-4 whitespace-nowrap">{{ formatDate(request.created_at) }}
                                        </td>
                                        <td class="px-6 py-4">{{ request.client_name }}</td>
                                        <td class="px-6 py-4">
                                            <span :class="getStatusClass(request.status)">
                                                {{ request.status }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <button @click="viewRequest(request.id)"
                                                class="text-indigo-600 hover:text-indigo-900">View</button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Blog Posts Section -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-medium">Blog Posts</h3>
                            <PrimaryButton @click="showNewPostModal = true">New Post</PrimaryButton>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead>
                                    <tr>
                                        <th
                                            class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Title</th>
                                        <th
                                            class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Status</th>
                                        <th
                                            class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Published Date</th>
                                        <th
                                            class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="post in blogPosts" :key="post.id">
                                        <td class="px-6 py-4">{{ post.title }}</td>
                                        <td class="px-6 py-4">
                                            <span :class="getPostStatusClass(post.status)">
                                                {{ post.status }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ formatDate(post.published_at) }}</td>
                                        <td class="px-6 py-4 space-x-2">
                                            <button @click="editPost(post.id)"
                                                class="text-indigo-600 hover:text-indigo-900">Edit</button>
                                            <button @click="deletePost(post.id)"
                                                class="text-red-600 hover:text-red-900">Delete</button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- New Post Modal -->
        <Modal :show="showNewPostModal" @close="showNewPostModal = false">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">Create New Blog Post</h2>
                <form @submit.prevent="submitPost">
                    <div class="mb-4">
                        <InputLabel for="title" value="Title" />
                        <TextInput id="title" type="text" class="mt-1 block w-full" v-model="newPost.title" required />
                    </div>

                    <div class="mb-4">
                        <InputLabel for="content" value="Content" />
                        <textarea id="content"
                            class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                            v-model="newPost.content" rows="6" required></textarea>
                    </div>

                    <div class="flex justify-end space-x-2">
                        <SecondaryButton @click="showNewPostModal = false">Cancel</SecondaryButton>
                        <PrimaryButton type="submit">Create Post</PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>
    </AppLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AuthenticatedLayout.vue';
import Modal from '@/Components/Modal.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

const correctionRequests = ref([]);
const blogPosts = ref([]);
const showNewPostModal = ref(false);

const newPost = useForm({
    title: '',
    content: '',
    status: 'draft'
});

onMounted(() => {
    loadDashboardData();
});

const loadDashboardData = async () => {
    try {
        // Load blog posts
        const postsResponse = await fetch(route('posts.index'));
        const postsData = await postsResponse.json();
        blogPosts.value = postsData;

        // Load correction requests
        const requestsResponse = await fetch(route('correction-requests.index'));
        const requestsData = await requestsResponse.json();
        correctionRequests.value = requestsData;
    } catch (error) {
        console.error('Error loading dashboard data:', error);
    }
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString();
};

const getStatusClass = (status) => {
    const classes = {
        pending: 'bg-yellow-100 text-yellow-800',
        'in-progress': 'bg-blue-100 text-blue-800',
        completed: 'bg-green-100 text-green-800',
    };
    return `px-2 py-1 text-xs font-medium rounded-full ${classes[status] || ''}`;
};

const getPostStatusClass = (status) => {
    const classes = {
        draft: 'bg-gray-100 text-gray-800',
        published: 'bg-green-100 text-green-800',
    };
    return `px-2 py-1 text-xs font-medium rounded-full ${classes[status] || ''}`;
};

const submitPost = () => {
    newPost.post(route('posts.store'), {
        onSuccess: () => {
            showNewPostModal.value = false;
            loadDashboardData();
            newPost.reset();
        },
    });
};

const viewRequest = (id) => {
    // Implement view request logic
};

const editPost = (post) => {
    window.location.href = route('posts.edit', post.id);
};

// Update deletePost function
const deletePost = async (id) => {
    if (confirm('Are you sure you want to delete this post?')) {
        try {
            await fetch(route('posts.destroy', id), {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });
            await loadDashboardData();
        } catch (error) {
            console.error('Error deleting post:', error);
        }
    }
};
</script>