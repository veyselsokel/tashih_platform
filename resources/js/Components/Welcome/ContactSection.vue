<script setup>
import { ref, onMounted } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { CheckCircle } from 'lucide-vue-next';

const isVisible = ref(false);

onMounted(() => {
    const observer = new IntersectionObserver(
        (entries) => {
            if (entries[0].isIntersecting) {
                isVisible.value = true;
            }
        },
        { threshold: 0.1 }
    );

    const element = document.querySelector('#contact-section');
    if (element) observer.observe(element);
});

// Form validation rules
const validateForm = (formData) => {
    const errors = {};

    // Name validation
    if (!formData.name.trim()) {
        errors.name = 'İsim alanı zorunludur';
    } else if (formData.name.trim().length < 2) {
        errors.name = 'İsim en az 2 karakter olmalıdır';
    } else if (formData.name.trim().length > 50) {
        errors.name = 'İsim 50 karakterden uzun olamaz';
    }

    // Email validation
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!formData.email.trim()) {
        errors.email = 'Email alanı zorunludur';
    } else if (!emailRegex.test(formData.email)) {
        errors.email = 'Geçerli bir email adresi giriniz';
    }

    // Message validation
    if (!formData.message.trim()) {
        errors.message = 'Mesaj alanı zorunludur';
    } else if (formData.message.trim().length < 10) {
        errors.message = 'Mesajınız en az 10 karakter olmalıdır';
    } else if (formData.message.trim().length > 1000) {
        errors.message = 'Mesajınız 1000 karakterden uzun olamaz';
    }

    return errors;
};

const form = useForm({
    name: '',
    email: '',
    message: ''
});

const isSubmitted = ref(false);
const validationErrors = ref({});

const handleSubmit = () => {
    // Reset previous validation errors
    validationErrors.value = {};

    // Validate form
    const errors = validateForm(form);

    // If there are validation errors, show them and stop submission
    if (Object.keys(errors).length > 0) {
        validationErrors.value = errors;
        return;
    }

    // If validation passes, submit the form
    form.post(route('contact.store'), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            validationErrors.value = {};
            isSubmitted.value = true;
            setTimeout(() => {
                isSubmitted.value = false;
            }, 3000);
        },
        onError: (errors) => {
            validationErrors.value = errors;
        }
    });
};

// Real-time validation
const validateField = (field) => {
    const errors = validateForm({
        name: form.name,
        email: form.email,
        message: form.message
    });
    validationErrors.value[field] = errors[field];
};
</script>

<template>
    <section id="contact-section" class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl font-bold text-navy-900 mb-4"
                    :class="{ 'opacity-100 translate-y-0': isVisible, 'opacity-0 translate-y-4': !isVisible }"
                    style="transition: all 0.8s cubic-bezier(0.4, 0, 0.2, 1)">
                    İletişime Geçin
                </h2>
                <p class="text-lg text-gray-600 max-w-2xl mx-auto"
                    :class="{ 'opacity-100 translate-y-0': isVisible, 'opacity-0 translate-y-4': !isVisible }"
                    style="transition: all 0.8s cubic-bezier(0.4, 0, 0.2, 1) 0.2s">
                    Sorularınız için bize ulaşın
                </p>
            </div>

            <div class="max-w-2xl mx-auto"
                :class="{ 'opacity-100 translate-y-0': isVisible, 'opacity-0 translate-y-4': !isVisible }"
                style="transition: all 0.8s cubic-bezier(0.4, 0, 0.2, 1) 0.4s">
                <div class="mt-12 bg-white rounded-2xl shadow-xl overflow-hidden">
                    <div class="p-6 sm:p-10">
                        <form @submit.prevent="handleSubmit" class="space-y-6">
                            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                                <!-- Name Field -->
                                <div class="col-span-1">
                                    <label for="name" class="block text-sm font-medium text-gray-700">İsim</label>
                                    <div class="mt-1 relative">
                                        <input v-model="form.name" type="text" id="name" required
                                            @blur="validateField('name')" class="block w-full px-4 py-3 text-gray-900 border border-gray-300 rounded-lg 
                   shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent 
                   transition-all duration-200" :class="{ 'border-red-300': validationErrors.name }">
                                        <div v-if="validationErrors.name"
                                            class="absolute -bottom-5 left-0 text-red-500 text-sm">
                                            {{ validationErrors.name }}
                                        </div>
                                    </div>
                                </div>

                                <!-- Email Field -->
                                <div class="col-span-1">
                                    <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                                    <div class="mt-1 relative">
                                        <input v-model="form.email" type="email" id="email" required
                                            @blur="validateField('email')" class="block w-full px-4 py-3 text-gray-900 border border-gray-300 rounded-lg 
                   shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent 
                   transition-all duration-200" :class="{ 'border-red-300': validationErrors.email }">
                                        <div v-if="validationErrors.email"
                                            class="absolute -bottom-5 left-0 text-red-500 text-sm">
                                            {{ validationErrors.email }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Message Field -->
                            <div>
                                <label for="message" class="block text-sm font-medium text-gray-700">Mesaj</label>
                                <div class="mt-1 relative">
                                    <textarea v-model="form.message" id="message" rows="6" required
                                        @blur="validateField('message')" class="block w-full px-4 py-3 text-gray-900 border border-gray-300 rounded-lg 
                   shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent 
                   transition-all duration-200 resize-none"
                                        :class="{ 'border-red-300': validationErrors.message }"></textarea>
                                    <div v-if="validationErrors.message"
                                        class="absolute -bottom-5 left-0 text-red-500 text-sm">
                                        {{ validationErrors.message }}
                                    </div>
                                </div>
                            </div>

                            <!-- Submit Button -->
                            <div class="pt-4">
                                <button type="submit" class="w-full sm:w-auto px-6 py-3 bg-gradient-to-r from-blue-600 to-blue-700 
                                           text-white font-semibold rounded-lg shadow-md hover:from-blue-700 
                                           hover:to-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 
                                           focus:ring-offset-2 transition-all duration-200 disabled:opacity-50 
                                           disabled:cursor-not-allowed" :disabled="form.processing">
                                    <span class="flex items-center justify-center space-x-2">
                                        <span>{{ form.processing ? 'Gönderiliyor...' : 'Gönder' }}</span>
                                    </span>
                                </button>
                            </div>
                        </form>

                        <!-- Success Message -->
                        <div v-if="isSubmitted" class="fixed bottom-8 right-8 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg 
                                   flex items-center space-x-2 animate-fade-in-up">
                            <CheckCircle class="w-5 h-5" />
                            <span>Mesajınız başarıyla gönderildi!</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>