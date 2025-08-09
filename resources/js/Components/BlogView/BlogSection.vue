<script setup>
import { Link } from '@inertiajs/vue3';
import { Search, Calendar, User, Clock, Tag } from 'lucide-vue-next';
import { ref, computed } from 'vue';

const props = defineProps({
    posts: {
        type: Object,
        required: true
    }
});

const searchQuery = ref('');
const selectedTag = ref('');
const sortBy = ref('newest');

// Tüm etiketleri topla
const allTags = computed(() => {
    const tags = new Set();
    props.posts.data.forEach(post => {
        if (post.tags) {
            post.tags.forEach(tag => tags.add(tag));
        }
    });
    return Array.from(tags);
});

// Filtrelenmiş postlar
const filteredPosts = computed(() => {
    let posts = props.posts.data.filter(post => {
        const matchesSearch = post.title.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
            post.meta_description?.toLowerCase().includes(searchQuery.value.toLowerCase());
        const matchesTag = !selectedTag.value || (post.tags && post.tags.includes(selectedTag.value));
        return matchesSearch && matchesTag;
    });

    // Sıralama
    return posts.sort((a, b) => {
        switch (sortBy.value) {
            case 'oldest':
                return new Date(a.published_at || a.created_at) - new Date(b.published_at || b.created_at);
            case 'title':
                return a.title.localeCompare(b.title);
            default: // newest
                return new Date(b.published_at || b.created_at) - new Date(a.published_at || a.created_at);
        }
    });
});


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
</script>

<template>
    <section class="py-16 sm:py-24">
        <div class="container mx-auto px-4">
            <!-- Filtreler ve Arama -->
            <div class="mb-12">
                <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                    <!-- Arama Başlığı -->
                    <h2 class="text-2xl font-bold text-gray-900 mb-6">Blog Yazıları</h2>

                    <div class="space-y-6">
                        <!-- Arama Alanı -->
                        <div class="relative">
                            <div class="flex items-center bg-gray-50 rounded-lg p-2">
                                <Search class="text-gray-400 h-5 w-5 ml-2" />
                                <input type="text" v-model="searchQuery" placeholder="Blog yazılarında ara..."
                                    class="w-full px-4 py-2 bg-transparent border-none focus:ring-0 text-gray-700 placeholder-gray-400" />
                            </div>
                        </div>

                        <!-- Filtreler -->
                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <h3 class="text-sm font-medium text-gray-700">Etiketler</h3>
                                <button v-if="selectedTag" @click="selectedTag = ''"
                                    class="text-sm text-orange-600 hover:text-orange-700">
                                    Filtreleri Temizle
                                </button>
                            </div>
                            <div class="flex flex-wrap gap-2">
                                <button v-for="tag in allTags" :key="tag"
                                    @click="selectedTag = selectedTag === tag ? '' : tag" :class="[
                                        'px-4 py-2 rounded-full text-sm font-medium transition-all duration-200',
                                        selectedTag === tag
                                            ? 'bg-orange-500 text-white shadow-md transform scale-105'
                                            : 'bg-orange-50 text-orange-700 hover:bg-orange-100'
                                    ]">
                                    <div class="flex items-center space-x-1">
                                        <Tag class="h-3.5 w-3.5" />
                                        <span>{{ tag }}</span>
                                    </div>
                                </button>
                            </div>
                        </div>

                        <!-- Sonuç Sayısı -->
                        <div class="pt-4 border-t border-gray-100">
                            <p class="text-sm text-gray-600">
                                {{ filteredPosts.length }} yazı bulundu
                                <span v-if="selectedTag">
                                    "{{ selectedTag }}" etiketi için
                                </span>
                                <span v-if="searchQuery">
                                    "{{ searchQuery }}" araması için
                                </span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sıralama -->
            <div class="mb-6 flex justify-end">
                <select v-model="sortBy"
                    class="border-gray-300 rounded-lg text-sm focus:border-orange-500 focus:ring-orange-500">
                    <option value="newest">En Yeni</option>
                    <option value="oldest">En Eski</option>
                    <option value="title">Başlığa Göre</option>
                </select>
            </div>

            <!-- Blog Grid -->
            <div class="grid gap-8 md:grid-cols-2 lg:grid-cols-3">
                <article v-for="post in filteredPosts" :key="post.id"
                    class="group bg-white rounded-xl shadow-sm hover:shadow-lg transition-all duration-300 overflow-hidden border border-gray-100">
                    <!-- Görsel -->
                    <Link :href="route('blog.show', post.slug)" class="block relative aspect-[16/9] overflow-hidden">
                    <img v-if="post.featured_image_url" :src="post.featured_image_url" :alt="post.title"
                        class="w-full h-full object-cover transform group-hover:scale-105 transition-transform duration-300" />
                    <div v-else
                        class="w-full h-full bg-gradient-to-br from-orange-100 to-orange-50 flex items-center justify-center">
                        <span class="text-orange-300 font-medium">Görsel yok</span>
                    </div>
                    </Link>

                    <!-- İçerik -->
                    <div class="p-6 space-y-4">
                        <!-- Meta Bilgiler -->
                        <div class="flex items-center gap-4 text-sm text-gray-600">
                            <div class="flex items-center">
                                <Calendar class="h-4 w-4 mr-1" />
                                {{ formatDate(post.published_at || post.created_at) }}
                            </div>
                            <div v-if="post.user" class="flex items-center">
                                <User class="h-4 w-4 mr-1" />
                                {{ post.user.name }}
                            </div>
                            <div class="flex items-center">
                                <Clock class="h-4 w-4 mr-1" />
                                {{ getReadingTime(post.content) }}
                            </div>
                        </div>

                        <!-- Başlık -->
                        <h2
                            class="text-xl font-bold text-gray-900 group-hover:text-orange-600 transition-colors line-clamp-2">
                            <Link :href="route('blog.show', post.slug)">
                            {{ post.title }}
                            </Link>
                        </h2>

                        <!-- Özet -->
                        <p class="text-gray-600 line-clamp-3">
                            {{ post.meta_description || post.content.substring(0, 150) + '...' }}
                        </p>

                        <!-- Etiketler -->
                        <div v-if="post.tags?.length" class="flex flex-wrap gap-2">
                            <span v-for="tag in post.tags" :key="tag"
                                class="inline-flex items-center px-3 py-1 text-xs font-medium text-orange-800 bg-orange-100 rounded-full">
                                <Tag class="h-3 w-3 mr-1" />
                                {{ tag }}
                            </span>
                        </div>

                        <!-- Devamını Oku -->
                        <Link :href="route('blog.show', post.slug)"
                            class="inline-flex items-center text-orange-600 hover:text-orange-700 font-medium">
                        Devamını Oku
                        <svg xmlns="http://www.w3.org/2000/svg"
                            class="h-4 w-4 ml-1 transform group-hover:translate-x-1 transition-transform"
                            viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M10.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L12.586 11H5a1 1 0 110-2h7.586l-2.293-2.293a1 1 0 010-1.414z"
                                clip-rule="evenodd" />
                        </svg>
                        </Link>
                    </div>
                </article>
            </div>

            <!-- Sayfalama -->
            <div v-if="posts.last_page > 1" class="mt-12 flex justify-center">
                <div class="inline-flex gap-2 rounded-lg bg-white p-1 shadow-sm">
                    <Link v-for="page in posts.last_page" :key="page" :href="route('blog.index', { page })"
                        class="px-4 py-2 text-sm font-medium rounded-md transition-colors" :class="[
                            page === posts.current_page
                                ? 'bg-orange-500 text-white'
                                : 'text-gray-700 hover:bg-orange-50'
                        ]">
                    {{ page }}
                    </Link>
                </div>
            </div>
        </div>
    </section>
</template>

<style scoped>
/* Etiket hover efekti */
.tag-button {
    transition: all 0.2s ease;
}

.tag-button:hover {
    transform: translateY(-1px);
}

/* Input focus efekti */
input:focus {
    box-shadow: none;
    outline: 2px solid transparent;
    outline-offset: 2px;
}
</style>