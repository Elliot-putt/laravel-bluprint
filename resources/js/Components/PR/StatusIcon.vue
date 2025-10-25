<template>
  <div class="flex-shrink-0 relative group">
    <div v-if="pullRequest.state === 'open'"
         class="flex items-center justify-center w-6 h-6 rounded-full"
         :class="pullRequest.is_draft ? 'bg-zinc-600' : 'bg-green-600'">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 text-white" viewBox="0 0 20 20" fill="currentColor">
        <path fill-rule="evenodd" d="M7.707 3.293a1 1 0 010 1.414L5.414 7H11a7 7 0 017 7v2a1 1 0 11-2 0v-2a5 5 0 00-5-5H5.414l2.293 2.293a1 1 0 11-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd"/>
      </svg>
    </div>
    <div v-else-if="pullRequest.isMerged || pullRequest.merged_at"
         class="flex items-center justify-center w-6 h-6 bg-purple-600 rounded-full">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 text-white" viewBox="0 0 20 20" fill="currentColor">
        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
      </svg>
    </div>
    <div v-else class="flex items-center justify-center w-6 h-6 bg-red-600 rounded-full">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 text-white" viewBox="0 0 20 20" fill="currentColor">
        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
      </svg>
    </div>
    <div class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-2 py-1 text-xs text-white bg-zinc-800 rounded opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none whitespace-nowrap z-10">
      {{ statusTooltipText }}
    </div>
  </div>
</template>
<script setup lang="ts">
import { computed } from 'vue';
import type { App } from '@/types/generated';
const props = defineProps<{ pullRequest: App.Data.PullRequest }>();
const statusTooltipText = computed(() => {
  if (props.pullRequest.state === 'open') {
    return props.pullRequest.is_draft ? 'Draft Pull Request' : 'Open Pull Request';
  } else if (props.pullRequest.isMerged || props.pullRequest.merged_at) {
    return 'Merged Pull Request';
  } else {
    return 'Closed Pull Request';
  }
});
</script> 