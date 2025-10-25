<template>
  <div>
    <div v-if="isLoadingRepoData">
      <label class="block text-sm font-medium text-zinc-300 mb-1">Reviewers</label>
      <DarkSkeleton class="h-10 w-full mb-1" variant="list" />
    </div>
    <ReviewerSelector
      v-else
      :reviewers="reviewers"
      :selectedReviewers="selectedReviewers"
      @update:selectedReviewers="$emit('update:selected-reviewers', $event)"
      :disabled="!isGenerated"
    />
  </div>
</template>

<script setup lang="ts">
import { PropType } from 'vue';
import ReviewerSelector from '@/Components/PR/ReviewerSelector.vue';
import { DarkSkeleton } from '@/Components/ui/skeleton';

const props = defineProps({
  reviewers: Array as PropType<App.Data.ReviewerData[]>,
  selectedReviewers: Array as PropType<(string | number)[]>,
  isLoadingRepoData: Boolean,
  isGenerated: Boolean
});

defineEmits<{ (e: 'update:selected-reviewers', value: (string | number)[]): void }>();
</script> 