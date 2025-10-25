<template>
  <Deferred data="recentlyMadeBranches">
    <template #fallback>
      <div class="bg-zinc-800/50 border border-zinc-700 rounded-lg shadow-lg backdrop-blur-sm mb-6">
        <div class="p-6 border-b border-zinc-700/50">
          <div class="flex items-center gap-3">
            <div class="flex items-center justify-center w-10 h-10 bg-gradient-to-br from-purple-600 to-pink-600 rounded-lg shadow-lg">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd" />
              </svg>
            </div>
            <div>
              <h3 class="text-lg font-semibold text-white">Recently Pushed Branches</h3>
              <p class="text-sm text-zinc-400">Branches that might need pull requests</p>
            </div>
          </div>
          <div class="flex items-center gap-3 text-zinc-400 my-4">
            <div class="relative">
              <div class="w-5 h-5 border-2 border-zinc-600 border-t-purple-500 rounded-full animate-spin"></div>
            </div>
            <span class="text-sm">Searching for recently pushed branches...</span>
          </div>
          <div class="space-y-2">
            <DarkSkeleton class="h-4" variant="list" />
          </div>
        </div>
      </div>
    </template>
    <template #default>
      <RecentBranchSuggestions
        ref="recentBranchSuggestionsRef"
        v-if="!showPRForm"
        :recent-branches="recentlyMadeBranches"
        @select-branch="$emit('select-branch', $event)"
        @loading-changed="$emit('loading-changed', $event)"
        @refresh-branches="$emit('refresh-branches')"
      />
    </template>
  </Deferred>
</template>

<script setup lang="ts">
import { defineProps, defineEmits, ref, defineExpose } from 'vue';
import { Deferred } from '@inertiajs/vue3';
import RecentBranchSuggestions from '@/Components/PR/RecentBranchSuggestions.vue';
import { DarkSkeleton } from '@/Components/ui/skeleton';
// import type { RecentBranchData } from '@/types/dashboard';

const props = defineProps<{
  recentlyMadeBranches: App.Data.RecentBranchData[];
  showPRForm: boolean;
}>();

const recentBranchSuggestionsRef = ref(null);

defineEmits<{
  (e: 'select-branch', payload: any): void;
  (e: 'loading-changed', isLoading: boolean): void;
  (e: 'refresh-branches'): void;
}>();

// Expose method to clear loading state
const clearLoadingState = () => {
  if (recentBranchSuggestionsRef.value) {
    recentBranchSuggestionsRef.value.clearLoadingState();
  }
};

const clearRefreshState = () => {
  if (recentBranchSuggestionsRef.value) {
    recentBranchSuggestionsRef.value.clearRefreshState();
  }
};

defineExpose({
  clearLoadingState,
  clearRefreshState
});
</script> 