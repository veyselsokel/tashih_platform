<script setup>
import { ref, onMounted } from 'vue';
import { Link } from '@inertiajs/vue3';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { MoveDown, BookOpen, FileText, BookCheck, Newspaper, GraduationCap, FileEdit, CheckCircle } from 'lucide-vue-next';

const isVisible = ref(false);
const selectedService = ref(null);

onMounted(() => {
    setTimeout(() => {
        isVisible.value = true;
    }, 100);
});

const services = [
    {
        icon: BookOpen,
        title: 'Kitap Tashihi',
        description: 'Roman, hikaye, şiir ve her türlü edebi eserin profesyonel tashih hizmeti',
        features: ['Gramer ve imla kontrolü', 'Anlam bütünlüğü', 'Üslup düzenlemesi']
    },
    {
        icon: FileText,
        title: 'Akademik Metin Tashihi',
        description: 'Tez, makale ve akademik yayınlar için uzman tashih desteği',
        features: ['APA formatı kontrolü', 'Akademik dil düzenlemesi', 'Kaynakça kontrolü']
    },
    {
        icon: Newspaper,
        title: 'Kurumsal İçerik Tashihi',
        description: 'Şirket dokümanları, raporlar ve kurumsal iletişim metinleri için tashih hizmeti',
        features: ['Kurumsal dil uyumu', 'Terminoloji kontrolü', 'Format standardizasyonu']
    },
    {
        icon: BookCheck,
        title: 'Son Okuma',
        description: 'Yayın öncesi son kontrol ve detaylı inceleme hizmeti',
        features: ['Detaylı kontrol', 'Tutarlılık denetimi', 'Son düzeltmeler']
    },
    {
        icon: GraduationCap,
        title: 'Eğitim Materyalleri',
        description: 'Ders kitapları ve eğitim içeriklerinin tashihi',
        features: ['Pedagojik uygunluk', 'Seviye kontrolü', 'Öğretim dili düzenlemesi']
    },
    {
        icon: FileEdit,
        title: 'Web İçerik Tashihi',
        description: 'Blog yazıları, web sitesi içerikleri ve dijital metinler için tashih hizmeti',
        features: ['SEO uyumlu düzenleme', 'Web formatı kontrolü', 'Başlık optimizasyonu']
    }
];

const handleServiceClick = (index) => {
    selectedService.value = selectedService.value === index ? null : index;
};
</script>

<template>
    <GuestLayout>
        <div class="relative min-h-screen bg-gradient-to-b from-green-50 to-white">
            <!-- Main Content -->
            <div class="relative min-h-screen px-6 py-24">
                <div class="max-w-7xl mx-auto">
                    <!-- Header Section -->
                    <div class="text-center mb-16">
                        <h1 class="text-4xl sm:text-5xl font-bold tracking-tight leading-tight mb-6 transition-all duration-1000"
                            :class="[
                                isVisible ? 'opacity-100 translate-y-0 scale-100' : 'opacity-0 -translate-y-10 scale-95',
                                'transform'
                            ]">
                            <span class="text-orange-500 inline-block transition-all duration-1000 hover:scale-105">
                                Hizmetlerimiz
                            </span>
                        </h1>
                        <p class="text-lg text-navy-600 max-w-2xl mx-auto transition-all duration-1000 delay-300"
                            :class="isVisible ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4'">
                            Profesyonel tashih ve düzeltme hizmetlerimizle metinlerinizi mükemmelleştiriyoruz
                        </p>
                    </div>

                    <!-- Services Grid -->
                    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8 mb-16">
                        <div v-for="(service, index) in services" :key="index" @click="handleServiceClick(index)"
                            class="bg-white p-6 rounded-lg shadow-lg cursor-pointer transform transition-all duration-500"
                            :class="[
                                { 'opacity-100 translate-y-0': isVisible, 'opacity-0 translate-y-4': !isVisible },
                                selectedService === index ? 'scale-105 ring-2 ring-orange-500' : 'hover:scale-102',
                            ]" :style="{ transitionDelay: `${200 + index * 100}ms` }">
                            <div class="flex items-center mb-4">
                                <component :is="service.icon"
                                    class="w-12 h-12 text-orange-500 transition-transform duration-500"
                                    :class="selectedService === index ? 'rotate-12' : ''" />
                            </div>
                            <h3 class="text-xl font-semibold mb-2 text-navy-900 transition-colors duration-300"
                                :class="selectedService === index ? 'text-orange-500' : ''">
                                {{ service.title }}
                            </h3>
                            <p class="text-navy-600 mb-4">
                                {{ service.description }}
                            </p>
                            <!-- Features List -->
                            <div class="space-y-2 mt-4" v-if="selectedService === index">
                                <div v-for="(feature, fIndex) in service.features" :key="fIndex"
                                    class="flex items-center space-x-2 text-sm transition-all duration-300"
                                    :style="{ transitionDelay: `${fIndex * 100}ms` }">
                                    <CheckCircle class="w-4 h-4 text-orange-500" />
                                    <span>{{ feature }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- CTA Section -->
                    <div class="text-center space-y-8 mt-16 bg-white p-8 rounded-lg shadow-lg transform transition-all duration-1000"
                        :class="{ 'opacity-100 translate-y-0': isVisible, 'opacity-0 translate-y-4': !isVisible }"
                        style="transition-delay: 800ms">
                        <h2 class="text-3xl font-semibold text-navy-900">
                            Metninizi Profesyonellerle İyileştirin
                        </h2>
                        <p class="text-lg text-navy-600 max-w-2xl mx-auto">
                            Tashih hizmetlerimiz hakkında detaylı bilgi almak ve fiyat teklifi için bizimle iletişime
                            geçin
                        </p>
                        <Link href="/iletisim" class="inline-block group relative rounded-md bg-orange-500 px-8 py-4 
           text-white font-semibold shadow-lg hover:shadow-xl transition-all duration-300 
           transform hover:-translate-y-1">
                        <span class="relative z-10 flex items-center justify-center space-x-2">
                            <span>İletişime Geçin</span>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 transition-transform duration-300 
              group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 7l5 5m0 0l-5 5m5-5H6" />
                            </svg>
                        </span>
                        <div class="absolute inset-0 bg-orange-600 rounded-md scale-x-0 origin-left 
              transition-transform duration-300 group-hover:scale-x-100">
                        </div>
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </GuestLayout>
</template>

<style scoped>
@keyframes float {

    0%,
    100% {
        transform: translateY(0);
    }

    50% {
        transform: translateY(-10px);
    }
}

.animate-float {
    animation: float 2s infinite ease-in-out;
}

.hover\:scale-102:hover {
    transform: scale(1.02);
}

/* Smooth transition for all transformations */
* {
    transition-property: transform, opacity, color, background-color, box-shadow;
    transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
}
</style>