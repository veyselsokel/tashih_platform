<script setup>
import { ref, onMounted } from 'vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { MoveDown, Book, Users, Award, Target, Rocket, Scale } from 'lucide-vue-next';

const isVisible = ref(false);
const animationStarted = ref(false);

onMounted(() => {
    // İlk görünürlük için kısa gecikme
    setTimeout(() => {
        isVisible.value = true;
    }, 100);

    // Sayfa yüklendiğinde scroll pozisyonunu kontrol et
    checkScroll();
    window.addEventListener('scroll', checkScroll);
});

const checkScroll = () => {
    const scrollPosition = window.scrollY;
    if (scrollPosition > 100 && !animationStarted.value) {
        animationStarted.value = true;
    }
};

const values = [
    {
        icon: Book,
        title: 'Uzmanlık',
        description: 'Alanında uzman kadromuzla en kaliteli hizmeti sunuyoruz'
    },
    {
        icon: Scale,
        title: 'Güvenilirlik',
        description: 'İşimizi titizlikle ve güvenilir bir şekilde yapıyoruz'
    },
    {
        icon: Rocket,
        title: 'Hız',
        description: 'Zamanında ve hızlı teslimat garantisi veriyoruz'
    }
];

const stats = [
    { value: '1000+', label: 'Tamamlanan Proje' },
    { value: '50+', label: 'Uzman Ekip' },
    { value: '98%', label: 'Müşteri Memnuniyeti' },
    { value: '20+', label: 'Yıllık Deneyim' }
];
</script>

<template>
    <GuestLayout>
        <div class="relative min-h-screen bg-gradient-to-b from-green-50 to-white">
            <!-- Main Content -->
            <div class="relative min-h-screen px-6 py-24">
                <div class="max-w-7xl mx-auto">
                    <!-- Header Section -->
                    <div class="text-center mb-16">
                        <h1 class="text-4xl sm:text-5xl font-bold tracking-tight leading-tight mb-6 transition-all duration-1000"
                            :class="[
                                isVisible ? 'opacity-100 translate-y-0 scale-100' : 'opacity-0 -translate-y-10 scale-95',
                                'transform'
                            ]">
                            <span class="text-orange-500 inline-block transition-all duration-1000 hover:scale-105">
                                Hakkımızda
                            </span>
                        </h1>
                        <p class="text-lg text-navy-600 max-w-2xl mx-auto transition-all duration-1000 delay-300"
                            :class="isVisible ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4'">
                            Türkçe dilinin doğru ve etkili kullanımı için çalışan profesyonel ekibimizle tanışın.
                        </p>
                    </div>

                    <!-- Stats Section -->
                    <div class="mb-20">
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-8">
                            <div v-for="(stat, index) in stats" :key="index"
                                class="bg-white p-6 rounded-lg shadow-lg text-center transition-all duration-700"
                                :class="[
                                    isVisible ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-10',
                                    'transform hover:scale-105'
                                ]" :style="{ transitionDelay: `${300 + index * 150}ms` }">
                                <div class="text-3xl font-bold text-orange-500 mb-2 animate-count">{{ stat.value }}
                                </div>
                                <div class="text-navy-600">{{ stat.label }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- Mission & Vision Section -->
                    <div class="grid md:grid-cols-2 gap-12 mb-20">
                        <div v-for="(item, index) in ['Vizyonumuz', 'Misyonumuz']" :key="item"
                            class="space-y-6 bg-white p-8 rounded-lg shadow-lg transition-all duration-1000" :class="[
                                isVisible ? 'opacity-100 translate-x-0' : index === 0 ? 'opacity-0 -translate-x-20' : 'opacity-0 translate-x-20',
                                'transform hover:shadow-xl'
                            ]" :style="{ transitionDelay: `${800 + index * 200}ms` }">
                            <div class="flex items-center space-x-4">
                                <component :is="index === 0 ? Target : Award"
                                    class="w-8 h-8 text-orange-500 transition-transform duration-500 hover:scale-110" />
                                <h2 class="text-2xl font-semibold text-navy-900">{{ item }}</h2>
                            </div>
                            <p class="text-navy-600">
                                {{ index === 0 ? 'Türkçe dilinin zenginliğini ve güzelliğini korumak...' :
                                    'Profesyonel tashih hizmetlerimizle...' }}
                            </p>
                        </div>
                    </div>

                    <!-- Values Section -->
                    <div class="mb-20">
                        <h2 class="text-3xl font-semibold text-center mb-10 transition-all duration-700"
                            :class="isVisible ? 'opacity-100 scale-100' : 'opacity-0 scale-95'">
                            Değerlerimiz
                        </h2>
                        <div class="grid md:grid-cols-3 gap-8">
                            <div v-for="(value, index) in values" :key="index"
                                class="bg-white p-6 rounded-lg shadow-lg transition-all duration-1000" :class="[
                                    isVisible ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-20',
                                    'transform hover:scale-105'
                                ]" :style="{ transitionDelay: `${1200 + index * 200}ms` }">
                                <component :is="value.icon"
                                    class="w-12 h-12 text-orange-500 mb-4 transition-all duration-500 transform hover:rotate-12" />
                                <h3 class="text-xl font-semibold mb-2">{{ value.title }}</h3>
                                <p class="text-navy-600">{{ value.description }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Team Section -->
                    <div class="text-center space-y-8">
                        <div class="flex items-center justify-center space-x-4 mb-10 transition-all duration-700"
                            :class="isVisible ? 'opacity-100 scale-100' : 'opacity-0 scale-95'">
                            <Users class="w-8 h-8 text-orange-500 animate-pulse" />
                            <h2 class="text-3xl font-semibold text-navy-900">Ekibimiz</h2>
                        </div>
                        <div class="grid md:grid-cols-3 gap-8">
                            <div v-for="(team, index) in ['Dil Uzmanları', 'Editörler', 'Tashih Uzmanları']"
                                :key="index" class="bg-white p-6 rounded-lg shadow-lg transition-all duration-1000"
                                :class="isVisible ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-20'"
                                :style="{ transitionDelay: `${1600 + index * 200}ms` }">
                                <h3 class="text-xl font-semibold mb-2">{{ team }}</h3>
                                <p class="text-navy-600">
                                    {{ index === 0 ? 'Türk Dili ve Edebiyatı alanında uzman akademisyenler...' :
                                        index === 1 ? 'Deneyimli editörler ve redaktörler' :
                                            'Profesyonel düzeltmenler ve kontrolörler' }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </GuestLayout>
</template>

<style scoped>
@keyframes float {

    0%,
    100% {
        transform: translateY(0);
    }

    50% {
        transform: translateY(-10px);
    }
}

.animate-float {
    animation: float 2s infinite ease-in-out;
}

@keyframes pulse {

    0%,
    100% {
        opacity: 1;
        transform: scale(1);
    }

    50% {
        opacity: 0.8;
        transform: scale(1.1);
    }
}

.animate-pulse {
    animation: pulse 2s infinite ease-in-out;
}

.animate-count {
    @apply transition-all duration-1000;
    counter-reset: count 0;
    animation: count 2s forwards;
}

@keyframes count {
    from {
        opacity: 0;
        transform: translateY(20px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>