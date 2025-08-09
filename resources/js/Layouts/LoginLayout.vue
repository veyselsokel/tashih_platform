<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="min-h-screen flex items-center justify-center bg-stone-100 p-4 sm:p-6 lg:p-8">
        <div class="w-full max-w-5xl mx-auto bg-white rounded-2xl shadow-2xl flex flex-col md:flex-row overflow-hidden">
            <!-- Left Side -->
            <div class="w-full md:w-1/2 p-8 md:p-12 flex flex-col justify-center items-center text-center bg-orange-500 text-white">
                <Link href="/">
                    <ApplicationLogo class="w-28 h-28 text-white fill-white mb-6" />
                </Link>
                <h2 class="text-3xl font-bold mb-4">Tashih Platformuna Hoş Geldiniz</h2>
                <p class="mb-8 leading-relaxed">Metinlerinizi kusursuzlaştırın, profesyonel bir dokunuşla fark yaratın.</p>
                <Link href="/" class="border-2 border-white/50 hover:bg-white/20 text-white font-bold py-2 px-6 rounded-full transition duration-300">
                    Ana Sayfaya Dön
                </Link>
            </div>

            <!-- Right Side (Form) -->
            <div class="w-full md:w-1/2 p-8 md:p-12 flex flex-col justify-center">
                <slot />
            </div>
        </div>
    </div>
</template>