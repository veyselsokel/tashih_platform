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
    <section id="contact-section" class="py-20 bg-white">
        <div class="container mx-auto px-4 py-16 sm:px-6 lg:px-8 max-w-6xl">
            <div class="grid lg:grid-cols-5 gap-8 items-start">
                <!-- Sol taraf - İletişim bilgileri -->
                <div class="lg:col-span-2 lg:sticky lg:top-4">
                    <ContactHeader />
                    <div class="mt-8 space-y-6">
                        <div class="flex items-center space-x-4 text-gray-600">
                            <div class="flex-shrink-0">
                                <Phone class="w-6 h-6" />
                            </div>
                            <div>
                                <h3 class="font-medium">Telefon</h3>
                                <p class="mt-1">+90 (507) 371 08 45</p>
                            </div>
                        </div>

                        <div class="flex items-center space-x-4 text-gray-600">
                            <div class="flex-shrink-0">
                                <Mail class="w-6 h-6" />
                            </div>
                            <div>
                                <h3 class="font-medium">Email</h3>
                                <p class="mt-1">info@tashih.tr</p>
                            </div>
                        </div>

                        <div class="flex items-center space-x-4 text-gray-600">
                            <div class="flex-shrink-0">
                                <MapPin class="w-6 h-6" />
                            </div>
                            <div>
                                <h3 class="font-medium">Adres</h3>
                                <p class="mt-1">İstanbul, Türkiye</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sağ taraf - Form -->
                <div class="lg:col-span-3">
                    <ContactForm />
                </div>
            </div>
        </div>
    </section>
</template>