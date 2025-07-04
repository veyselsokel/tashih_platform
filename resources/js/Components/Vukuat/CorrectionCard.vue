<script setup>
import { defineProps, defineEmits } from 'vue';

const props = defineProps({
    item: {
        type: Object,
        required: true
    }
});

const emit = defineEmits(['image-click']);
</script>

<template>
    <div class="bg-white rounded-xl shadow-lg overflow-hidden transition-all duration-300 hover:shadow-2xl hover:-translate-y-1">
        <div class="cursor-pointer" @click="emit('image-click')">
            <img :src="item.image" :alt="item.description" class="w-full h-auto object-cover">
        </div>
        <div class="p-6">
            <span class="inline-block bg-orange-100 text-orange-800 text-xs font-semibold px-2.5 py-1 rounded-full mb-4">{{ item.category }}</span>
            <p class="text-gray-700 leading-relaxed">{{ item.description }}</p>
        </div>
    </div>
</template>