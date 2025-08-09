<script setup>
import { ref, onMounted } from 'vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Book, Users, Award, Target, Rocket, Scale } from 'lucide-vue-next';

const isVisible = ref(false);

onMounted(() => {
    setTimeout(() => {
        isVisible.value = true;
    }, 100);
});

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
    <GuestLayout title="Hakkımızda">
        <div class="bg-stone-50">
            <!-- Header Section -->
            <header class="relative text-center px-6 py-24 md:py-32 lg:py-40">
                 <div class="max-w-4xl mx-auto">
                    <transition enter-active-class="transition ease-out duration-1000" enter-from-class="opacity-0 translate-y-4" enter-to-class="opacity-100 translate-y-0">
                        <h1 v-if="isVisible" class="text-4xl md:text-5xl font-extrabold text-gray-800 leading-tight mb-4">
                            Hakkımızda
                        </h1>
                    </transition>
                    <transition enter-active-class="transition ease-out duration-1000 delay-200" enter-from-class="opacity-0 translate-y-4" enter-to-class="opacity-100 translate-y-0">
                        <p v-if="isVisible" class="text-lg text-gray-600 max-w-3xl mx-auto">
                            Türkçe dilinin doğru ve etkili kullanımı için çalışan profesyonel ekibimizle tanışın.
                        </p>
                    </transition>
                </div>
            </header>

            <!-- Main Content -->
            <main class="px-6 pb-24">
                <div class="max-w-7xl mx-auto">
                    <!-- Stats Section -->
                    <section class="mb-24">
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-8">
                            <div v-for="(stat, index) in stats" :key="index"
                                class="bg-white p-6 rounded-xl shadow-lg text-center transition-all duration-700 transform hover:scale-105"
                                :class="isVisible ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-10'"
                                :style="{ transitionDelay: `${300 + index * 150}ms` }">
                                <div class="text-4xl font-bold text-orange-500 mb-2">{{ stat.value }}</div>
                                <div class="text-gray-600">{{ stat.label }}</div>
                            </div>
                        </div>
                    </section>

                    <!-- Mission & Vision Section -->
                    <section class="grid md:grid-cols-2 gap-12 mb-24">
                        <div v-for="(item, index) in [{title: 'Vizyonumuz', icon: Target, content: 'Türkçe dilinin zenginliğini ve güzelliğini korumak...'}, {title: 'Misyonumuz', icon: Award, content: 'Profesyonel tashih hizmetlerimizle...'}]" :key="item.title"
                            class="space-y-6 bg-white p-8 rounded-xl shadow-lg transition-all duration-1000 transform hover:shadow-xl"
                            :class="isVisible ? 'opacity-100 translate-x-0' : (index === 0 ? 'opacity-0 -translate-x-20' : 'opacity-0 translate-x-20')"
                            :style="{ transitionDelay: `${800 + index * 200}ms` }">
                            <div class="flex items-center space-x-4">
                                <component :is="item.icon" class="w-10 h-10 text-orange-500" />
                                <h2 class="text-3xl font-semibold text-gray-800">{{ item.title }}</h2>
                            </div>
                            <p class="text-gray-600 text-lg leading-relaxed">
                                {{ item.content }}
                            </p>
                        </div>
                    </section>

                    <!-- Values Section -->
                    <section class="mb-24">
                        <h2 class="text-3xl font-bold text-center mb-12 text-gray-800">Değerlerimiz</h2>
                        <div class="grid md:grid-cols-3 gap-8">
                            <div v-for="(value, index) in values" :key="index"
                                class="bg-white p-8 rounded-xl shadow-lg transition-all duration-1000 transform hover:scale-105"
                                :class="isVisible ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-20'"
                                :style="{ transitionDelay: `${1200 + index * 200}ms` }">
                                <component :is="value.icon" class="w-12 h-12 text-orange-500 mb-6" />
                                <h3 class="text-2xl font-semibold mb-3 text-gray-800">{{ value.title }}</h3>
                                <p class="text-gray-600 leading-relaxed">{{ value.description }}</p>
                            </div>
                        </div>
                    </section>

                    <!-- Team Section -->
                    <section class="text-center">
                        <div class="flex items-center justify-center space-x-4 mb-12">
                            <Users class="w-10 h-10 text-orange-500" />
                            <h2 class="text-3xl font-bold text-gray-800">Ekibimiz</h2>
                        </div>
                        <div class="grid md:grid-cols-3 gap-8">
                            <div v-for="(team, index) in ['Dil Uzmanları', 'Editörler', 'Tashih Uzmanları']" :key="index"
                                class="bg-white p-8 rounded-xl shadow-lg transition-all duration-1000"
                                :class="isVisible ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-20'"
                                :style="{ transitionDelay: `${1600 + index * 200}ms` }">
                                <h3 class="text-2xl font-semibold mb-3 text-gray-800">{{ team }}</h3>
                                <p class="text-gray-600 leading-relaxed">
                                    {{ index === 0 ? 'Türk Dili ve Edebiyatı alanında uzman akademisyenler...' :
                                        index === 1 ? 'Deneyimli editörler ve redaktörler' :
                                            'Profesyonel düzeltmenler ve kontrolörler' }}
                                </p>
                            </div>
                        </div>
                    </section>
                </div>
            </main>
        </div>
    </GuestLayout>
</template>