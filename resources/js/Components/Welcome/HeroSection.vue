<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue';
import { MoveDown } from 'lucide-vue-next';

const isVisible = ref(false);
const mousePosition = ref({ x: 0, y: 0 });
const targetMousePosition = ref({ x: 0, y: 0 });
const scrollPosition = ref(0);
let animationFrame = null;

// Lerp function for smooth animations
const lerp = (start, end, factor) => start + (end - start) * factor;

const updateMousePosition = () => {
    const smoothFactor = 0.1; // Adjust this value to control smoothness (0-1)

    mousePosition.value = {
        x: lerp(mousePosition.value.x, targetMousePosition.value.x, smoothFactor),
        y: lerp(mousePosition.value.y, targetMousePosition.value.y, smoothFactor),
    };

    animationFrame = requestAnimationFrame(updateMousePosition);
};

const handleMouseMove = (e) => {
    targetMousePosition.value = {
        x: (e.clientX - window.innerWidth / 2) * 0.01, // Adjust factor for stronger effect
        y: (e.clientY - window.innerHeight / 2) * 0.01,
    };
};

const handleScroll = () => {
    scrollPosition.value = window.scrollY; // Track the vertical scroll position
};

// Generate dynamic styles for background circles
const circles = computed(() =>
    Array.from({ length: 4 }, (_, i) => ({
        size: `${150 + i * 50}px`,
        left: `${10 + i * 20}%`,
        top: `${20 + i * 15}%`,
        color: i % 2 ? 'from-orange-500/20' : 'from-green-700/20',
        transform: `translate(${mousePosition.value.x * (i + 1)}px, ${mousePosition.value.y * (i + 1) - scrollPosition.value * 0.1 * (i + 1)
            }px)`,
    }))
);

onMounted(() => {
    console.log("Hero Section Mounted");
    setTimeout(() => {
        isVisible.value = true;
    }, 100);

    // Add event listeners
    window.addEventListener('mousemove', handleMouseMove);
    window.addEventListener('scroll', handleScroll);

    // Start the animation loop
    updateMousePosition();
});

onUnmounted(() => {
    window.removeEventListener('mousemove', handleMouseMove);
    window.removeEventListener('scroll', handleScroll);
    if (animationFrame) cancelAnimationFrame(animationFrame);
});
</script>

<template>
    <div class="relative min-h-screen overflow-hidden bg-gradient-to-b from-green-50 to-white">
        <!-- Background Circles -->
        <div class="absolute inset-0">
            <div v-for="(circle, i) in circles" :key="i"
                class="absolute rounded-full mix-blend-multiply blur-xl transition-transform"
                :class="`bg-gradient-radial ${circle.color} to-transparent`"
                :style="{ width: circle.size, height: circle.size, left: circle.left, top: circle.top, transform: circle.transform }">
            </div>
        </div>

        <!-- Main Content -->
        <div class="relative min-h-screen flex items-center justify-center px-6">
            <div class="text-center space-y-8">
                <!-- Animated Heading -->
                <h1 class="text-3xl sm:text-5xl font-bold tracking-tight text-navy-900 leading-tight transition-all duration-800 ease-out"
                    :class="{ 'opacity-100 translate-y-0': isVisible, 'opacity-0 translate-y-4': !isVisible }">
                    <span class="relative inline-block">
                        Tashih Hizmetlerine
                        <div class="absolute bottom-0 left-0 w-full h-1 bg-orange-500 transform origin-left transition-transform duration-500"
                            :class="{ 'scale-x-100': isVisible, 'scale-x-0': !isVisible }"></div>
                    </span>
                    <br />
                    <span class="relative inline-block text-orange-500">Hoş Geldiniz</span>
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
                <div class="flex flex-col sm:flex-row items-center justify-center gap-4 sm:gap-6 mt-8 transition-all duration-800"
                    :class="{ 'opacity-100 translate-y-0': isVisible, 'opacity-0 translate-y-4': !isVisible }"
                    style="transition-delay: 400ms">
                    <button
                        class="relative w-full sm:w-auto rounded-md bg-orange-500 px-6 py-3 text-white font-semibold shadow hover:bg-orange-600 transition overflow-hidden">
                        <span class="relative z-10">Başlayın</span>
                        <div
                            class="absolute inset-0 bg-orange-600 scale-x-0 origin-left transition-transform duration-300 group-hover:scale-x-100">
                        </div>
                    </button>
                    <button
                        class="text-base font-semibold leading-6 text-navy-900 hover:text-orange-500 transition duration-200">
                        Daha fazla bilgi edinin
                        <span class="inline-block transition-transform duration-200 group-hover:translate-x-1">→</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Scroll Indicator -->
        <div class="absolute bottom-8 left-1/2 transform -translate-x-1/2 transition-opacity duration-800"
            :class="{ 'opacity-100': isVisible, 'opacity-0': !isVisible }" style="transition-delay: 600ms">
            <MoveDown class="animate-bounce w-6 h-6 text-navy-600" />
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

.animate-bounce {
    animation: float 2s infinite ease-in-out;
}
</style>
