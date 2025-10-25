<template>
    <div>
        <div class="flex justify-between items-center mb-1">
            <label  class="block text-sm font-medium text-zinc-300"></label>
            <div class="flex items-center space-x-4">
                <!-- Media Uploader Button (only show when PR is generated) -->
                <MediaUploader
                    v-if="!disabled"
                    @insert-media="insertMediaContent"
                />

                <!-- Edit/Preview Toggle -->
                <div class="flex items-center">
                    <span class="text-xs text-zinc-400 mr-2">{{ isPreviewMode ? 'Preview' : 'Edit' }}</span>
                    <button
                        type="button"
                        class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2"
                        :class="[isPreviewMode ? 'bg-purple-600' : 'bg-zinc-700']"
                        @click="togglePreviewMode"
                        :disabled="disabled"
                    >
                        <span
                            class="pointer-events-none relative inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"
                            :class="[isPreviewMode ? 'translate-x-5' : 'translate-x-0']"
                        ></span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Markdown textarea - show when preview is off -->
        <textarea
            v-if="!isPreviewMode"
            id="prBody"
            v-model="localValue"
            :disabled="disabled"
            class="w-full h-80 px-4 py-2 border border-zinc-600 shadow-sm focus:ring-purple-500 focus:border-purple-500 bg-zinc-700/70 text-white disabled:opacity-50 disabled:cursor-not-allowed transition-shadow hover:bg-zinc-600/70"
            placeholder="Enter pull request description"
        ></textarea>

        <!-- Rendered preview - show when preview is on -->
        <div
            v-else
            class="w-full h-80 px-4 py-2 border border-zinc-600 shadow-sm bg-zinc-700/70 text-white overflow-auto markdown-body github-markdown-preview"
            v-html="renderedMarkdown"
        ></div>
    </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import { marked } from 'marked';
import MediaUploader from '@/Components/PR/MediaUploader.vue';

const props = defineProps({
    modelValue: {
        type: String,
        default: ''
    },
    disabled: {
        type: Boolean,
        default: false
    }
});

const emit = defineEmits(['update:modelValue']);

const localValue = ref(props.modelValue);
const isPreviewMode = ref(false);

// Update local value when prop changes
watch(() => props.modelValue, (newValue) => {
    localValue.value = newValue;
});

// Emit changes when local value changes
watch(() => localValue.value, (newValue) => {
    emit('update:modelValue', newValue);
});

// Toggle preview mode
const togglePreviewMode = () => {
    isPreviewMode.value = !isPreviewMode.value;
};

// Render markdown
const renderedMarkdown = computed(() => {
    if (!localValue.value) return '';

    try {
        return marked.parse(localValue.value);
    } catch (error) {
        console.error('Error parsing markdown:', error);
        return '<p class="text-red-500">Error rendering markdown</p>';
    }
});

// Insert media content into the editor
const insertMediaContent = (markdownContent) => {
    // If the textarea is empty, just set the content
    if (!localValue.value) {
        localValue.value = markdownContent;
        return;
    }

    // Otherwise, append to the end with a newline
    localValue.value += '\n\n' + markdownContent;
};

// Initialize marked
onMounted(() => {
    marked.use({
        gfm: true,           // GitHub Flavored Markdown
        breaks: true,        // Convert \n to <br>
        smartLists: true,    // Use smarter list behavior
        headerIds: true,     // Add ids to headers
        highlight: function(code, lang) {
            return code; // You could add syntax highlighting here if needed
        }
    });
});
</script>

<style>
/* GitHub Markdown Styling */
.github-markdown-preview {
    color: #f0f6fc !important;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Helvetica, Arial, sans-serif !important;
    font-size: 14px !important;
    line-height: 1.5 !important;
    word-wrap: break-word !important;
}

.github-markdown-preview h1,
.github-markdown-preview h2,
.github-markdown-preview h3,
.github-markdown-preview h4,
.github-markdown-preview h5,
.github-markdown-preview h6 {
    margin-top: 24px !important;
    margin-bottom: 16px !important;
    font-weight: 600 !important;
    line-height: 1.25 !important;
}

.github-markdown-preview h1 {
    font-size: 2em !important;
    padding-bottom: 0.3em !important;
    border-bottom: 1px solid #30363d !important;
}

.github-markdown-preview h2 {
    font-size: 1.5em !important;
    padding-bottom: 0.3em !important;
    border-bottom: 1px solid #30363d !important;
}

.github-markdown-preview h3 {
    font-size: 1.25em !important;
}

.github-markdown-preview h4 {
    font-size: 1em !important;
}

.github-markdown-preview h5 {
    font-size: 0.875em !important;
}

.github-markdown-preview h6 {
    font-size: 0.85em !important;
    color: #8b949e !important;
}

.github-markdown-preview p {
    margin-top: 0 !important;
    margin-bottom: 16px !important;
}

.github-markdown-preview ul,
.github-markdown-preview ol {
    padding-left: 2em !important;
    margin-top: 0 !important;
    margin-bottom: 16px !important;
}

.github-markdown-preview ul {
    list-style-type: disc !important;
}

.github-markdown-preview ol {
    list-style-type: decimal !important;
}

.github-markdown-preview li {
    display: list-item !important;
    margin-bottom: 0.25em !important;
}

.github-markdown-preview li + li {
    margin-top: 0.25em !important;
}

.github-markdown-preview code {
    padding: 0.2em 0.4em !important;
    margin: 0 !important;
    font-family: ui-monospace, SFMono-Regular, SF Mono, Menlo, Consolas, "Liberation Mono", monospace !important;
    font-size: 85% !important;
    background-color: rgba(110, 118, 129, 0.4) !important;
    border-radius: 3px !important;
}

.github-markdown-preview pre {
    padding: 16px !important;
    overflow: auto !important;
    font-size: 85% !important;
    line-height: 1.45 !important;
    background-color: #161b22 !important;
    border-radius: 6px !important;
    margin-bottom: 16px !important;
}

.github-markdown-preview pre code {
    padding: 0 !important;
    margin: 0 !important;
    font-size: 100% !important;
    background-color: transparent !important;
    border: 0 !important;
    word-break: normal !important;
    white-space: pre !important;
}

.github-markdown-preview blockquote {
    padding: 0 1em !important;
    color: #8b949e !important;
    border-left: 0.25em solid #30363d !important;
    margin: 0 0 16px 0 !important;
}

.github-markdown-preview hr {
    height: 0.25em !important;
    padding: 0 !important;
    margin: 24px 0 !important;
    background-color: #30363d !important;
    border: 0 !important;
}

.github-markdown-preview img {
    max-width: 100% !important;
    box-sizing: content-box !important;
    background-color: #0d1117 !important;
}

.github-markdown-preview table {
    display: block !important;
    width: 100% !important;
    overflow: auto !important;
    margin-top: 0 !important;
    margin-bottom: 16px !important;
    border-spacing: 0 !important;
    border-collapse: collapse !important;
}

.github-markdown-preview table th {
    font-weight: 600 !important;
}

.github-markdown-preview table th,
.github-markdown-preview table td {
    padding: 6px 13px !important;
    border: 1px solid #30363d !important;
}

.github-markdown-preview table tr {
    background-color: #0d1117 !important;
    border-top: 1px solid #30363d !important;
}

.github-markdown-preview table tr:nth-child(2n) {
    background-color: #161b22 !important;
}

.github-markdown-preview a {
    color: #58a6ff !important;
    text-decoration: none !important;
}

.github-markdown-preview a:hover {
    text-decoration: underline !important;
}
</style>
