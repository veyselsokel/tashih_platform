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

    const element = document.querySelector('#portfolio-section');
    if (element) observer.observe(element);
});

const portfolioItems = [
    {
        icon: `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1 0-5H20"></path></svg>`,
        title: 'Akademik Kitaplar',
        description: 'Çeşitli akademik yayınevleri için tashih hizmeti verdiğimiz kitaplar.',
        count: '250+'
    },
    {
        icon: `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"></path><polyline points="14 2 14 8 20 8"></polyline></svg>`,
        title: 'Akademik Makaleler',
        description: 'Ulusal ve uluslararası dergilerde yayınlanan makaleler için sağladığımız düzenleme hizmetleri.',
        count: '1000+'
    },
    {
        icon: `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"></path><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"></path></svg>`,
        title: 'Edebi Eserler',
        description: 'Roman, hikaye ve şiir kitapları için gerçekleştirdiğimiz profesyonel düzenleme çalışmaları.',
        count: '150+'
    },
    {
        icon: `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="7" width="20" height="15" rx="2" ry="2"></rect><polyline points="17 2 12 7 7 2"></polyline></svg>`,
        title: 'Kurumsal Projeler',
        description: 'Şirketler ve kurumlar için hazırladığımız raporlar ve kurumsal dokümanlar.',
        count: '300+'
    }
];
</script>

<template>
    <section id="portfolio-section" class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl font-bold text-navy-900 mb-4"
                    :class="{ 'opacity-100 translate-y-0': isVisible, 'opacity-0 translate-y-4': !isVisible }"
                    style="transition: all 0.8s cubic-bezier(0.4, 0, 0.2, 1)">
                    Portföyümüz
                </h2>
                <p class="text-lg text-gray-600 max-w-2xl mx-auto"
                    :class="{ 'opacity-100 translate-y-0': isVisible, 'opacity-0 translate-y-4': !isVisible }"
                    style="transition: all 0.8s cubic-bezier(0.4, 0, 0.2, 1) 0.2s">
                    Bugüne kadar tamamladığımız projelerden bazı örnekler
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                <div v-for="(item, index) in portfolioItems" :key="index"
                    class="p-6 bg-white rounded-lg shadow-lg hover:shadow-xl transition-all duration-300"
                    :class="{ 'opacity-100 translate-y-0': isVisible, 'opacity-0 translate-y-4': !isVisible }"
                    :style="`transition: all 0.8s cubic-bezier(0.4, 0, 0.2, 1) ${0.2 + index * 0.1}s`">
                    <div class="w-12 h-12 text-orange-500 mb-4" v-html="item.icon"></div>
                    <div class="flex items-center justify-between mb-2">
                        <h3 class="text-xl font-semibold text-navy-900">{{ item.title }}</h3>
                        <!-- <span class="text-sm font-bold text-orange-500">{{ item.count }}</span> -->
                    </div>
                    <p class="text-gray-600">{{ item.description }}</p>
                </div>
            </div>
        </div>
    </section>
</template>