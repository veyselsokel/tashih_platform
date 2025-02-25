<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import CrosswordComponent from './Crossword/CrosswordComponent.vue';

const props = defineProps({
    title: {
        type: String,
        default: 'Bulmacalar'
    }
});

const puzzleData = {
    title: "Örnek Bulmaca",
    width: 10,
    height: 10
};
</script>

<template>
    <GuestLayout :title="title">
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <h1 class="text-2xl font-semibold mb-6">{{ title }}</h1>
                        <CrosswordComponent :puzzleData="puzzleData" />
                    </div>
                </div>
            </div>
        </div>
    </GuestLayout>
</template>