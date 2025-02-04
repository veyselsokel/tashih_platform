<script setup>
import {
    AlignLeft,
    AlignCenter,
    AlignRight,
    Type,
    TextSelect,
    Palette
} from 'lucide-vue-next';
import { ref } from 'vue';
import { fontOptions, fontSizeOptions } from '@/constants/formatting';

const props = defineProps({
    formatting: {
        type: Object,
        required: true
    }
});

const emit = defineEmits(['update:formatting']);
const showColorPicker = ref(false);

const updateStyle = (key, value) => {
    emit('update:formatting', key, value);
};

const alignmentOptions = [
    { value: 'left', icon: AlignLeft, label: 'Sola Hizala' },
    { value: 'center', icon: AlignCenter, label: 'Ortala' },
    { value: 'right', icon: AlignRight, label: 'Sağa Hizala' }
];

const lineHeightOptions = [
    { value: '1', label: 'Sıkışık' },
    { value: '1.5', label: 'Normal' },
    { value: '2', label: 'Geniş' }
];
</script>

<template>
    <div class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Font Controls -->
            <div class="space-y-4">
                <!-- Font Family -->
                <div class="form-group">
                    <label class="flex items-center text-sm font-medium text-gray-700 mb-1.5">
                        <Type class="w-4 h-4 mr-2" />
                        Yazı Tipi
                    </label>
                    <select :value="formatting.font" @change="updateStyle('font', $event.target.value)"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 text-sm">
                        <option v-for="font in fontOptions" :key="font.value" :value="font.value">
                            {{ font.label }}
                        </option>
                    </select>
                </div>

                <!-- Font Size -->
                <div class="form-group">
                    <label class="flex items-center text-sm font-medium text-gray-700 mb-1.5">
                        <Type class="w-4 h-4 mr-2" />
                        Boyut
                    </label>
                    <select :value="formatting.fontSize" @change="updateStyle('fontSize', $event.target.value)"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 text-sm">
                        <option v-for="size in fontSizeOptions" :key="size.value" :value="size.value">
                            {{ size.label }}
                        </option>
                    </select>
                </div>
            </div>

            <div class="space-y-4">
                <!-- Line Height -->
                <div class="form-group">
                    <label class="flex items-center text-sm font-medium text-gray-700 mb-1.5">
                        <TextSelect class="w-4 h-4 mr-2" />
                        Satır Aralığı
                    </label>
                    <div class="flex space-x-2">
                        <button v-for="option in lineHeightOptions" :key="option.value"
                            @click="updateStyle('lineHeight', option.value)" :class="[
                                'flex-1 px-3 py-1.5 rounded text-sm font-medium transition-colors',
                                formatting.lineHeight === option.value
                                    ? 'bg-orange-100 text-orange-700'
                                    : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
                            ]">
                            {{ option.label }}
                        </button>
                    </div>
                </div>

                <!-- Text Color -->
                <div class="form-group">
                    <label class="flex items-center text-sm font-medium text-gray-700 mb-1.5">
                        <Palette class="w-4 h-4 mr-2" />
                        Renk
                    </label>
                    <div class="relative">
                        <button @click="showColorPicker = !showColorPicker"
                            class="w-full flex items-center justify-between px-3 py-2 border rounded-md hover:border-orange-500 transition-colors">
                            <div class="flex items-center space-x-2">
                                <div class="w-4 h-4 rounded-full border" :style="{ backgroundColor: formatting.color }">
                                </div>
                                <span class="text-sm">{{ formatting.color }}</span>
                            </div>
                        </button>
                        <div v-if="showColorPicker" class="absolute mt-1 p-2 bg-white rounded-lg shadow-xl border">
                            <input type="color" :value="formatting.color"
                                @change="updateStyle('color', $event.target.value)" class="w-full h-32" />
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Text Alignment -->
        <div class="border-t pt-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">Hizalama</label>
            <div class="flex space-x-2">
                <button v-for="align in alignmentOptions" :key="align.value" type="button"
                    @click="updateStyle('textAlign', align.value)" :class="[
                        'flex items-center justify-center p-2 rounded-md transition-colors',
                        formatting.textAlign === align.value
                            ? 'bg-orange-500 text-white'
                            : 'bg-gray-100 hover:bg-gray-200 text-gray-700'
                    ]" :title="align.label">
                    <component :is="align.icon" class="w-4 h-4" />
                </button>
            </div>
        </div>
    </div>
</template>