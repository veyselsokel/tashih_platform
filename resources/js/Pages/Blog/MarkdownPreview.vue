<script setup>
import { computed } from 'vue';
import MarkdownIt from 'markdown-it';

const md = new MarkdownIt({
    html: true,
    linkify: true,
    typographer: true,
    breaks: true,
    highlight: function (str, lang) {
        if (lang && hljs.getLanguage(lang)) {
            try {
                return hljs.highlight(str, { language: lang }).value;
            } catch (__) { }
        }
        return ''; // use external default escaping
    }
});

const props = defineProps({
    content: {
        type: String,
        required: true
    },
    formatting: {
        type: Object,
        required: true
    }
});

const renderedContent = computed(() => {
    return md.render(props.content);
});

const previewStyles = computed(() => {
    return {
        fontFamily: props.formatting.font,
        fontSize: props.formatting.fontSize,
        textAlign: props.formatting.textAlign,
        color: props.formatting.color,
        lineHeight: props.formatting.lineHeight
    };
});
</script>

<template>
    <div class="prose max-w-none rounded-md border p-4" :style="previewStyles" v-html="renderedContent"></div>
</template>

<style>
.prose {
    max-width: 65ch;
    margin: 0 auto;
}

.prose h1 {
    @apply text-2xl font-bold mb-4 mt-6;
}

.prose h2 {
    @apply text-xl font-bold mb-3 mt-5;
}

.prose h3 {
    @apply text-lg font-bold mb-2 mt-4;
}

.prose p {
    @apply mb-4;
}

.prose ul {
    @apply list-disc list-inside mb-4;
}

.prose ol {
    @apply list-decimal list-inside mb-4;
}

.prose blockquote {
    @apply border-l-4 border-gray-300 pl-4 italic my-4;
}

.prose pre {
    @apply bg-gray-100 rounded-md p-4 overflow-x-auto my-4;
}

.prose code {
    @apply bg-gray-100 px-1 py-0.5 rounded-md text-sm;
}

.prose a {
    @apply text-orange-600 hover:text-orange-700 underline;
}

.prose img {
    @apply max-w-full h-auto rounded-lg my-4;
}

.prose table {
    @apply w-full border-collapse my-4;
}

.prose th,
.prose td {
    @apply border border-gray-300 p-2;
}

.prose thead {
    @apply bg-gray-50;
}
</style>