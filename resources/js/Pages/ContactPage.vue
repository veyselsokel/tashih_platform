<script setup>
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';
import NavBar from '@/Components/Welcome/NavBar.vue';
import { useForm } from '@inertiajs/vue3';
import { CheckCircle } from 'lucide-vue-next';

defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
});

const form = useForm({
    name: '',
    email: '',
    message: ''
});

const isSubmitted = ref(false);

const handleSubmit = () => {
    form.post(route('contact.store'), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            isSubmitted.value = true;
            setTimeout(() => {
                isSubmitted.value = false;
            }, 3000);
        },
    });
};
</script>

<template>

    <Head title="İletişim" />

    <div class="min-h-screen bg-gradient-to-br from-blue-50 to-green-50">
        <NavBar :can-login="canLogin" :can-register="canRegister" />

        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <div class="max-w-3xl mx-auto pt-16 pb-24 sm:pt-24 sm:pb-32">
                <!-- Header Section -->
                <div class="text-center space-y-4">
                    <h1 class="text-3xl font-bold tracking-tight text-gray-900 sm:text-5xl transition-all duration-300">
                        İletişime Geçin
                    </h1>
                    <p class="text-lg leading-8 text-gray-600 max-w-2xl mx-auto">
                        Herhangi bir sorunuz veya öneriniz mi var? Bizimle iletişime geçmek için aşağıdaki formu
                        doldurun.
                    </p>
                </div>

                <!-- Contact Form Card -->
                <div class="mt-12 bg-white rounded-2xl shadow-xl overflow-hidden">
                    <div class="p-6 sm:p-10">
                        <form @submit.prevent="handleSubmit" class="space-y-6">
                            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                                <!-- Name Field -->
                                <div class="col-span-1">
                                    <label for="name" class="block text-sm font-medium text-gray-700">İsim</label>
                                    <div class="mt-1 relative">
                                        <input v-model="form.name" type="text" id="name" required class="block w-full px-4 py-3 text-gray-900 border border-gray-300 rounded-lg 
                                                   shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent 
                                                   transition-all duration-200"
                                            :class="{ 'border-red-300': form.errors.name }">
                                        <div v-if="form.errors.name"
                                            class="absolute -bottom-5 left-0 text-red-500 text-sm">
                                            {{ form.errors.name }}
                                        </div>
                                    </div>
                                </div>

                                <!-- Email Field -->
                                <div class="col-span-1">
                                    <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                                    <div class="mt-1 relative">
                                        <input v-model="form.email" type="email" id="email" required class="block w-full px-4 py-3 text-gray-900 border border-gray-300 rounded-lg 
                                                   shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent 
                                                   transition-all duration-200"
                                            :class="{ 'border-red-300': form.errors.email }">
                                        <div v-if="form.errors.email"
                                            class="absolute -bottom-5 left-0 text-red-500 text-sm">
                                            {{ form.errors.email }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Message Field -->
                            <div>
                                <label for="message" class="block text-sm font-medium text-gray-700">Mesaj</label>
                                <div class="mt-1 relative">
                                    <textarea v-model="form.message" id="message" rows="6" required class="block w-full px-4 py-3 text-gray-900 border border-gray-300 rounded-lg 
                                               shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent 
                                               transition-all duration-200 resize-none"
                                        :class="{ 'border-red-300': form.errors.message }"></textarea>
                                    <div v-if="form.errors.message"
                                        class="absolute -bottom-5 left-0 text-red-500 text-sm">
                                        {{ form.errors.message }}
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
    </div>
</template>

<style scoped>
.animate-fade-in-up {
    animation: fadeInUp 0.5s ease-out forwards;
}

@keyframes fadeInUp {
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