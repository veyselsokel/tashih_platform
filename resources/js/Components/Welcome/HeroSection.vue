<script setup>
import { ref, onMounted } from 'vue';
import { Link } from '@inertiajs/vue3';
import heroImage from '../../../assets/hero-section-image.jpg';

const isVisible = ref(false);
onMounted(() => {
    setTimeout(() => {
        isVisible.value = true;
    }, 100);
});
</script>
<template>
    <section class="relative min-h-screen flex items-center justify-center text-center overflow-hidden bg-stone-100">
        <!-- Background Image and Overlay -->
        <div class="absolute inset-0 z-0">
            <img :src="heroImage" alt="Profesyonel tashih hizmeti için bir masaüstü çalışma alanı"
                class="w-full h-full object-cover opacity-30" />
            <div class="absolute inset-0 bg-gradient-to-b from-stone-10 via-stone-50 to-stone-100"></div>
        </div>

        <!-- Content -->
        <div class="relative z-10 container mx-auto px-4 py-24">
            <div class="max-w-4xl mx-auto">
                <transition enter-active-class="transition ease-out duration-1000"
                    enter-from-class="opacity-0 translate-y-4" enter-to-class="opacity-100 translate-y-0">
                    <h1 v-if="isVisible" class="text-4xl md:text-6xl font-extrabold text-gray-800 leading-tight mb-6">
                        Tashih Nedir? <span class="block text-orange-500">Metinlerinizi
                            Kusursuzlaştıran
                            Profesyonel
                            Dokunuş</span>
                    </h1>
                </transition>
                <transition enter-active-class="transition ease-out duration-1000 delay-300"
                    enter-from-class="opacity-0 translate-y-4" enter-to-class="opacity-100 translate-y-0">
                    <p v-if="isVisible" class="text-lg md:text-xl text-gray-600 mb-8 leading-relaxed">
                        Tashih, bir metni yazım hataları, noktalama eksikleri ve anlatım bozukluklarından arındırarak
                        ona akıcılık, güvenilirlik ve profesyonellik kazandırma sanatıdır.
                    </p>
                </transition>
                <transition enter-active-class="transition ease-out duration-1000 delay-500"
                    enter-from-class="opacity-0 translate-y-4" enter-to-class="opacity-100 translate-y-0">
                    <p v-if="isVisible" class="text-2xl md:text-xl text-gray-700 font-semibold italic mb-12">
                        1994’ten bugüne gelen bilgi, birikim ve tecrübeyle metinlerinize değer katıyoruz.
                    </p>
                </transition>
                <transition enter-active-class="transition ease-out duration-1000 delay-700"
                    enter-from-class="opacity-0 scale-90" enter-to-class="opacity-100 scale-100">
                    <div v-if="isVisible" class="flex flex-col sm:flex-row items-center justify-center gap-4">
                        <Link href="#services"
                            class="w-full sm:w-auto bg-orange-500 hover:bg-orange-600 text-white font-bold py-3 px-8 rounded-full transition duration-300 transform hover:scale-105">
                        Hizmetlerimizi Keşfedin
                        </Link>
                        <Link href="#contact"
                            class="w-full sm:w-auto bg-white hover:bg-gray-100 text-gray-800 font-bold py-3 px-8 rounded-full transition duration-300 border border-gray-200 shadow-sm">
                        Bize Ulaşın
                        </Link>
                    </div>
                </transition>
            </div>
        </div>
    </section>
</template>
<style scoped></style>
