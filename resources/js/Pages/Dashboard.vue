<script setup>
import { ref, onMounted } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Edit, Trash2, Eye, PlusCircle, FileText, CheckCircle2, PenLine } from 'lucide-vue-next';
import Modal from '@/Components/Modal.vue';

const posts = ref([]);
const stats = ref({
    totalPosts: 0,
    publishedPosts: 0,
    draftPosts: 0
});

const showDeleteModal = ref(false);
const selectedPost = ref(null);
const isLoading = ref(true);

onMounted(async () => {
    try {
        const response = await fetch(route('dashboard.data'));
        const data = await response.json();
        posts.value = data.posts;
        stats.value = data.stats;
    } catch (error) {
        console.error('Error fetching posts:', error);
    } finally {
        isLoading.value = false;
    }
});

const confirmDelete = (post) => {
    selectedPost.value = post;
    showDeleteModal.value = true;
};

const deletePost = () => {
    router.delete(route('blog.destroy', selectedPost.value.slug), {
        onSuccess: () => {
            showDeleteModal.value = false;
            selectedPost.value = null;
        },
    });
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('tr-TR', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
};
</script>

<template>
    <AppLayout title="Dashboard">
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Blog Yazılarım
                </h2>
                <Link :href="route('blog.create')"
                    class="inline-flex items-center px-4 py-2 bg-orange-500 text-white rounded-md hover:bg-orange-600 transition">
                <PlusCircle class="w-5 h-5 mr-2" />
                Yeni Yazı
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- İstatistikler -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 mb-6">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <div class="flex items-center">
                            <FileText class="w-8 h-8 text-blue-500" />
                            <div class="ml-4">
                                <p class="text-sm text-gray-600">Toplam Yazı</p>
                                <p class="text-2xl font-semibold">{{ stats.totalPosts }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <div class="flex items-center">
                            <CheckCircle2 class="w-8 h-8 text-green-500" />
                            <div class="ml-4">
                                <p class="text-sm text-gray-600">Yayında</p>
                                <p class="text-2xl font-semibold">{{ stats.publishedPosts }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <div class="flex items-center">
                            <PenLine class="w-8 h-8 text-yellow-500" />
                            <div class="ml-4">
                                <p class="text-sm text-gray-600">Taslak</p>
                                <p class="text-2xl font-semibold">{{ stats.draftPosts }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Blog Yazıları Tablosu -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div v-if="isLoading" class="text-center py-12">
                            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-orange-500 mx-auto"></div>
                        </div>

                        <div v-else-if="posts.length === 0" class="text-center py-12">
                            <p class="text-gray-500 mb-4">Henüz blog yazınız bulunmuyor.</p>
                            <Link :href="route('blog.create')"
                                class="inline-flex items-center px-4 py-2 bg-orange-500 text-white rounded-md hover:bg-orange-600 transition">
                            <PlusCircle class="w-5 h-5 mr-2" />
                            İlk yazınızı oluşturun
                            </Link>
                        </div>

                        <div v-else>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead>
                                        <tr>
                                            <th
                                                class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase">
                                                Başlık
                                            </th>
                                            <th
                                                class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase">
                                                Durum
                                            </th>
                                            <th
                                                class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase">
                                                Oluşturulma Tarihi
                                            </th>
                                            <th
                                                class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase">
                                                Son Güncelleme
                                            </th>
                                            <th
                                                class="px-6 py-3 bg-gray-50 text-center text-xs font-medium text-gray-500 uppercase">
                                                İşlemler
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <tr v-for="post in posts" :key="post.id" class="hover:bg-gray-50">
                                            <td class="px-6 py-4">
                                                <div class="flex items-center">
                                                    <img v-if="post.featured_image_url" :src="post.featured_image_url"
                                                        class="h-10 w-10 rounded-md object-cover mr-3"
                                                        :alt="post.title" />
                                                    <div>
                                                        <div class="font-medium text-gray-900">{{ post.title }}</div>
                                                        <div class="text-sm text-gray-500">
                                                            {{ post.meta_description?.substring(0, 50) }}...
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4">
                                                <span :class="[
                                                    'px-2 py-1 text-xs font-medium rounded-full',
                                                    post.is_draft ? 'bg-yellow-100 text-yellow-800' :
                                                        post.is_published ? 'bg-green-100 text-green-800' :
                                                            'bg-gray-100 text-gray-800'
                                                ]">
                                                    {{ post.is_draft ? 'Taslak' :
                                                        post.is_published ? 'Yayında' : 'Beklemede' }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 text-sm text-gray-500">
                                                {{ formatDate(post.created_at) }}
                                            </td>
                                            <td class="px-6 py-4 text-sm text-gray-500">
                                                {{ formatDate(post.updated_at) }}
                                            </td>
                                            <td class="px-6 py-4">
                                                <div class="flex justify-center space-x-3">
                                                    <Link :href="route('blog.show', post.slug)"
                                                        class="text-gray-400 hover:text-gray-500">
                                                    <Eye class="w-5 h-5" />
                                                    </Link>
                                                    <Link :href="route('blog.edit', post.slug)"
                                                        class="text-blue-400 hover:text-blue-500">
                                                    <Edit class="w-5 h-5" />
                                                    </Link>
                                                    <button @click="confirmDelete(post)"
                                                        class="text-red-400 hover:text-red-500">
                                                        <Trash2 class="w-5 h-5" />
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <Modal :show="showDeleteModal" @close="showDeleteModal = false">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">
                    Blog Yazısını Sil
                </h3>
                <p class="text-gray-600 mb-6">
                    "{{ selectedPost?.title }}" başlıklı blog yazısını silmek istediğinizden emin misiniz?
                    Bu işlem geri alınamaz.
                </p>
                <div class="flex justify-end space-x-3">
                    <button @click="showDeleteModal = false"
                        class="px-4 py-2 text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200">
                        İptal
                    </button>
                    <button @click="deletePost" class="px-4 py-2 text-white bg-red-500 rounded-md hover:bg-red-600">
                        Sil
                    </button>
                </div>
            </div>
        </Modal>
    </AppLayout>
</template>