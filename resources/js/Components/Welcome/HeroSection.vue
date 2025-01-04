<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue';
import { MoveDown } from 'lucide-vue-next';

// Constants for configuration
const ANIMATION_SETTINGS = {
    SMOOTH_FACTOR: 0.1,
    MOUSE_SENSITIVITY: 0.01,
    THROTTLE_MS: 16, // 60fps için
    VISIBILITY_DELAY: 100
};

const CIRCLE_SETTINGS = {
    COUNT: 4,
    BASE_SIZE: 150,
    SIZE_INCREMENT: 50,
    BASE_LEFT: 10,
    LEFT_INCREMENT: 20,
    BASE_TOP: 20,
    TOP_INCREMENT: 15
};

// State management
const isVisible = ref(false);
const mousePosition = ref({ x: 0, y: 0 });
const targetMousePosition = ref({ x: 0, y: 0 });
const scrollPosition = ref(0);
let animationFrame = null;
let mouseMoveTimeout = null;

// Smooth animation helper
const lerp = (start, end, factor) => start + (end - start) * factor;

// Performance optimized mouse position update
const updateMousePosition = () => {
    mousePosition.value = {
        x: lerp(mousePosition.value.x, targetMousePosition.value.x, ANIMATION_SETTINGS.SMOOTH_FACTOR),
        y: lerp(mousePosition.value.y, targetMousePosition.value.y, ANIMATION_SETTINGS.SMOOTH_FACTOR)
    };
    animationFrame = requestAnimationFrame(updateMousePosition);
};

// Throttled mouse move handler
const handleMouseMove = (e) => {
    if (mouseMoveTimeout) return;

    mouseMoveTimeout = setTimeout(() => {
        targetMousePosition.value = {
            x: (e.clientX - window.innerWidth / 2) * ANIMATION_SETTINGS.MOUSE_SENSITIVITY,
            y: (e.clientY - window.innerHeight / 2) * ANIMATION_SETTINGS.MOUSE_SENSITIVITY
        };
        mouseMoveTimeout = null;
    }, ANIMATION_SETTINGS.THROTTLE_MS);
};

// Scroll handler with optimization
const handleScroll = () => {
    scrollPosition.value = window.scrollY;
};

// Computed circles with optimized calculations
const circles = computed(() =>
    Array.from({ length: CIRCLE_SETTINGS.COUNT }, (_, i) => ({
        size: `${CIRCLE_SETTINGS.BASE_SIZE + i * CIRCLE_SETTINGS.SIZE_INCREMENT}px`,
        left: `${CIRCLE_SETTINGS.BASE_LEFT + i * CIRCLE_SETTINGS.LEFT_INCREMENT}%`,
        top: `${CIRCLE_SETTINGS.BASE_TOP + i * CIRCLE_SETTINGS.TOP_INCREMENT}%`,
        color: i % 2 ? 'from-orange-500/20' : 'from-green-700/20',
        transform: `translate(
            ${mousePosition.value.x * (i + 1)}px, 
            ${mousePosition.value.y * (i + 1) - scrollPosition.value * 0.1 * (i + 1)}px
        )`
    }))
);

// Lifecycle hooks
onMounted(() => {
    setTimeout(() => {
        isVisible.value = true;
    }, ANIMATION_SETTINGS.VISIBILITY_DELAY);

    window.addEventListener('mousemove', handleMouseMove);
    window.addEventListener('scroll', handleScroll);
    updateMousePosition();
});

onUnmounted(() => {
    window.removeEventListener('mousemove', handleMouseMove);
    window.removeEventListener('scroll', handleScroll);
    if (animationFrame) cancelAnimationFrame(animationFrame);
    if (mouseMoveTimeout) clearTimeout(mouseMoveTimeout);
});
</script>

<template>
    <div class="relative min-h-screen overflow-hidden bg-gradient-to-b from-green-50 to-white">
        <!-- Background Circles -->
        <div class="absolute inset-0">
            <div v-for="(circle, i) in circles" :key="i"
                class="absolute rounded-full mix-blend-multiply blur-xl transition-transform"
                :class="`bg-gradient-radial ${circle.color} to-transparent`" :style="{
                    width: circle.size,
                    height: circle.size,
                    left: circle.left,
                    top: circle.top,
                    transform: circle.transform
                }">
            </div>
        </div>

        <!-- Main Content -->
        <div class="relative min-h-screen flex items-center justify-center px-6">
            <div class="text-center space-y-8">
                <!-- Animated Heading -->
                <h1 class="text-3xl sm:text-5xl font-bold tracking-tight leading-tight transition-all duration-800 ease-out"
                    :class="{ 'opacity-100 translate-y-0': isVisible, 'opacity-0 translate-y-4': !isVisible }">
                    <span class="relative inline-block">
                        <VueTyper class="typer-text" text="TASHİH HİZMETLERİNE" erase-style="backspace"
                            erase-delay="70" />
                        <div class="absolute bottom-0 left-0 w-full h-1 bg-orange-500 transform origin-left transition-transform duration-500"
                            :class="{ 'scale-x-100': isVisible, 'scale-x-0': !isVisible }">
                        </div>
                    </span>
                    <br />
                    <span class="relative inline-block text-orange-500 mt-4">HOŞ GELDİNİZ</span>
                </h1>

                <!-- Animated Description -->
                <p class="mt-4 text-lg text-navy-600 max-w-2xl mx-auto leading-relaxed transition-all duration-800"
                    :class="{ 'opacity-100 translate-y-0': isVisible, 'opacity-0 translate-y-4': !isVisible }"
                    style="transition-delay: 200ms">
                    Tashih veya düzeltme hizmetleri, yazılı içeriğinizin doğruluğunu ve kalitesini sağlar.
                    <span class="block mt-2">
                        Kitap, makale veya başka herhangi bir yazılı metninizi mükemmelleştirmekte uzmanız.
                    </span>
                </p>

                <!-- Animated Buttons -->
                <div class="flex flex-col sm:flex-row items-center justify-center gap-4 sm:gap-6 mt-8"
                    :class="{ 'opacity-100 translate-y-0': isVisible, 'opacity-0 translate-y-4': !isVisible }"
                    style="transition-delay: 400ms">
                    <button class="group relative w-full sm:w-auto rounded-md bg-orange-500 px-6 py-3 
                                 text-white font-semibold shadow hover:bg-orange-600 transition overflow-hidden">
                        <span class="relative z-10">Başlayın</span>
                        <div class="absolute inset-0 bg-orange-600 scale-x-0 origin-left 
                                  transition-transform duration-300 group-hover:scale-x-100">
                        </div>
                    </button>
                    <button class="group text-base font-semibold leading-6 text-navy-900 
                                 hover:text-orange-500 transition duration-200">
                        Daha fazla bilgi edinin
                        <span class="inline-block transition-transform duration-200 
                                   group-hover:translate-x-1">→</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Scroll Indicator -->
        <div class="absolute bottom-8 left-1/2 transform -translate-x-1/2 transition-opacity duration-800"
            :class="{ 'opacity-100': isVisible, 'opacity-0': !isVisible }" style="transition-delay: 600ms">
            <MoveDown class="animate-float w-6 h-6 text-navy-600" />
        </div>
    </div>
</template>

<style scoped>
.bg-gradient-radial {
    background-image: radial-gradient(circle, var(--tw-gradient-stops));
}

@keyframes float {

    0%,
    100% {
        transform: translateY(0);
    }

    50% {
        transform: translateY(-20px);
    }
}

.animate-float {
    animation: float 2s infinite ease-in-out;
}
</style>