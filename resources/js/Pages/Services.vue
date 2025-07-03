<script setup>
import { ref, onMounted } from 'vue';
import { Link } from '@inertiajs/vue3';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { BookOpen, FileText, BookCheck, Newspaper, GraduationCap, FileEdit, CheckCircle } from 'lucide-vue-next';

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
    <GuestLayout title="Hizmetlerimiz">
        <div class="bg-stone-50">
             <!-- Header Section -->
            <header class="relative text-center px-6 py-24 md:py-32 lg:py-40">
                 <div class="max-w-4xl mx-auto">
                    <transition enter-active-class="transition ease-out duration-1000" enter-from-class="opacity-0 translate-y-4" enter-to-class="opacity-100 translate-y-0">
                        <h1 v-if="isVisible" class="text-4xl md:text-5xl font-extrabold text-gray-800 leading-tight mb-4">
                            Hizmetlerimiz
                        </h1>
                    </transition>
                    <transition enter-active-class="transition ease-out duration-1000 delay-200" enter-from-class="opacity-0 translate-y-4" enter-to-class="opacity-100 translate-y-0">
                        <p v-if="isVisible" class="text-lg text-gray-600 max-w-3xl mx-auto">
                            Profesyonel tashih ve düzeltme hizmetlerimizle metinlerinizi mükemmelleştiriyoruz.
                        </p>
                    </transition>
                </div>
            </header>

            <!-- Main Content -->
            <main class="px-6 pb-24">
                <div class="max-w-7xl mx-auto">
                    <!-- Services Grid -->
                    <section class="grid md:grid-cols-2 lg:grid-cols-3 gap-8 mb-24">
                        <div v-for="(service, index) in services" :key="index" @click="handleServiceClick(index)"
                            class="bg-white p-8 rounded-xl shadow-lg cursor-pointer transform transition-all duration-500"
                            :class="[
                                { 'opacity-100 translate-y-0': isVisible, 'opacity-0 translate-y-4': !isVisible },
                                selectedService === index ? 'scale-105 ring-2 ring-orange-500' : 'hover:scale-102',
                            ]" :style="{ transitionDelay: `${200 + index * 100}ms` }">
                            <div class="flex items-center mb-6">
                                <component :is="service.icon"
                                    class="w-12 h-12 text-orange-500 transition-transform duration-500"
                                    :class="selectedService === index ? 'rotate-12' : ''" />
                            </div>
                            <h3 class="text-2xl font-semibold mb-3 text-gray-800 transition-colors duration-300"
                                :class="selectedService === index ? 'text-orange-500' : ''">
                                {{ service.title }}
                            </h3>
                            <p class="text-gray-600 mb-6 leading-relaxed">
                                {{ service.description }}
                            </p>
                            <!-- Features List -->
                            <div class="space-y-3 mt-4 transition-all duration-500 ease-in-out overflow-hidden"
                                :class="selectedService === index ? 'max-h-40' : 'max-h-0'">
                                <div v-for="(feature, fIndex) in service.features" :key="fIndex"
                                    class="flex items-center space-x-3 text-gray-700">
                                    <CheckCircle class="w-5 h-5 text-orange-500 flex-shrink-0" />
                                    <span>{{ feature }}</span>
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- CTA Section -->
                    <section class="text-center bg-white p-12 rounded-xl shadow-lg transform transition-all duration-1000"
                        :class="{ 'opacity-100 translate-y-0': isVisible, 'opacity-0 translate-y-4': !isVisible }"
                        style="transition-delay: 800ms">
                        <h2 class="text-3xl font-bold text-gray-800">
                            Metninizi Profesyonellerle İyileştirin
                        </h2>
                        <p class="text-lg text-gray-600 max-w-2xl mx-auto mt-4 mb-8">
                            Tashih hizmetlerimiz hakkında detaylı bilgi almak ve fiyat teklifi için bizimle iletişime geçin.
                        </p>
                        <Link href="/iletisim" class="inline-block group relative rounded-full bg-orange-500 px-8 py-3 text-lg text-white font-semibold shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
                            <span class="relative z-10 flex items-center justify-center space-x-2">
                                <span>İletişime Geçin</span>
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 transition-transform duration-300 group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                </svg>
                            </span>
                        </Link>
                    </section>
                </div>
            </main>
        </div>
    </GuestLayout>
</template>

<style scoped>
.hover\:scale-102:hover {
    transform: scale(1.02);
}
</style>