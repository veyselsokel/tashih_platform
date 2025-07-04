<script setup>
import { ref, onMounted, computed } from 'vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import CorrectionCard from '@/Components/Vukuat/CorrectionCard.vue';
import ImageLightbox from '@/Pages/Blog/ImageLightbox.vue';

// Import the single available image
import vukuatImage1 from '@/../assets/images/vukuat/vukuat-1.jpg';
import vukuatImage2 from '@/../assets/images/vukuat/vukuat-2.jpg';
import vukuatImage3 from '@/../assets/images/vukuat/vukuat-3.jpg';
import vukuatImage4 from '@/../assets/images/vukuat/vukuat-4.jpg';
import vukuatImage5 from '@/../assets/images/vukuat/vukuat-5.jpg';
import vukuatImage6 from '@/../assets/images/vukuat/vukuat-6.jpg';

const isVisible = ref(false);
const showLightbox = ref(false);
const selectedImageIndex = ref(0);

const corrections = ref([
    {
        id: 1,
        image: vukuatImage1,
        description: 'Bu örnekte, \'de\' bağlacı yanlış bir şekilde bitişik yazılmıştır. Doğrusu: "O da gelecek." olmalıdır.',
        category: 'Bağlaç Hatası'
    },
    {
        id: 2,
        image: vukuatImage2,
        description: 'Burada \'ki\' ilgi eki yanlış ayrı yazılmıştır. Cümledeki anlamı güçlendirmek için bitişik yazılmalıdır: "Benimki daha güzel."',
        category: 'Ek Hatası'
    },
    {
        id: 3,
        image: vukuatImage3,
        description: 'Noktalama işareti eksikliği cümlenin anlamını belirsizleştirmiş. Cümlenin sonuna nokta konulmalıydı.',
        category: 'Noktalama Hatası'
    },
    {
        id: 4,
        image: vukuatImage4,
        description: 'Bu metinde anlatım bozukluğu bulunmaktadır. Cümlenin daha akıcı ve anlaşılır olması için yeniden yapılandırılması gerekir.',
        category: 'Anlatım Bozukluğu'
    },
    {
        id: 5,
        image: vukuatImage5,
        description: 'Kelime yanlış anlamda kullanılmıştır. Cümlenin bağlamına uygun olmayan bir kelime seçimi yapılmıştır.',
        category: 'Anlam Hatası'
    },
    {
        id: 6,
        image: vukuatImage6,
        description: 'Sık yapılan bir yazım yanlışı. \'Herkes\' kelimesi \'z\' harfi ile biter, \'s\' ile değil.',
        category: 'Yazım Yanlışı'
    }
]);

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
