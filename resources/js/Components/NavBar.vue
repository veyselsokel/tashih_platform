<script setup>
import { ref, onMounted, computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    canLogin: {
        type: Boolean,
        default: true
    },
    canRegister: {
        type: Boolean,
        default: true
    }
});

const isMenuOpen = ref(false);
const isScrolled = ref(false);
const previousScrollPosition = ref(0);
const isNavVisible = ref(true);

const navigationItems = [
    'İletişim',
    'Blog',
    'Hakkımızda',
    'Hizmetlerimiz',
    'Bulmacalar',
];

const turkishToEnglish = (text) => {
    return text
        .toLowerCase()
        .replace(/i̇/g, 'i')
        .replace(/ğ/g, 'g')
        .replace(/ü/g, 'u')
        .replace(/ş/g, 's')
        .replace(/ı/g, 'i')
        .replace(/ö/g, 'o')
        .replace(/ç/g, 'c')
        .replace(/\s+/g, '-');
};

const navClasses = computed(() => {
    return {
        'transform -translate-y-full': !isNavVisible.value && isScrolled.value,
        'bg-white/80 backdrop-blur-lg shadow-lg': isScrolled.value,
        'bg-transparent': !isScrolled.value,
        'shadow-none': !isScrolled.value
    }
    console.log('deneme');
});

const handleScroll = () => {
    const currentScrollPosition = window.scrollY;
    isScrolled.value = currentScrollPosition > 50;

    if (currentScrollPosition < 0) return;
    if (Math.abs(currentScrollPosition - previousScrollPosition.value) < 50) return;

    isNavVisible.value = previousScrollPosition.value > currentScrollPosition || currentScrollPosition < 50;
    previousScrollPosition.value = currentScrollPosition;
};

onMounted(() => {
    window.addEventListener('scroll', handleScroll);
    return () => window.removeEventListener('scroll', handleScroll);
});

const toggleMenu = () => {
    isMenuOpen.value = !isMenuOpen.value;
};

// Close mobile menu when window is resized to desktop view
const handleResize = () => {
    if (window.innerWidth >= 768 && isMenuOpen.value) {
        isMenuOpen.value = false;
    }
};

onMounted(() => {
    window.addEventListener('resize', handleResize);
    return () => window.removeEventListener('resize', handleResize);
});
</script>

<template>
    <nav :class="[
        'fixed w-full z-50 transition-all duration-300 ease-in-out',
        navClasses
    ]">
        <div class="container mx-auto px-3 sm:px-4 lg:px-6 xl:px-8">
            <div class="flex items-center justify-between h-14 sm:h-16 lg:h-20">
                <!-- Logo -->
                <Link href="/"
                    class="group relative text-lg sm:text-xl lg:text-2xl font-bold text-navy-600 hover:text-orange-500 transition-all duration-300">
                <span class="whitespace-nowrap">Tashih Hizmetleri</span>
                <span
                    class="absolute bottom-0 left-0 w-full h-0.5 bg-orange-500 transform scale-x-0 transition-transform duration-300 origin-left group-hover:scale-x-100"></span>
                </Link>

                <!-- Desktop Navigation -->
                <div class="hidden md:flex items-center space-x-2 lg:space-x-4 xl:space-x-6">
                    <Link v-for="item in navigationItems" :key="item" :href="'/' + turkishToEnglish(item)"
                        class="group relative px-2 lg:px-3 py-2 text-sm lg:text-base font-medium">
                    <span class="relative z-10 text-navy-600 transition-colors duration-300">
                        {{ item }}
                    </span>
                    <span
                        class="absolute bottom-0 left-0 w-full h-0.5 bg-orange-500 transform scale-x-0 transition-transform duration-300 origin-left group-hover:scale-x-100"></span>
                    </Link>

                    <!-- Auth Buttons -->
                    <div v-if="canLogin" class="flex items-center space-x-2 lg:space-x-3">
                        <Link v-if="$page.props.auth.user" :href="route('dashboard')"
                            class="relative overflow-hidden bg-orange-500 text-white px-3 lg:px-4 py-1.5 lg:py-2 rounded text-sm lg:text-base group">
                        <span class="relative z-10">Panel</span>
                        <div
                            class="absolute inset-0 h-full w-full bg-orange-600 transform scale-x-0 origin-left transition-transform duration-300 group-hover:scale-x-100">
                        </div>
                        </Link>
                        <template v-else>
                            <Link :href="route('login')"
                                class="group relative px-2 lg:px-3 py-1.5 lg:py-2 text-sm lg:text-base font-medium">
                            <span class="relative z-10 text-navy-600 transition-colors duration-300">Giriş Yap</span>
                            <span
                                class="absolute bottom-0 left-0 w-full h-0.5 bg-orange-500 transform scale-x-0 transition-transform duration-300 origin-left group-hover:scale-x-100"></span>
                            </Link>
                            <Link v-if="canRegister" :href="route('register')"
                                class="relative overflow-hidden bg-orange-500 text-white px-3 lg:px-4 py-1.5 lg:py-2 rounded text-sm lg:text-base group">
                            <span class="relative z-10">Kayıt Ol</span>
                            <div
                                class="absolute inset-0 h-full w-full bg-orange-600 transform scale-x-0 origin-left transition-transform duration-300 group-hover:scale-x-100">
                            </div>
                            </Link>
                        </template>
                    </div>
                </div>

                <!-- Mobile menu button -->
                <button @click="toggleMenu"
                    class="md:hidden relative w-10 h-10 focus:outline-none hover:bg-gray-100 rounded-full transition-colors duration-200"
                    :aria-expanded="isMenuOpen">
                    <div class="block w-5 absolute left-1/2 top-1/2 transform -translate-x-1/2 -translate-y-1/2">
                        <span :class="[
                            'block absolute h-0.5 w-5 bg-current transform transition duration-300 ease-in-out',
                            { 'rotate-45': isMenuOpen, '-translate-y-1.5': !isMenuOpen }
                        ]"></span>
                        <span :class="[
                            'block absolute h-0.5 w-5 bg-current transform transition duration-300 ease-in-out',
                            { 'opacity-0': isMenuOpen }
                        ]"></span>
                        <span :class="[
                            'block absolute h-0.5 w-5 bg-current transform transition duration-300 ease-in-out',
                            { '-rotate-45': isMenuOpen, 'translate-y-1.5': !isMenuOpen }
                        ]"></span>
                    </div>
                </button>
            </div>
        </div>

        <!-- Mobile menu -->
        <div :class="[
            'md:hidden fixed left-0 right-0 transition-all duration-300 ease-in-out transform',
            isMenuOpen ? 'translate-y-0 opacity-100' : '-translate-y-10 opacity-0 pointer-events-none'
        ]">
            <div class="px-3 pt-2 pb-3 space-y-1 bg-white/90 backdrop-blur-lg shadow-lg border-t border-gray-100">
                <Link v-for="item in navigationItems" :key="item" :href="'/' + turkishToEnglish(item)"
                    class="block px-3 py-2.5 rounded-lg text-base font-medium text-navy-600 hover:text-orange-500 hover:bg-orange-50 transition-all duration-300"
                    @click="isMenuOpen = false">
                {{ item }}
                </Link>

                <!-- Mobile Auth Buttons -->
                <div v-if="canLogin" class="mt-4 px-3 space-y-2.5">
                    <Link v-if="$page.props.auth.user" :href="route('dashboard')"
                        class="relative overflow-hidden block w-full text-center bg-orange-500 text-white px-4 py-2.5 rounded-lg group"
                        @click="isMenuOpen = false">
                    <span class="relative z-10">Panel</span>
                    <div
                        class="absolute inset-0 h-full w-full bg-orange-600 transform scale-x-0 origin-left transition-transform duration-300 group-hover:scale-x-100">
                    </div>
                    </Link>
                    <template v-else>
                        <Link :href="route('login')"
                            class="block w-full text-center px-4 py-2.5 rounded-lg text-navy-600 hover:text-orange-500 hover:bg-orange-50 transition-colors duration-300 text-base font-medium"
                            @click="isMenuOpen = false">
                        Giriş Yap
                        </Link>
                        <Link v-if="canRegister" :href="route('register')"
                            class="relative overflow-hidden block w-full text-center bg-orange-500 text-white px-4 py-2.5 rounded-lg group"
                            @click="isMenuOpen = false">
                        <span class="relative z-10">Kayıt Ol</span>
                        <div
                            class="absolute inset-0 h-full w-full bg-orange-600 transform scale-x-0 origin-left transition-transform duration-300 group-hover:scale-x-100">
                        </div>
                        </Link>
                    </template>
                </div>
            </div>
        </div>
    </nav>
</template>

<style scoped>
.backdrop-blur-lg {
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
}

html {
    scrollbar-gutter: stable;
}
</style>