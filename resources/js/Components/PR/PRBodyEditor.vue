<template>
  <div class="mb-4 flex-grow">
    <label for="prBody" class="block text-sm font-medium text-zinc-300 mb-1">PR Body</label>
    <div v-if="isGenerating" class="flex-grow">
      <DarkSkeleton class="h-4 w-full" variant="dashboard" />
    </div>
    <MarkdownEditor
      v-else
      :model-value="body"
      :disabled="!isGenerated"
      class="flex-grow"
      @update:modelValue="$emit('update:body', $event)"
    />
  </div>
</template>

<script setup lang="ts">
import { PropType } from 'vue';
import MarkdownEditor from '@/Components/PR/MarkdownEditor.vue';
import { DarkSkeleton } from '@/Components/ui/skeleton';

const props = defineProps({
  body: {
    type: String,
    default: ''
  },
  isGenerating: {
    type: Boolean,
    default: false
  },
  isGenerated: {
    type: Boolean,
    default: false
  }
});

defineEmits<{ (e: 'update:body', value: string): void }>();
</script> 