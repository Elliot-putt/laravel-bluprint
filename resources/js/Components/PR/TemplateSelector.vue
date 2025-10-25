<template>
    <div class="mb-6">
        <Select
            :options="templates"
            :model-value="selectedTemplateId"
            @update:modelValue="onSelectTemplate"
            label="PR Template"
            placeholder="Select a template..."
            :getOptionLabel="(template: any) => template.name"
            :getOptionKey="(template: any) => template.id"
            :optionDescription="(template: any) => template.description || ''"
            :disabled="disabled"
        />
        <p class="text-zinc-400 text-xs mt-1">Select a template for AI to use when generating PR content.</p>
    </div>
</template>

<script setup lang="ts">
import { PropType } from 'vue';
import Select from '@/Components/ui/Select.vue';

const props = defineProps({
    templates: {
        type: Array as PropType<any[]>,
        default: () => []
    },
    defaultTemplateId: {
        type: Number,
        default: null
    },
    disabled: {
        type: Boolean,
        default: false
    }
});

const emit = defineEmits(['update:selectedTemplateId']);

const selectedTemplateId = defineModel('selectedTemplateId', {
    type: Number,
    default: null
});

function onSelectTemplate(templateId: number) {
    emit('update:selectedTemplateId', templateId);
}
</script>
