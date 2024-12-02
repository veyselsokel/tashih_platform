<script setup>
import { ref, onMounted } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import NavBar from '@/Components/Welcome/NavBar.vue';
import HeroSection from '@/Components/Welcome/HeroSection.vue';
import InfoSection from '@/Components/Welcome/InfoSection.vue';
import FeaturesSection from '@/Components/Welcome/FeaturesSection.vue';
import TestimonialsSection from '@/Components/Welcome/TestimonialsSection.vue';
import ContactSection from '@/Components/Welcome/ContactSection.vue';

defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
});

const isScrolled = ref(false);

onMounted(() => {
    window.addEventListener('scroll', handleScroll);
});

const handleScroll = () => {
    isScrolled.value = window.scrollY > 50;
};
</script>

<template>

    <Head title="Hoş Geldiniz" />

    <div class="min-h-screen bg-green-50">
        <!-- Navbar -->
        <NavBar :can-login="canLogin" :can-register="canRegister" />

        <!-- Hero Section -->
        <HeroSection />

        <!-- Info Section -->
        <InfoSection />

        <!-- Features Section -->
        <FeaturesSection />

        <!-- Testimonials Section -->
        <TestimonialsSection />

        <!-- Contact Section -->
        <ContactSection />
    </div>
</template>