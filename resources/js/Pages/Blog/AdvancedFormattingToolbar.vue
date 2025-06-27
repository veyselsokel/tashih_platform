<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import {
    Type, AlignLeft, AlignCenter, AlignRight, Bold, Italic,
    Underline, List, ListOrdered, Quote, Code, Link, Music,
    Palette, TextSelect, LayoutTemplate, Monitor
} from 'lucide-vue-next';
import { fontOptions, fontSizeOptions } from '@/constants/formatting';

const props = defineProps({
    modelValue: {
        type: Object,
        required: true
    },
    content: {
        type: String,
        default: ''
    }
});

const emit = defineEmits(['update:modelValue', 'update:content', 'format']);
const showColorPicker = ref(false);
const activeContentType = ref('normal');
const showPreview = ref(false);
const selectedText = ref('');
const isMobileView = ref(window.innerWidth < 768);

const handleResize = () => {
    isMobileView.value = window.innerWidth < 768;
};

onMounted(() => {
    document.addEventListener('selectionchange', updateSelectedText);
    window.addEventListener('resize', handleResize);
});

onUnmounted(() => {
    document.removeEventListener('selectionchange', updateSelectedText);
    window.removeEventListener('resize', handleResize);
});

const updateSelectedText = () => {
    selectedText.value = document?.getSelection()?.toString() || '';
};

const handleColorChange = (color) => {
    if (selectedText.value) {
        handleFormat((text) => `<span style="color: ${color}">${text}</span>`);
    } else {
        updateStyle('color', color);
    }
};
const contentTypes = [
    { id: 'normal', label: 'Normal Metin', icon: Type },
    { id: 'poem', label: 'Şiir', icon: Music },
    { id: 'code', label: 'Kod', icon: Code },
    { id: 'quote', label: 'Alıntı', icon: Quote }
];

// Line height seçeneklerini güncelleyelim
const lineHeightOptions = [
    { value: '1.2', label: 'Sıkışık' },
    { value: '1.5', label: 'Normal' },
    { value: '1.8', label: 'Geniş' },
    { value: '2', label: 'Çok Geniş' }
];

const templates = {
    poem: {
        font: 'Georgia, serif',
        fontSize: '18px',
        lineHeight: '1.8',
        textAlign: 'center',
        color: '#000000',
        contentStyle: 'poem'
    },
    code: {
        font: 'Courier New, monospace',
        fontSize: '16px',
        lineHeight: '1.5',
        textAlign: 'left',
        color: '#24292e',
        contentStyle: 'code'
    },
    quote: {
        font: 'Georgia, serif',
        fontSize: '18px',
        lineHeight: '1.8',
        textAlign: 'left',
        color: '#4a5568',
        contentStyle: 'quote'
    }
};


const updateStyle = (key, value) => {
    const newFormatting = {
        ...props.modelValue,
        [key]: value
    };
    emit('update:modelValue', newFormatting);
};

const applyContentType = (type) => {
    activeContentType.value = type;

    if (type in templates) {
        const newFormatting = {
            ...props.modelValue,
            ...templates[type]
        };
        emit('update:modelValue', newFormatting);

        if (type === 'poem') {
            const lines = props.content.split('\n');
            const formattedContent = lines
                .map(line => line.trim())
                .filter(line => line) // Boş satırları filtrele 
                .join('\n    ');
            emit('update:content', `    ${formattedContent}`); // Başlangıçta da girinti ekle
        }
    }
};

const formatTools = [
    {
        icon: Bold,
        label: 'Kalın',
        shortcut: '⌘+B',
        action: (text) => `**${text.trim()}**`
    },
    {
        icon: Italic,
        label: 'İtalik',
        shortcut: '⌘+I',
        action: (text) => `*${text.trim()}*`
    },
    {
        icon: Link,
        label: 'Bağlantı',
        shortcut: '⌘+K',
        action: (text) => {
            const url = prompt('Bağlantı URL:', 'https://');
            return url ? `[${text.trim() || 'bağlantı'}](${url})` : text;
        }
    },
    {
        icon: Code,
        label: 'Kod',
        shortcut: '⌘+`',
        action: (text) => {
            if (text.includes('\n')) {
                return `\`\`\`\n${text.trim()}\n\`\`\``;
            }
            return `\`${text.trim()}\``;
        }
    },
    {
        icon: Quote,
        label: 'Alıntı',
        shortcut: '⌘+Q',
        action: (text) => text.split('\n').map(line => `> ${line}`).join('\n')
    },
    {
        icon: List,
        label: 'Liste',
        shortcut: '⌘+L',
        action: (text) => text.split('\n').map(line => `- ${line.trim()}`).join('\n')
    },
    {
        icon: ListOrdered,
        label: 'Numaralı Liste',
        shortcut: '⌘+O',
        action: (text) => text.split('\n').map((line, i) => `${i + 1}. ${line.trim()}`).join('\n')
    },
    {
        icon: Palette,
        label: 'Metin Rengi',
        shortcut: '⌘+Shift+C',
        action: (text) => {
            const color = prompt('Renk kodunu girin (örn: #FF0000)', '#000000');
            if (color) {
                return `<span style="color: ${color}">${text}</span>`;
            }
            return text;
        }
    },
];

const presetColors = [
    { name: 'Siyah', value: '#000000' },
    { name: 'Koyu Gri', value: '#4A5568' },
    { name: 'Gri', value: '#718096' },
    { name: 'Açık Gri', value: '#A0AEC0' },
    { name: 'Lacivert', value: '#2C5282' },
    { name: 'Mavi', value: '#3182CE' },
    { name: 'Yeşil', value: '#38A169' },
    { name: 'Kırmızı', value: '#E53E3E' },
    { name: 'Turuncu', value: '#ED8936' },
    { name: 'Mor', value: '#805AD5' }
];

const handleFormat = (formatter) => {
    emit('format', formatter);
};
</script>

<template>
    <div class="formatting-toolbar space-y-4">
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-4 p-4 bg-gray-50 rounded-lg">
            <div class="flex items-center gap-2">
                <span class="text-sm font-medium text-gray-700 whitespace-nowrap">İçerik Türü:</span>
                <div class="flex flex-wrap gap-2">
                    <button v-for="type in contentTypes" :key="type.id" @click="applyContentType(type.id)" :class="[
                        'flex items-center px-3 py-1.5 rounded-md transition-colors min-w-[100px] justify-center',
                        activeContentType === type.id
                            ? 'bg-orange-100 text-orange-700'
                            : 'bg-white hover:bg-gray-100'
                    ]">
                        <component :is="type.icon" class="w-4 h-4 mr-2" />
                        <span class="text-sm">{{ type.label }}</span>
                    </button>
                </div>
            </div>
            <button @click="showPreview = !showPreview"
                class="sm:ml-auto flex items-center justify-center px-3 py-1.5 rounded-md bg-white hover:bg-gray-100">
                <Monitor class="w-4 h-4 mr-2" />
                <span class="text-sm">{{ showPreview ? 'Düzenle' : 'Önizle' }}</span>
            </button>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 p-4 border rounded-lg">
            <div class="space-y-4">
                <div class="flex flex-col sm:flex-row gap-2">
                    <select :value="modelValue.font" @change="updateStyle('font', $event.target.value)"
                        class="rounded-md border-gray-300 text-sm flex-1">
                        <option v-for="font in fontOptions" :key="font.value" :value="font.value">
                            {{ font.label }}
                        </option>
                    </select>
                    <select :value="modelValue.fontSize" @change="updateStyle('fontSize', $event.target.value)"
                        class="rounded-md border-gray-300 text-sm flex-1">
                        <option v-for="size in fontSizeOptions" :key="size.value" :value="size.value">
                            {{ size.label }}
                        </option>
                    </select>
                </div>

                <div class="flex flex-col sm:flex-row items-start sm:items-center gap-2">
                    <TextSelect class="w-4 h-4 text-gray-500" />
                    <div class="flex-1 grid grid-cols-2 sm:grid-cols-4 gap-1">
                        <button v-for="option in lineHeightOptions" :key="option.value"
                            @click="updateStyle('lineHeight', option.value)" :class="[
                                'px-2 py-1 text-xs rounded transition-colors',
                                modelValue.lineHeight === option.value
                                    ? 'bg-orange-100 text-orange-700'
                                    : 'bg-gray-100 hover:bg-gray-200'
                            ]">
                            {{ option.label }}
                        </button>
                    </div>
                </div>
            </div>

            <div class="space-y-4">
                <div class="flex justify-center space-x-1">
                    <button v-for="align in ['left', 'center', 'right']" :key="align"
                        @click="updateStyle('textAlign', align)" :class="[
                            'p-2 rounded-md transition-colors flex-1',
                            modelValue.textAlign === align
                                ? 'bg-orange-100 text-orange-700'
                                : 'bg-gray-100 hover:bg-gray-200'
                        ]">
                        <component :is="align === 'left' ? AlignLeft : align === 'center' ? AlignCenter : AlignRight"
                            class="w-4 h-4 mx-auto" />
                    </button>
                </div>

                <div class="color-picker-container">
                    <div class="p-2 rounded-lg bg-gray-50">
                        <div class="flex items-center justify-between mb-2 flex-wrap gap-2">
                            <span class="text-sm font-medium text-gray-700">Metin Rengi</span>
                            <div class="flex items-center space-x-2">
                                <div class="w-6 h-6 rounded border" :style="{ backgroundColor: modelValue.color }">
                                </div>
                                <span class="text-xs font-medium">{{ modelValue.color }}</span>
                            </div>
                        </div>

                        <div class="grid grid-cols-5 gap-2">
                            <button v-for="color in presetColors" :key="color.value"
                                @click="handleColorChange(color.value)"
                                class="w-8 h-8 rounded-lg border-2 transition-all hover:scale-110 relative group"
                                :class="modelValue.color === color.value ? 'border-orange-500 scale-110' : 'border-transparent'"
                                :style="{ backgroundColor: color.value }">
                                <span
                                    class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-1 px-2 py-1 text-xs text-white bg-gray-800 rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap">
                                    {{ color.name }}
                                </span>
                            </button>
                        </div>

                        <div class="mt-3 flex items-center gap-3">
                            <div class="flex flex-1">
                                <input type="color" :value="modelValue.color"
                                    @input="handleColorChange($event.target.value)" class="custom-color-input" />
                                <div class="ml-2">
                                    <span class="text-xs font-medium text-gray-700">Özel Renk</span>
                                    <div class="text-xs text-gray-500">
                                        {{ selectedText ? 'Seçili metne uygulanacak' : 'Tüm metne uygulanacak' }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex flex-wrap gap-2 p-4 border rounded-lg">
            <button v-for="tool in formatTools" :key="tool.label" @click="handleFormat(tool.action)"
                class="format-tool group relative">
                <div class="p-2 rounded-md bg-gray-100 hover:bg-gray-200 transition-colors">
                    <component :is="tool.icon" class="w-4 h-4" />
                </div>
                <div class="tooltip">
                    <span>{{ tool.label }}</span>
                    <span class="text-xs opacity-75">{{ tool.shortcut }}</span>
                </div>
            </button>
        </div>
    </div>
</template>


<style scoped>
.format-tool {
    @apply relative;
}

.tooltip {
    @apply invisible opacity-0 absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-2 py-1 bg-gray-800 text-white text-xs rounded flex flex-col items-center whitespace-nowrap transition-all duration-200 z-50;
}

.format-tool:hover .tooltip {
    @apply visible opacity-100;
}

:deep(.poem-content) {
    @apply font-serif text-lg leading-relaxed text-center whitespace-pre-wrap;
    padding: 0 2rem;
}

:deep(.code-content) {
    @apply font-mono text-base leading-normal bg-gray-50 p-4 rounded;
}

:deep(.quote-content) {
    @apply font-serif text-lg leading-relaxed italic border-l-4 border-orange-500 pl-4 my-4;
}

.custom-color-input {
    @apply appearance-none bg-transparent border border-gray-300 rounded-md p-0 cursor-pointer w-8 h-8;
}

input[type="color"] {
    @apply appearance-none p-0 w-8 h-8 border border-gray-300 rounded-md overflow-hidden;
}

input[type="color"]::-webkit-color-swatch-wrapper {
    @apply p-0;
}

input[type="color"]::-webkit-color-swatch,
input[type="color"]::-moz-color-swatch {
    @apply border-none rounded;
}

.color-btn {
    @apply transition-all duration-200;
}

.color-btn:hover,
.color-btn.active {
    @apply scale-110;
}

.color-btn:hover {
    box-shadow: 0 0 0 2px rgba(237, 137, 54, 0.4);
}

.color-btn.active {
    box-shadow: 0 0 0 2px rgba(237, 137, 54, 1);
}

@media (max-width: 640px) {
    .formatting-toolbar {
        @apply text-sm;
    }

    .color-picker-container {
        @apply overflow-x-auto;
    }

    .format-tool {
        @apply p-1.5;
    }

    .tooltip {
        @apply hidden;
    }
}
</style>