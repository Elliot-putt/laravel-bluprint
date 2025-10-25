<template>
  <div>
    <div class="flex items-center justify-end gap-4 text-sm text-zinc-400 mb-3">
      <div v-if="pullRequest.comments > 0" class="flex items-center gap-1">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
          <path fill-rule="evenodd" d="M18 10c0 3.866-3.582 7-8 7a8.841 8.841 0 01-4.083-.98L2 17l1.338-3.123C2.493 12.767 2 11.434 2 10c0-3.866 3.582-7 8-7s8 3.134 8 7zM7 9H5v2h2V9zm8 0h-2v2h2V9zM9 9h2v2H9V9z" clip-rule="evenodd"/>
        </svg>
        <span>{{ pullRequest.comments }}</span>
      </div>
      <div v-if="pullRequest.commits > 0" class="flex items-center gap-1">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
          <path d="M3 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z"/>
        </svg>
        <span>{{ pullRequest.commits }}</span>
      </div>
      <div v-if="pullRequest.changed_files > 0" class="flex items-center gap-1">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
          <path d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z"/>
        </svg>
        <span>{{ pullRequest.changed_files }}</span>
      </div>
    </div>
    <div v-if="pullRequest.additions || pullRequest.deletions" class="flex items-center justify-end gap-2 text-sm mb-3">
      <span v-if="pullRequest.additions" class="text-green-400 font-mono">+{{ pullRequest.additions }}</span>
      <span v-if="pullRequest.deletions" class="text-red-400 font-mono">-{{ pullRequest.deletions }}</span>
    </div>
  </div>
</template>
<script setup lang="ts">
import type { App } from '@/types/generated';
const props = defineProps<{ pullRequest: App.Data.PullRequest }>();
</script> 