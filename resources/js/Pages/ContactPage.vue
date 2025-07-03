<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import ContactForm from '@/Components/Contact/ContactForm.vue';
import ContactHeader from '@/Components/Contact/ContactHeader.vue';
import { Phone, Mail, MapPin } from 'lucide-vue-next';

defineProps({
    posts: {
        type: Object,
        default: () => ({})
    },
    title: {
        type: String,
        default: 'İletişim'
    }
});
</script>

<template>
    <GuestLayout :title="title">
        <div class="bg-stone-50">
            <div class="container mx-auto px-4 py-24 sm:px-6 lg:px-8 max-w-6xl">
                <div class="grid lg:grid-cols-5 gap-12 items-start">
                    <!-- Sol taraf - İletişim bilgileri -->
                    <div class="lg:col-span-2">
                        <ContactHeader />
                        <div class="mt-10 space-y-8">
                            <div class="flex items-center space-x-4 text-gray-600">
                                <div class="flex-shrink-0 w-12 h-12 bg-orange-100 text-orange-600 rounded-full flex items-center justify-center">
                                    <Phone class="w-6 h-6" />
                                </div>
                                <div>
                                    <h3 class="font-semibold text-lg text-gray-800">Telefon</h3>
                                    <p class="mt-1 text-gray-600">+90 (507) 371 0845</p>
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

                    <!-- Sağ taraf - Form -->
                    <div class="lg:col-span-3">
                        <ContactForm />
                    </div>
                </div>
            </div>
        </div>
    </GuestLayout>
</template>