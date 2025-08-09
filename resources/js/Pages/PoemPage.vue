<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import PoemSection from '@/Components/Poem/PoemSection.vue';

defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
});
</script>

<template>
    <GuestLayout title="Şiirler">
        <div class="bg-stone-50 min-h-screen">
             <header class="relative text-center px-6 py-24 md:py-32">
                 <div class="max-w-4xl mx-auto">
                    <h1 class="text-4xl md:text-5xl font-extrabold text-gray-800 leading-tight mb-4">
                        Şiirler
                    </h1>
                    <p class="text-lg text-gray-600 max-w-3xl mx-auto">
                        Kalemimizden dökülenler.
                    </p>
                </div>
            </header>
            <main class="pb-24">
                <PoemSection />
            </main>
        </div>
    </GuestLayout>
</template>