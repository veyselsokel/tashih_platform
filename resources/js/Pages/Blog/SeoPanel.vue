<script setup>
import { computed } from 'vue';

const props = defineProps({
    title: {
        type: String,
        required: true
    },
    content: {
        type: String,
        required: true
    },
    metaDescription: {
        type: String,
        required: true
    },
    tags: {
        type: Array,
        required: true
    },
    hasFeaturedImage: {
        type: Boolean,
        required: true
    }
});

const emit = defineEmits(['update:metaDescription']);

// SEO Puanı Hesaplama
const seoScore = computed(() => {
    let score = 0;
    const checks = {
        title: { score: 20, check: () => titleLength.value >= 40 && titleLength.value <= 60 },
        metaDescription: { score: 20, check: () => metaDescriptionLength.value >= 120 && metaDescriptionLength.value <= 160 },
        content: { score: 20, check: () => wordCount.value >= 300 },
        tags: { score: 20, check: () => props.tags.length >= 3 },
        image: { score: 20, check: () => props.hasFeaturedImage }
    };

    Object.values(checks).forEach(({ score: points, check }) => {
        if (check()) score += points;
    });

    return score;
});

// Karakter Sayıları
const titleLength = computed(() => props.title.length);
const metaDescriptionLength = computed(() => props.metaDescription.length);
const wordCount = computed(() => props.content.trim().split(/\s+/).length);

// SEO Önerileri
const seoSuggestions = computed(() => {
    const suggestions = [];

    if (titleLength.value < 40) {
        suggestions.push({
            type: 'warning',
            message: `Başlık çok kısa (${titleLength.value} karakter). İdeal uzunluk 40-60 karakter arasında olmalı.`
        });
    } else if (titleLength.value > 60) {
        suggestions.push({
            type: 'warning',
            message: `Başlık çok uzun (${titleLength.value} karakter). İdeal uzunluk 40-60 karakter arasında olmalı.`
        });
    }

    if (metaDescriptionLength.value < 120) {
        suggestions.push({
            type: 'warning',
            message: `Meta açıklama çok kısa (${metaDescriptionLength.value} karakter). İdeal uzunluk 120-160 karakter arasında olmalı.`
        });
    } else if (metaDescriptionLength.value > 160) {
        suggestions.push({
            type: 'warning',
            message: `Meta açıklama çok uzun (${metaDescriptionLength.value} karakter). İdeal uzunluk 120-160 karakter arasında olmalı.`
        });
    }

    if (wordCount.value < 300) {
        suggestions.push({
            type: 'warning',
            message: `İçerik çok kısa (${wordCount.value} kelime). SEO için en az 300 kelime önerilir.`
        });
    }

    if (props.tags.length < 3) {
        suggestions.push({
            type: 'warning',
            message: `En az 3 etiket eklemeniz önerilir (şu an ${props.tags.length}).`
        });
    }

    if (!props.hasFeaturedImage) {
        suggestions.push({
            type: 'warning',
            message: 'Öne çıkan görsel ekleyin. Bu, sosyal medya paylaşımları için önemlidir.'
        });
    }

    return suggestions;
});

const getScoreColor = computed(() => {
    if (seoScore.value >= 80) return 'text-green-600';
    if (seoScore.value >= 60) return 'text-yellow-600';
    return 'text-red-600';
});
</script>

<template>
    <div class="rounded-lg border bg-white p-4 shadow-sm">
        <div class="mb-4 flex items-center justify-between">
            <h3 class="text-lg font-medium text-gray-900">SEO Analizi</h3>
            <div :class="['text-2xl font-bold', getScoreColor]">
                {{ seoScore }}/100
            </div>
        </div>

        <!-- Meta Açıklama -->
        <div class="mb-4">
            <label class="mb-2 block text-sm font-medium text-gray-700">
                Meta Açıklama
                <span class="ml-1 text-sm text-gray-500">
                    ({{ metaDescriptionLength }}/160)
                </span>
            </label>
            <textarea :value="metaDescription" @input="$emit('update:metaDescription', $event.target.value)" rows="2"
                maxlength="160"
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500"
                placeholder="Sayfanızın Google sonuçlarında görünecek açıklaması"></textarea>
        </div>

        <!-- SEO Önerileri -->
        <div class="space-y-2">
            <h4 class="font-medium text-gray-900">SEO Önerileri</h4>
            <ul class="space-y-2">
                <li v-for="(suggestion, index) in seoSuggestions" :key="index"
                    class="flex items-start space-x-2 text-sm">
                    <span :class="[
                        'mt-0.5 rounded-full p-1',
                        suggestion.type === 'warning' ? 'bg-yellow-100 text-yellow-600' : 'bg-green-100 text-green-600'
                    ]">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-4 w-4">
                            <path fill-rule="evenodd"
                                d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495zM10 5a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 5zm0 9a1 1 0 100-2 1 1 0 000 2z"
                                clip-rule="evenodd" />
                        </svg>
                    </span>
                    <span class="flex-1">{{ suggestion.message }}</span>
                </li>
            </ul>
        </div>
    </div>
</template>