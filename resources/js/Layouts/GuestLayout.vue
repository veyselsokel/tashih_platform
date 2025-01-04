<script setup>
import { ref, watch } from 'vue';
import { Head } from '@inertiajs/vue3';
import NavBar from '@/Components/NavBar.vue';
import Footer from '@/Components/Footer.vue';

const props = defineProps({
    title: {
        type: String,
        required: true
    },
    canLogin: {
        type: Boolean,
        default: false
    },
    canRegister: {
        type: Boolean,
        default: false
    }
});

// Get scroll width to prevent content shift when modal opens
const scrollbarWidth = ref(0);
watch(() => window.innerWidth, () => {
    scrollbarWidth.value = window.innerWidth - document.documentElement.clientWidth;
}, { immediate: true });
</script>

<template>
    <div class="min-h-screen bg-gray-50 flex flex-col">

        <Head :title="title" />

        <!-- Navbar -->
        <NavBar :can-login="canLogin" :can-register="canRegister" />

        <!-- Main Content -->
        <main class="flex-grow">
            <slot />
        </main>

        <!-- Footer -->
        <Footer />
    </div>
</template>

<style scoped>
/* Prevent content shift when scrollbar appears/disappears */
:root {
    scrollbar-gutter: stable;
}

/* Ensure smooth scrolling */
html {
    scroll-behavior: smooth;
}

/* Hide scrollbar for Chrome, Safari and Opera */
.no-scrollbar::-webkit-scrollbar {
    display: none;
}

/* Hide scrollbar for IE, Edge and Firefox */
.no-scrollbar {
    -ms-overflow-style: none;
    /* IE and Edge */
    scrollbar-width: none;
    /* Firefox */
}
</style>