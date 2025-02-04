<script setup>
const props = defineProps({
    formatting: {
        type: Object,
        required: true
    }
});

const emit = defineEmits(['format']);

const markdownTools = [
    {
        id: 'heading',
        icon: 'heading',
        label: 'Başlık',
        shortcut: 'Ctrl + H',
        format: (text) => `## ${text}`
    },
    {
        id: 'bold',
        icon: 'bold',
        label: 'Kalın',
        shortcut: 'Ctrl + B',
        format: (text) => `**${text}**`
    },
    {
        id: 'italic',
        icon: 'italic',
        label: 'İtalik',
        shortcut: 'Ctrl + I',
        format: (text) => `*${text}*`
    },
    {
        id: 'quote',
        icon: 'quote-right',
        label: 'Alıntı',
        shortcut: 'Ctrl + Q',
        format: (text) => text.split('\n').map(line => `> ${line}`).join('\n')
    },
    {
        id: 'code',
        icon: 'code',
        label: 'Kod',
        shortcut: 'Ctrl + K',
        format: (text) => `\`\`\`\n${text}\n\`\`\``
    },
    {
        id: 'link',
        icon: 'link',
        label: 'Bağlantı',
        shortcut: 'Ctrl + L',
        format: (text) => {
            const url = prompt('URL giriniz:');
            return url ? `[${text}](${url})` : text;
        }
    },
    {
        id: 'list-ul',
        icon: 'list-ul',
        label: 'Liste',
        shortcut: 'Ctrl + U',
        format: (text) => text.split('\n').map(line => `- ${line}`).join('\n')
    },
    {
        id: 'list-ol',
        icon: 'list-ol',
        label: 'Numaralı Liste',
        shortcut: 'Ctrl + O',
        format: (text) => text.split('\n').map((line, i) => `${i + 1}. ${line}`).join('\n')
    }
];
</script>

<template>
    <div class="markdown-controls">
        <!-- Markdown Toolbar -->
        <div class="flex flex-wrap gap-2">
            <button v-for="tool in markdownTools" :key="tool.id" type="button" @click="emit('format', tool.format)"
                class="markdown-tool-button group relative">
                <!-- Button Content -->
                <div
                    class="flex items-center justify-center p-2 rounded-md bg-gray-100 hover:bg-gray-200 transition-colors">
                    <i :class="['fas', 'fa-' + tool.icon]"></i>
                </div>

                <!-- Tooltip -->
                <div class="tooltip">
                    <span class="font-medium">{{ tool.label }}</span>
                    <span class="text-xs text-gray-500">{{ tool.shortcut }}</span>
                </div>
            </button>
        </div>

        <!-- Markdown Quick Reference -->
        <div class="mt-4 p-3 bg-gray-50 rounded-md text-sm">
            <h4 class="font-medium text-gray-700 mb-2">Markdown İpuçları</h4>
            <div class="grid grid-cols-2 gap-2 text-gray-600">
                <div># Başlık</div>
                <div>**Kalın**</div>
                <div>*İtalik*</div>
                <div>> Alıntı</div>
                <div>[Bağlantı](url)</div>
                <div>- Liste öğesi</div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.markdown-tool-button {
    @apply relative;
}

.tooltip {
    @apply invisible opacity-0 absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-3 py-2 bg-gray-800 text-white text-xs rounded-md flex flex-col items-center whitespace-nowrap transition-all duration-200;
}

.markdown-tool-button:hover .tooltip {
    @apply visible opacity-100;
}
</style>