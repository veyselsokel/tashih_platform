<script setup>
import { computed } from 'vue';
import MarkdownIt from 'markdown-it';

const md = new MarkdownIt({
    html: true,
    linkify: true,
    typographer: true,
    breaks: true
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
    if (!props.content) return '';
    
    // First process the content to handle our custom formatting
    let processedContent = props.content;
    
    // Convert markdown and preserve HTML color spans
    let rendered = md.render(processedContent);
    
    return rendered;
});

const previewStyles = computed(() => {
    return {
        fontFamily: props.formatting.font || 'Arial',
        fontSize: props.formatting.fontSize || '16px',
        textAlign: props.formatting.textAlign || 'left',
        color: props.formatting.color || '#333333',
        lineHeight: props.formatting.lineHeight || '1.5'
    };
});

const containerClasses = computed(() => {
    const classes = ['prose', 'max-w-none', 'rounded-md', 'border', 'p-4', 'preview-content'];
    
    // Add content type specific classes
    if (props.formatting.contentStyle) {
        classes.push(`${props.formatting.contentStyle}-content`);
    }
    
    return classes.join(' ');
});
</script>

<template>
    <div :class="containerClasses" :style="previewStyles" v-html="renderedContent"></div>
</template>

<style scoped>
.preview-content {
    min-height: 200px;
    overflow-wrap: break-word;
    word-wrap: break-word;
}

.prose {
    max-width: none;
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

/* Handle inline color spans */
.prose span[style*="color"] {
    display: inline;
}

/* Content type specific styles */
.poem-content {
    @apply font-serif text-lg leading-relaxed text-center whitespace-pre-wrap;
    padding: 0 2rem;
}

.code-content {
    @apply font-mono text-base leading-normal bg-gray-50 p-4 rounded;
}

.quote-content {
    @apply font-serif text-lg leading-relaxed italic border-l-4 border-orange-500 pl-4 my-4;
}

/* Improved readability for preview */
.preview-content p:last-child {
    margin-bottom: 0;
}

.preview-content:empty::before {
    content: "Önizleme için içerik yazın...";
    color: #9CA3AF;
    font-style: italic;
}
</style>