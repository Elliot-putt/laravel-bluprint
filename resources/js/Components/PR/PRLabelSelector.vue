<template>
  <div>
    <div v-if="isLoadingRepoData">
      <label class="block text-sm font-medium text-zinc-300 mb-1">Labels</label>
      <DarkSkeleton class="h-10 w-full mb-1" variant="list" />
    </div>
    <LabelSelector
      v-else
      :labels="labels || []"
      :selectedLabels="selectedLabels"
      @update:selectedLabels="$emit('update:selected-labels', $event)"
      :disabled="!isGenerated"
    />
  </div>
</template>

<script setup lang="ts">
import { PropType } from 'vue';
import LabelSelector from '@/Components/PR/LabelSelector.vue';
import { DarkSkeleton } from '@/Components/ui/skeleton';

const props = defineProps({
  labels: Array as PropType<App.Data.LabelData[]>,
  selectedLabels: Array as PropType<(string | number)[]>,
  isLoadingRepoData: Boolean,
  isGenerated: Boolean
});

defineEmits<{ (e: 'update:selected-labels', value: (string | number)[]): void }>();
</script> 