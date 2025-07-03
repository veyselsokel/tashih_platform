<script setup>
import { ref, onMounted } from 'vue';
import HeroSection from '@/Components/Welcome/HeroSection.vue';
import InfoSection from '@/Components/Welcome/InfoSection.vue';
import FeaturesSection from '@/Components/Welcome/FeaturesSection.vue';
import ContactSection from '@/Components/Welcome/ContactSection.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import ContentComponent from '@/Components/Content/ContentComponent.vue';

const props = defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
});

const isScrolled = ref(false);

onMounted(() => {
    window.addEventListener('scroll', handleScroll);
    return () => window.removeEventListener('scroll', handleScroll);
});

const handleScroll = () => {
    isScrolled.value = window.scrollY > 50;
};
</script>

<template>
    <GuestLayout title="Tashih Nedir? Profesyonel Metin Düzeltme Hizmetleri">
        <HeroSection />
        <InfoSection />
        <ContentComponent />
        <FeaturesSection id="services" />
        <ContactSection id="contact" />
    </GuestLayout>
</template>