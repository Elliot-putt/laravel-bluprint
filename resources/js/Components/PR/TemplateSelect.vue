<template>
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
  <p class="mt-1 text-xs text-zinc-400">Select a template for AI to use when generating PR content.</p>
</template>

<script setup lang="ts">
import { PropType } from 'vue';
import Select from '@/Components/ui/Select.vue';

const props = defineProps({
  templates: Array as PropType<App.Data.TemplateData[]>,
  selectedTemplateId: Number,
  disabled: Boolean
});

const emit = defineEmits(['update:selectedTemplateId']);

function onSelectTemplate(templateId: number) {
  emit('update:selectedTemplateId', templateId);
}
</script> 