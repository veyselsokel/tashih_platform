<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import ContactForm from '@/Components/Contact/ContactForm.vue';
import ContactHeader from '@/Components/Contact/ContactHeader.vue';

defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
});
</script>

<template>
    <GuestLayout>
        <div class="bg-gradient-to-b from-green-50 to-white min-h-screen flex items-center justify-center">
            <div>
                <ContactHeader />
                <ContactForm />
            </div>
        </div>
    </GuestLayout>
</template>