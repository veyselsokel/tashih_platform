<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { CheckCircle, Send, Loader2 } from 'lucide-vue-next';
import ContactFormField from './ContactFormField.vue';

const form = useForm({
    name: '',
    email: '',
    phone: '',
    subject: '',
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
    <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
        <div class="p-8">
            <form @submit.prevent="handleSubmit" class="space-y-6">
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <ContactFormField v-model="form.name" id="name" label="İsim" type="text" :error="form.errors.name"
                        placeholder="Adınız Soyadınız" />
                    <ContactFormField v-model="form.email" id="email" label="Email" type="email"
                        :error="form.errors.email" placeholder="email@example.com" />
                </div>

                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <ContactFormField v-model="form.phone" id="phone" label="Telefon" type="tel"
                        :error="form.errors.phone" placeholder="+90 (___) ___ ____" />
                    <ContactFormField v-model="form.subject" id="subject" label="Konu" type="text"
                        :error="form.errors.subject" placeholder="Mesajınızın konusu" />
                </div>

                <ContactFormField v-model="form.message" id="message" label="Mesaj" type="textarea"
                    :error="form.errors.message" :rows="6" placeholder="Mesajınızı buraya yazın..." />

                <div class="pt-4">
                    <button type="submit" class="w-full sm:w-auto px-8 py-4 bg-gradient-to-r from-blue-600 to-blue-700 
                               text-white font-semibold rounded-lg shadow-md hover:from-blue-700 
                               hover:to-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 
                               focus:ring-offset-2 transition-all duration-200 disabled:opacity-50 
                               disabled:cursor-not-allowed flex items-center justify-center space-x-2"
                        :disabled="form.processing">
                        <span>{{ form.processing ? 'Gönderiliyor...' : 'Mesaj Gönder' }}</span>
                        <Send v-if="!form.processing" class="w-5 h-5" />
                        <Loader2 v-else class="w-5 h-5 animate-spin" />
                    </button>
                </div>
            </form>

            <div v-if="isSubmitted" class="fixed bottom-8 right-8 bg-green-500 text-white px-6 py-4 rounded-lg shadow-lg 
                       flex items-center space-x-2 animate-fade-in-up">
                <CheckCircle class="w-5 h-5" />
                <span>Mesajınız başarıyla gönderildi!</span>
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