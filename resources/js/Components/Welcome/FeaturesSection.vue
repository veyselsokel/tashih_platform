<script setup>
import { ref, onMounted } from 'vue';
import { BookCheck, ShieldCheck } from 'lucide-vue-next';

const isVisible = ref(false);
const servicesSection = ref(null);

onMounted(() => {
    const observer = new IntersectionObserver(
        (entries) => {
            if (entries[0].isIntersecting) {
                isVisible.value = true;
                observer.unobserve(entries[0].target);
            }
        },
        { threshold: 0.1 }
    );

    if (servicesSection.value) {
        observer.observe(servicesSection.value);
    }
});

const services = [
    {
        icon: BookCheck,
        title: 'Tez ve Makale Düzeltmesi (Akademik Tashih)',
        description: 'Akademik metinler, en yüksek düzeyde doğruluk ve profesyonellik gerektirir. Tez düzeltmesi ve makale düzeltmesi hizmetlerimizle, bilimsel çalışmalarınızın yazım, dil bilgisi, üslup ve format açısından kusursuz hale gelmesini sağlıyoruz.',
    },
    {
        icon: ShieldCheck,
        title: 'İntihal Giderme ve Özgünlük Kontrolü',
        description: 'Akademik ve dijital dünyada özgünlük esastır. Metinlerinizdeki intihal (aşırma) oranlarını tespit ediyor, anlamı ve bütünlüğü koruyarak gerekli yeniden yazım (paraphrasing) işlemlerini yaparak intihal giderme hizmeti sunuyoruz.',
    }
];
</script>

<template>
    <section id="services-section" ref="servicesSection" class="py-24 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl font-extrabold text-gray-800 mb-4"
                    :class="{ 'opacity-100 translate-y-0': isVisible, 'opacity-0 translate-y-4': !isVisible }"
                    style="transition: all 0.8s cubic-bezier(0.4, 0, 0.2, 1)">
                    Hizmetlerimiz
                </h2>
                <p class="text-lg text-gray-600 max-w-2xl mx-auto"
                    :class="{ 'opacity-100 translate-y-0': isVisible, 'opacity-0 translate-y-4': !isVisible }"
                    style="transition: all 0.8s cubic-bezier(0.4, 0, 0.2, 1) 0.2s">
                    Metinlerinizin türü ve ihtiyacı ne olursa olsun, profesyonel çözümler sunuyoruz.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
                <div v-for="(service, index) in services" :key="index"
                    class="p-8 bg-stone-50 rounded-xl shadow-lg hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-2"
                    :class="{ 'opacity-100 translate-y-0': isVisible, 'opacity-0 translate-y-4': !isVisible }"
                    :style="`transition: all 0.8s cubic-bezier(0.4, 0, 0.2, 1) ${0.2 + index * 0.1}s`">
                    <div class="w-16 h-16 text-white bg-orange-500 rounded-full flex items-center justify-center mb-6 shadow-md">
                        <component :is="service.icon" class="w-8 h-8" />
                    </div>
                    <h3 class="text-2xl font-semibold text-gray-800 mb-4">{{ service.title }}</h3>
                    <p class="text-gray-600 leading-relaxed">{{ service.description }}</p>
                </div>
            </div>
        </div>
    </section>
</template>