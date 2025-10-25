<template>
  <div class="flex items-center justify-end gap-2 mb-3 flex-wrap">
    <span v-if="reviewSummary.workflow_status === 'fully_approved'" class="inline-flex items-center gap-1 px-2 py-1 text-xs font-medium bg-green-600/20 text-green-400 rounded">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor">
        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
      </svg>
      ✅ {{ reviewSummary.status_message }}
    </span>
    <span v-else-if="reviewSummary.workflow_status === 'partially_approved'" class="inline-flex items-center gap-1 px-2 py-1 text-xs font-medium bg-blue-600/20 text-blue-400 rounded">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor">
        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
      </svg>
      {{ reviewSummary.status_message }}
    </span>
    <span v-else-if="reviewSummary.workflow_status === 'changes_requested'" class="inline-flex items-center gap-1 px-2 py-1 text-xs font-medium bg-red-600/20 text-red-400 rounded">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor">
        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
      </svg>
      ❌ {{ reviewSummary.status_message }}
    </span>
    <span v-else-if="reviewSummary.workflow_status === 'awaiting_re_review'" class="inline-flex items-center gap-1 px-2 py-1 text-xs font-medium bg-orange-600/20 text-orange-400 rounded">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor">
        <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd"/>
      </svg>
      🔄 {{ reviewSummary.status_message }}
    </span>
    <span v-else-if="reviewSummary.workflow_status === 'awaiting_initial_review'" class="inline-flex items-center gap-1 px-2 py-1 text-xs font-medium bg-yellow-600/20 text-yellow-400 rounded">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor">
        <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/>
        <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"/>
      </svg>
      ⏳ {{ reviewSummary.status_message }}
    </span>
    <span v-else-if="reviewSummary.workflow_status === 'review_in_progress'" class="inline-flex items-center gap-1 px-2 py-1 text-xs font-medium bg-purple-600/20 text-purple-400 rounded">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor">
        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
      </svg>
      🔍 {{ reviewSummary.status_message }}
    </span>
    <span v-else-if="reviewSummary.workflow_status === 'no_review_requested'" class="inline-flex items-center gap-1 px-2 py-1 text-xs font-medium bg-zinc-600/20 text-zinc-400 rounded">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor">
        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
      </svg>
      ➖ No review requested
    </span>
    <span v-else-if="reviewSummary.overall_status" class="inline-flex items-center gap-1 px-2 py-1 text-xs font-medium bg-gray-600/20 text-gray-400 rounded">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor">
        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
      </svg>
      {{ reviewSummary.overall_status }}
    </span>
  </div>
</template>
<script setup lang="ts">
import type { App } from '@/types/generated';
const props = defineProps<{ reviewSummary: any }>();
</script> 