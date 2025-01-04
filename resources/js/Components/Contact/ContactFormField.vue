<script setup>
defineProps({
    modelValue: {
        type: [String, Number],
        default: ''
    },
    id: {
        type: String,
        required: true
    },
    label: {
        type: String,
        required: true
    },
    type: {
        type: String,
        default: 'text'
    },
    error: {
        type: String,
        default: ''
    },
    rows: {
        type: Number,
        default: 3
    }
});

const emit = defineEmits(['update:modelValue']);
</script>

<template>
    <div class="col-span-1">
        <label :for="id" class="block text-sm font-medium text-gray-700">{{ label }}</label>
        <div class="mt-1 relative">
            <textarea v-if="type === 'textarea'" :id="id" :value="modelValue"
                @input="emit('update:modelValue', $event.target.value)" :rows="rows" required class="block w-full px-4 py-3 text-gray-900 border border-gray-300 rounded-lg 
                       shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent 
                       transition-all duration-200 resize-none" :class="{ 'border-red-300': error }" />
            <input v-else :id="id" :type="type" :value="modelValue"
                @input="emit('update:modelValue', $event.target.value)" required class="block w-full px-4 py-3 text-gray-900 border border-gray-300 rounded-lg 
                       shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent 
                       transition-all duration-200" :class="{ 'border-red-300': error }" />
            <div v-if="error" class="absolute -bottom-5 left-0 text-red-500 text-sm">
                {{ error }}
            </div>
        </div>
    </div>
</template>