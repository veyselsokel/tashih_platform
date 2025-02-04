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
    Link
} from 'lucide-vue-next';
import TextStyleControls from './TextStyleControls.vue';
import MarkdownControls from './MarkdownControls.vue';

const props = defineProps({
    modelValue: {
        type: Object,
        required: true
    },
    onFormatText: {
        type: Function,
        required: true
    }
});

const emit = defineEmits(['update:modelValue']);

const tabs = ref([
    { id: 'text-style', label: 'Metin Stili', icon: Type },
    { id: 'markdown', label: 'Markdown', icon: Code }
]);

const activeTab = ref('text-style');

const updateFormatting = (key, value) => {
    emit('update:modelValue', {
        ...props.modelValue,
        [key]: value
    });
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
        <div class="tab-content bg-white rounded-lg p-10">
            <TextStyleControls v-show="activeTab === 'text-style'" :formatting="modelValue"
                @update:formatting="updateFormatting" />

            <MarkdownControls v-show="activeTab === 'markdown'" :formatting="modelValue" @format="onFormatText" />
        </div>
    </div>
</template>

<style scoped>
.formatting-toolbar {
    @apply bg-white border rounded-lg shadow-sm overflow-hidden;
}
</style>