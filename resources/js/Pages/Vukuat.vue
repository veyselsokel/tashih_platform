<script setup>
import { ref, onMounted, computed } from 'vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import CorrectionCard from '@/Components/Vukuat/CorrectionCard.vue';
import ImageLightbox from '@/Pages/Blog/ImageLightbox.vue';
import vukuatData from '@/../assets/images/vukuat/vukuat.json';

// Import available images
const importImages = () => {
    const images = {};
    const imageFiles = [1, 2, 3, 4, 5, 6, 7, 8, 10, 12, 13, 14, 15, 16, 17, 19, 21, 23, 24, 25, 26, 27, 29, 30, 31, 34, 35, 36, 37, 39, 40, 41, 42, 43, 44, 45, 46, 47, 48, 51, 53, 57];

    imageFiles.forEach(id => {
        try {
            const ext = id === 57 ? 'png' : 'jpg';
            images[id] = new URL(`../../assets/images/vukuat/${id}.${ext}`, import.meta.url).href;
        } catch (error) {
            console.warn(`Could not load image for ID ${id}`);
        }
    });

    return images;
};

const availableImages = importImages();

const isVisible = ref(false);
const showLightbox = ref(false);
const selectedImageIndex = ref(0);

// Process vukuat data and filter out unpublishable entries
const corrections = computed(() => {
    return vukuatData.tashih_vukuat
        .filter(item => item.aciklama !== "Yayınlanmayacak" && availableImages[item.id])
        .map(item => ({
            id: item.id,
            image: availableImages[item.id],
            description: item.aciklama,
            category: getCategoryFromDescription(item.aciklama)
        }))
        .sort((a, b) => a.id - b.id);
});

// Helper function to determine category from description
const getCategoryFromDescription = (description) => {
    if (description.includes('tekrar') || description.includes('Tekrar')) return 'Kelime Tekrarı';
    if (description.includes('yanlış') || description.includes('hata')) return 'Yazım Yanlışı';
    if (description.includes('isim') || description.includes('İsim') || description.includes('soyadı')) return 'İsim Hatası';
    if (description.includes('virgül') || description.includes('nokta')) return 'Noktalama Hatası';
    if (description.includes('deyim') || description.includes('kelime')) return 'Deyim/Kelime Hatası';
    if (description.includes('çelişki') || description.includes('çelişkili')) return 'Çelişkili Bilgi';
    if (description.includes('cümle') || description.includes('anlatım')) return 'Anlatım Bozukluğu';
    return 'Tashih Hatası';
};

onMounted(() => {
    setTimeout(() => {
        isVisible.value = true;
    }, 100);
});

const lightboxImages = computed(() => corrections.value.map(c => ({
    image_url: c.image,
    caption: c.description,
    alt_text: c.description
})));

const openLightbox = (index) => {
    selectedImageIndex.value = index;
    showLightbox.value = true;
};
</script>

<template>
    <GuestLayout title="Vukuat">
        <div class="bg-stone-50 min-h-screen">
            <header class="relative text-center px-6 py-24 md:py-32">
                <div class="max-w-4xl mx-auto">
                    <h1 class="text-4xl md:text-5xl font-extrabold text-gray-800 leading-tight mb-4">
                        Vukuat: Sıkça Yapılan Hatalar
                    </h1>
                    <p class="text-lg text-gray-600 max-w-3xl mx-auto">
                        Gerçek hayattan alınmış tashih (düzeltme) örnekleri ve hataların analizleri.
                    </p>
                </div>
            </header>

            <main class="px-4 sm:px-6 lg:px-8 pb-24">
                <div class="max-w-7xl mx-auto">
                    <div class="text-center mb-8">
                        <p class="text-gray-600">
                            Toplam {{ corrections.length }} tashih örneği bulunmaktadır.
                        </p>
                    </div>
                    <div class="masonry-grid">
                        <div v-for="(correction, index) in corrections" :key="correction.id"
                            class="masonry-item mb-8 break-inside-avoid">
                            <CorrectionCard :item="correction" @image-click="openLightbox(index)" />
                        </div>
                    </div>
                </div>
            </main>
        </div>
        <ImageLightbox :images="lightboxImages" :is-open="showLightbox" :initial-index="selectedImageIndex"
            @close="showLightbox = false" />
    </GuestLayout>
</template>

<style scoped>
.masonry-grid {
    column-count: 1;
}

@media (min-width: 768px) {
    .masonry-grid {
        column-count: 2;
        column-gap: 2rem;
    }
}

@media (min-width: 1024px) {
    .masonry-grid {
        column-count: 3;
        column-gap: 2rem;
    }
}

.break-inside-avoid {
    break-inside: avoid;
}
</style>
