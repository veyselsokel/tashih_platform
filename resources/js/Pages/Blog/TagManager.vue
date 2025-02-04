<script setup>
import { ref } from 'vue';

const props = defineProps({
    modelValue: {
        type: Array,
        required: true
    },
    maxTags: {
        type: Number,
        default: 10
    }
});

const emit = defineEmits(['update:modelValue']);
const newTag = ref('');

const addTag = (event) => {
    event.preventDefault();
    const tag = newTag.value.trim();

    if (tag && !props.modelValue.includes(tag) && props.modelValue.length < props.maxTags) {
        emit('update:modelValue', [...props.modelValue, tag]);
        newTag.value = '';
    }
};

const removeTag = (index) => {
    const updatedTags = [...props.modelValue];
    updatedTags.splice(index, 1);
    emit('update:modelValue', updatedTags);
};

const handleKeydown = (event) => {
    if (event.key === 'Enter') {
        addTag(event);
    } else if (event.key === 'Backspace' && !newTag.value && props.modelValue.length > 0) {
        removeTag(props.modelValue.length - 1);
    }
};
</script>

<template>
    <div class="space-y-2">
        <label class="block text-sm font-medium text-gray-700">
            Etiketler
        </label>

        <div
            class="min-h-[2.5rem] rounded-md border border-gray-300 p-2 focus-within:border-orange-500 focus-within:ring-1 focus-within:ring-orange-500">
            <div class="flex flex-wrap gap-2">
                <!-- Mevcut Etiketler -->
                <span v-for="(tag, index) in modelValue" :key="index"
                    class="inline-flex items-center rounded-full bg-orange-100 px-3 py-1 text-sm">
                    <span class="text-orange-800">{{ tag }}</span>
                    <button type="button" @click="removeTag(index)"
                        class="ml-1.5 inline-flex h-4 w-4 items-center justify-center rounded-full text-orange-600 hover:bg-orange-200 hover:text-orange-800 focus:outline-none">
                        <span class="sr-only">Etiketi kaldır</span>
                        ×
                    </button>
                </span>

                <!-- Etiket Giriş Alanı -->
                <input v-model="newTag" type="text" placeholder="Etiket eklemek için yazın ve Enter'a basın"
                    class="flex-1 border-0 bg-transparent p-0 text-sm focus:outline-none focus:ring-0"
                    @keydown="handleKeydown" />
            </div>
        </div>

        <!-- Yardımcı Metin -->
        <p class="text-sm text-gray-500">
            {{ modelValue.length }}/{{ maxTags }} etiket
            <span v-if="modelValue.length === maxTags" class="text-orange-500">
                (Maksimum limite ulaşıldı)
            </span>
        </p>
    </div>
</template>