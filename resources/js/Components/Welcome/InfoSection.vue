<script setup>
import { Link } from '@inertiajs/vue3';

const features = [
    {
        title: 'Profesyonel Düzeltme',
        description: 'Deneyimli editörlerimiz metninizi detaylı bir şekilde inceleyerek, dilbilgisi, yazım ve noktalama hatalarını düzeltir.',
        icon: 'M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10'
    },
    {
        title: 'Akademik Tashih',
        description: 'Tez, makale ve akademik yayınlarınız için özel olarak tasarlanmış tashih hizmetleri sunuyoruz.',
        icon: 'M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342M6.75 15a.75.75 0 100-1.5.75.75 0 000 1.5zm0 0v-3.675A55.378 55.378 0 0112 8.443m-7.007 11.55A5.981 5.981 0 006.75 15.75v-1.5'
    },
    {
        title: 'Hızlı Teslimat',
        description: 'Acil işleriniz için süratli tashih hizmeti sağlıyoruz. İhtiyacınıza göre 24-48 saat içinde teslim ediyoruz.',
        icon: 'M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z'
    },
    {
        title: 'Çok Dilli Destek',
        description: 'Türkçe, İngilizce ve diğer dillerde profesyonel tashih hizmetleri sunuyoruz.',
        icon: 'M10.5 21l5.25-11.25L21 21m-9-3h7.5M3 5.621a48.474 48.474 0 016-.371m0 0c1.12 0 2.233.038 3.334.114M9 5.25V3m3.334 2.364C11.176 10.658 7.69 15.08 3 17.502m9.334-12.138c.896.061 1.785.147 2.666.257m-4.589 8.495a18.023 18.023 0 01-3.827-5.802'
    }
];
</script>

<template>
    <section class="py-24 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl font-bold tracking-tight text-navy-900 sm:text-4xl">
                    Neden bizi seçmelisiniz?
                </h2>
                <p class="mt-4 text-lg leading-relaxed text-navy-600 max-w-3xl mx-auto">
                    Tashih işine 1994 yılında başladık ve o günden bugüne ulusal gazeteler, çeşitli yayınevlerine ait
                    kitap ve dergilerde hizmet vermeye devam ediyoruz.
                </p>
                <p class="mt-4 text-lg leading-relaxed text-navy-600 max-w-3xl mx-auto">
                    Beklentilerinizi en iyi şekilde anlamak ve iş sürecinde sürekli iletişimde kalarak tam
                    memnuniyetinizi en üst düzeyde sağlamak için güvence veriyoruz.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-8 md:grid-cols-2 lg:gap-12">
                <div v-for="feature in features" :key="feature.title"
                    class="relative p-6 bg-green-50 rounded-lg transition duration-300 hover:shadow-lg">
                    <div class="flex items-start space-x-4">
                        <div class="flex-shrink-0">
                            <div class="flex items-center justify-center w-12 h-12 rounded-md bg-orange-500 text-white">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" :d="feature.icon" />
                                </svg>
                            </div>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-xl font-semibold text-navy-900 mb-2">
                                {{ feature.title }}
                            </h3>
                            <p class="text-navy-600 leading-relaxed">
                                {{ feature.description }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-16 text-center">
                <Link href="/hizmetlerimiz"
                    class="inline-flex items-center rounded-md bg-orange-500 px-6 py-3 text-base font-semibold text-white shadow-sm hover:bg-orange-600 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-orange-400 transition-colors duration-200">
                Tüm Hizmetlerimizi İnceleyin
                <svg class="ml-2 -mr-1 w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                </svg>
                </Link>
            </div>
        </div>
    </section>
</template>