<template>
  <div class="mb-4">
    <label for="prTitle" class="block text-sm font-medium text-zinc-300 mb-1">PR Title</label>
    <div v-if="isGenerating">
      <DarkSkeleton class="h-10 w-full" variant="list" />
    </div>
    <input
      v-else
      type="text"
      id="prTitle"
      :value="title"
      :disabled="!isGenerated"
      class="w-full px-4 py-2 border border-zinc-600 shadow-sm focus:ring-purple-500 focus:border-purple-500 bg-zinc-700/70 text-white disabled:opacity-50 disabled:cursor-not-allowed transition-shadow hover:bg-zinc-600/70"
      placeholder="Enter PR title"
      @input="$emit('update:title', $event.target.value)"
    />
  </div>
</template>

<script setup lang="ts">
import { PropType } from 'vue';
import { DarkSkeleton } from '@/Components/ui/skeleton';

const props = defineProps({
  title: {
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

defineEmits<{ (e: 'update:title', value: string): void }>();
</script> 