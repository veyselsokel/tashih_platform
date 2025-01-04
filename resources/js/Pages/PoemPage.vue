<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import PoemSection from '@/Components/Poem/PoemSection.vue';

defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
});
</script>

<template>
    <GuestLayout>
        <div class="bg-gradient-to-b from-green-50 to-white min-h-screen flex items-center justify-center">
            <div>
                <PoemSection />
            </div>
        </div>
    </GuestLayout>
</template>