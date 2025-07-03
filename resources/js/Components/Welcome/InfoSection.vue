<script setup>
import { Link } from '@inertiajs/vue3';
import { CheckCircle, Zap, BookOpen, Award } from 'lucide-vue-next';

const features = [
    {
        title: 'Güvenilirlik ve Ciddiyet Kazandırır',
        description: 'Hatalarla dolu bir metin, yazarının veya kurumun imajını zedeler. Tashih, metne ciddiyet kazandırarak okuyucunun gözünde güven oluşturur.',
        icon: Award
    },
    {
        title: 'Anlamı Güçlendirir',
        description: 'Anlatım bozuklukları ve mantık hataları giderilerek metnin yazarının niyetiyle tam olarak örtüşmesi sağlanır.',
        icon: BookOpen
    },
    {
        title: 'Okunabilirliği Artırır',
        description: 'Akıcı ve sade bir dil, okuyucunun metinden kopmasını engeller. Tashih, metnin sonuna kadar rahatça okunmasını sağlar.',
        icon: Zap
    },
    {
        title: 'Profesyonel Bir İmaj Sunar',
        description: 'İster bir tez, ister bir iş raporu olsun, hatasız bir metin sunmak profesyonelliğin ilk ve en önemli adımıdır.',
        icon: CheckCircle
    }
];
</script>

<template>
    <section id="why-proofreading" class="py-24 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl font-extrabold tracking-tight text-gray-800 sm:text-4xl">
                    Tashih Neden Önemlidir?
                </h2>
                <p class="mt-4 text-lg leading-relaxed text-gray-600 max-w-3xl mx-auto">
                    Bir metni yazmak ne kadar ustalık gerektiriyorsa, onu kusursuz hale getirmek de o kadar
                    profesyonellik ister. En güçlü fikirler bile, küçük bir harf hatası veya yanlış yerleştirilmiş bir
                    virgül yüzünden gölgelenebilir.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-10 md:grid-cols-2 lg:gap-12">
                <div v-for="feature in features" :key="feature.title"
                    class="relative p-6 bg-stone-50 rounded-xl transition duration-300 hover:shadow-xl hover:scale-105">
                    <div class="flex items-start space-x-4">
                        <div class="flex-shrink-0">
                            <div class="flex items-center justify-center w-12 h-12 rounded-lg bg-orange-500 text-white">
                                <component :is="feature.icon" class="w-6 h-6" />
                            </div>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-xl font-semibold text-gray-800 mb-2">
                                {{ feature.title }}
                            </h3>
                            <p class="text-gray-600 leading-relaxed">
                                {{ feature.description }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-20 text-center">
                <blockquote
                    class="text-2xl font-serif italic text-gray-700 border-l-4 border-orange-400 pl-6 inline-block">
                    Unutmayın; tashih, metninizi sadece anlatmaz, onu <strong>anlatılabilir</strong> hâle getirir.
                </blockquote>
            </div>
        </div>
    </section>
</template>
