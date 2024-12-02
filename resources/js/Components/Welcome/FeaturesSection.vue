<script setup>
import { ref, onMounted } from 'vue';

const isVisible = ref(false);

onMounted(() => {
    const observer = new IntersectionObserver(
        (entries) => {
            if (entries[0].isIntersecting) {
                isVisible.value = true;
            }
        },
        { threshold: 0.1 }
    );

    const element = document.querySelector('#features-section');
    if (element) observer.observe(element);
});

const features = [
    {
        icon: `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"></path><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"></path></svg>`,
        title: 'Profesyonel Düzenleme',
        description: 'Deneyimli editörlerimiz metninizi detaylı bir şekilde inceler ve geliştirmeler önerir.'
    },
    {
        icon: `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M22 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>`,
        title: 'Uzman Kadro',
        description: 'Alanında uzman akademisyen ve editörlerden oluşan geniş kadromuzla hizmetinizdeyiz.'
    },
    {
        icon: `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>`,
        title: 'Hızlı Teslimat',
        description: 'Projelerinizi belirlenen sürede, kaliteden ödün vermeden teslim ediyoruz.'
    },
    {
        icon: `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="7"></circle><polyline points="8.21 13.89 7 23 12 20 17 23 15.79 13.88"></polyline></svg>`,
        title: 'Kalite Garantisi',
        description: 'Memnuniyet odaklı çalışma prensibiyle en iyi hizmeti sunuyoruz.'
    }
];
</script>

<template>
    <section id="features-section" class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl font-bold text-navy-900 mb-4"
                    :class="{ 'opacity-100 translate-y-0': isVisible, 'opacity-0 translate-y-4': !isVisible }"
                    style="transition: all 0.8s cubic-bezier(0.4, 0, 0.2, 1)">
                    Neden Bizi Tercih Etmelisiniz?
                </h2>
                <p class="text-lg text-gray-600 max-w-2xl mx-auto"
                    :class="{ 'opacity-100 translate-y-0': isVisible, 'opacity-0 translate-y-4': !isVisible }"
                    style="transition: all 0.8s cubic-bezier(0.4, 0, 0.2, 1) 0.2s">
                    Kaliteli ve profesyonel hizmet anlayışımızla projelerinizi en iyi şekilde tamamlıyoruz.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                <div v-for="(feature, index) in features" :key="index"
                    class="p-6 bg-white rounded-lg shadow-lg hover:shadow-xl transition-all duration-300"
                    :class="{ 'opacity-100 translate-y-0': isVisible, 'opacity-0 translate-y-4': !isVisible }"
                    :style="`transition: all 0.8s cubic-bezier(0.4, 0, 0.2, 1) ${0.2 + index * 0.1}s`">
                    <div class="w-12 h-12 text-orange-500 mb-4" v-html="feature.icon"></div>
                    <h3 class="text-xl font-semibold text-navy-900 mb-2">{{ feature.title }}</h3>
                    <p class="text-gray-600">{{ feature.description }}</p>
                </div>
            </div>
        </div>
    </section>
</template>