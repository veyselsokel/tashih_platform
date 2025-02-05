<script setup>
import { ref } from 'vue';
import {
    Type,
    AlignLeft,
    AlignCenter,
    AlignRight,
    Bold,
    Italic,
    Underline,
    List,
    ListOrdered,
    Quote,
    Code,
    Link,
    Music,
    Monitor,
    Layout, // LayoutTemplate yerine Layout kullanıyoruz
    Palette,
    TextSelect
} from 'lucide-vue-next';
import AdvancedFormattingToolbar from './AdvancedFormattingToolbar.vue';
import TextStyleControls from './TextStyleControls.vue';
import MarkdownControls from './MarkdownControls.vue';

const props = defineProps({
    modelValue: {
        type: Object,
        required: true
    },
    content: {
        type: String,
        default: ''
    },
    onFormatText: {
        type: Function,
        required: true
    }
});

const emit = defineEmits(['update:modelValue', 'update:content']);

const tabs = ref([
    { id: 'advanced', label: 'Gelişmiş Düzenleme', icon: Layout },
    { id: 'text-style', label: 'Metin Stili', icon: Type },
    { id: 'markdown', label: 'Markdown', icon: Code }
]);

const activeTab = ref('text-style'); // varsayılan olarak text-style sekmesini aktif yapıyoruz

const updateFormatting = (key, value) => {
    emit('update:modelValue', {
        ...props.modelValue,
        [key]: value
    });
};

const handleContentUpdate = (newContent) => {
    emit('update:content', newContent);
};
</script>

<template>
    <div class="formatting-toolbar">
        <!-- Tab Navigation -->
        <nav class="flex items-center border-b border-gray-200 mb-4">
            <div class="flex space-x-1">
                <button v-for="tab in tabs" :key="tab.id" @click="activeTab = tab.id" type="button" :class="[
                    'flex items-center px-4 py-2 text-sm font-medium rounded-t-lg transition-all',
                    activeTab === tab.id
                        ? 'bg-orange-50 text-orange-600 border-t border-l border-r border-gray-200'
                        : 'text-gray-600 hover:text-gray-800 hover:bg-gray-50'
                ]">
                    <component :is="tab.icon" class="w-4 h-4 mr-2" />
                    {{ tab.label }}
                </button>
            </div>
        </nav>

        <!-- Tab Panels -->
        <div class="tab-content bg-white rounded-lg p-6">
            <!-- Gelişmiş Düzenleme Paneli -->
            <AdvancedFormattingToolbar v-if="activeTab === 'advanced'" :modelValue="modelValue" :content="content"
                @update:modelValue="emit('update:modelValue', $event)" @update:content="handleContentUpdate"
                @format="onFormatText" />

            <!-- Metin Stili Paneli -->
            <TextStyleControls v-if="activeTab === 'text-style'" :formatting="modelValue"
                @update:formatting="updateFormatting" />

            <!-- Markdown Paneli -->
            <MarkdownControls v-if="activeTab === 'markdown'" :formatting="modelValue" @format="onFormatText" />
        </div>
    </div>
</template>

<style scoped>
.formatting-toolbar {
    @apply bg-white border rounded-lg shadow-sm overflow-hidden;
}

.tab-content>div {
    @apply transition-all duration-200 ease-in-out;
}
</style>