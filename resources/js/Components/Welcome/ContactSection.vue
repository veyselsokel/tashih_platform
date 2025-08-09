<script setup>
import ContactForm from '@/Components/Contact/ContactForm.vue';
import ContactHeader from '@/Components/Contact/ContactHeader.vue';
import { Phone, Mail, MapPin } from 'lucide-vue-next';

defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
});
</script>

<template>
    <section id="contact" class="py-24 bg-stone-50">
        <div class="container mx-auto px-4 py-16 sm:px-6 lg:px-8 max-w-6xl">
            <div class="grid lg:grid-cols-5 gap-12 items-start">
                <!-- Left side - Contact info -->
                <div class="lg:col-span-2">
                    <ContactHeader />
                    <div class="mt-10 space-y-8">
                        <div class="flex items-center space-x-4 text-gray-600">
                            <div class="flex-shrink-0 w-12 h-12 bg-orange-100 text-orange-600 rounded-full flex items-center justify-center">
                                <Phone class="w-6 h-6" />
                            </div>
                            <div>
                                <h3 class="font-semibold text-lg text-gray-800">Telefon</h3>
                                <p class="mt-1 text-gray-600">+90 (507) 371 08 45</p>
                            </div>
                        </div>

                        <div class="flex items-center space-x-4 text-gray-600">
                             <div class="flex-shrink-0 w-12 h-12 bg-orange-100 text-orange-600 rounded-full flex items-center justify-center">
                                <Mail class="w-6 h-6" />
                            </div>
                            <div>
                                <h3 class="font-semibold text-lg text-gray-800">Email</h3>
                                <p class="mt-1 text-gray-600">info@tashih.tr</p>
                            </div>
                        </div>

                        <div class="flex items-center space-x-4 text-gray-600">
                             <div class="flex-shrink-0 w-12 h-12 bg-orange-100 text-orange-600 rounded-full flex items-center justify-center">
                                <MapPin class="w-6 h-6" />
                            </div>
                            <div>
                                <h3 class="font-semibold text-lg text-gray-800">Adres</h3>
                                <p class="mt-1 text-gray-600">İstanbul, Türkiye</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right side - Form -->
                <div class="lg:col-span-3">
                    <ContactForm />
                </div>
            </div>
        </div>
    </section>
</template>