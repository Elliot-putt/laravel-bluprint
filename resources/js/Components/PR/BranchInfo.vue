<template>
  <div class="flex items-center gap-2 text-sm text-zinc-400 mb-3">
    <span class="font-mono text-xs bg-zinc-700/50 px-2 py-1 rounded">
      {{ pullRequest.head?.ref || 'unknown' }}
    </span>
    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
      <path fill-rule="evenodd" d="M10.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L12.586 11H5a1 1 0 110-2h7.586l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd"/>
    </svg>
    <span class="font-mono text-xs px-2 py-1 rounded" :class="pullRequest.is_behind ? 'bg-amber-600/20 text-amber-400 border border-amber-600/30' : 'bg-zinc-700/50'">
      {{ pullRequest.base?.ref || 'unknown' }}
      <span v-if="pullRequest.is_behind">(Behind)</span>
    </span>
  </div>
</template>
<script setup lang="ts">
import type { App } from '@/types/generated';
const props = defineProps<{ pullRequest: App.Data.PullRequest }>();
</script> 