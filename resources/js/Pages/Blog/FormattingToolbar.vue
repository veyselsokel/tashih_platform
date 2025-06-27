<script setup>
import AdvancedFormattingToolbar from './AdvancedFormattingToolbar.vue';

defineProps({
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
</script>

<template>
    <div class="formatting-toolbar bg-white border rounded-lg shadow-sm overflow-hidden p-4">
        <AdvancedFormattingToolbar
            :modelValue="modelValue"
            :content="content"
            @update:modelValue="emit('update:modelValue', $event)"
            @update:content="emit('update:content', $event)"
            @format="onFormatText"
        />
    </div>
</template>
