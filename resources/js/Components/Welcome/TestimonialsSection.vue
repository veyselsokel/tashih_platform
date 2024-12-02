<script setup>
import { ref, onMounted } from 'vue';

const isVisible = ref(false);
const currentTestimonial = ref(0);

const testimonials = [
    {
        name: 'Dr. Ahmet Yılmaz',
        role: 'Akademisyen',
        content: 'Makalemizin düzenlenmesinde gösterdikleri profesyonel yaklaşım ve titiz çalışma için teşekkür ederim.',
        image: '/api/placeholder/64/64'
    },
    {
        name: 'Ayşe Kaya',
        role: 'Yazar',
        content: 'Kitabımın düzenleme sürecinde gösterdikleri özen ve sundukları yapıcı öneriler çok değerliydi.',
        image: '/api/placeholder/64/64'
    },
    {
        name: 'Prof. Dr. Mehmet Demir',
        role: 'Akademisyen',
        content: 'Akademik çalışmalarımızda tercih ettiğimiz güvenilir bir çözüm ortağı.',
        image: '/api/placeholder/64/64'
    }
];

onMounted(() => {
    const observer = new IntersectionObserver(
        (entries) => {
            if (entries[0].isIntersecting) {
                isVisible.value = true;
            }
        },
        { threshold: 0.1 }
    );

    const element = document.querySelector('#testimonials-section');
    if (element) observer.observe(element);

    // Auto-rotate testimonials
    setInterval(() => {
        currentTestimonial.value = (currentTestimonial.value + 1) % testimonials.length;
    }, 5000);
});
</script>

<template>
    <section id="testimonials-section" class="py-20 bg-gray-50 pb-44">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-10">
                <h2 class="text-3xl font-bold text-navy-900 mb-4"
                    :class="{ 'opacity-100 translate-y-0': isVisible, 'opacity-0 translate-y-4': !isVisible }"
                    style="transition: all 0.8s cubic-bezier(0.4, 0, 0.2, 1)">
                    Müşteri Yorumları
                </h2>
                <p class="text-lg text-gray-600 max-w-2xl mx-auto"
                    :class="{ 'opacity-100 translate-y-0': isVisible, 'opacity-0 translate-y-4': !isVisible }"
                    style="transition: all 0.8s cubic-bezier(0.4, 0, 0.2, 1) 0.2s">
                    Müşterilerimizin deneyimlerini dinleyin
                </p>
            </div>

            <div class="relative max-w-3xl mx-auto"
                :class="{ 'opacity-100 translate-y-5': isVisible, 'opacity-0 translate-y-4': !isVisible }"
                style="transition: all 0.8s cubic-bezier(0.4, 0, 0.2, 1) 0.4s">
                <div class="relative">
                    <div v-for="(testimonial, index) in testimonials" :key="index"
                        class="absolute w-full transition-all duration-500"
                        :class="index === currentTestimonial ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4'">
                        <div class="bg-white rounded-lg shadow-lg p-8">
                            <div class="flex items-center mb-4">
                                <img :src="testimonial.image" :alt="testimonial.name"
                                    class="w-16 h-16 rounded-full object-cover" />
                                <div class="ml-4">
                                    <h3 class="text-xl font-semibold text-navy-900">{{ testimonial.name }}</h3>
                                    <p class="text-gray-600">{{ testimonial.role }}</p>
                                </div>
                            </div>
                            <p class="text-gray-600 italic">{{ testimonial.content }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex justify-center mt-1 space-x-2">
                <button v-for="(_, index) in testimonials" :key="index"
                    class="w-3 h-3 rounded-full transition-all duration-300"
                    :class="index === currentTestimonial ? 'bg-orange-500' : 'bg-gray-300'"
                    @click="currentTestimonial = index">
                </button>
            </div>
        </div>
    </section>
</template>