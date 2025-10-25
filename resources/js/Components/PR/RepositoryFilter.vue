<template>
  <div class="bg-zinc-800/50 border border-zinc-700 rounded-lg shadow-lg mb-6">
    <div class="p-6">
      <div class="flex items-center gap-3 mb-4">
        <div class="flex items-center justify-center w-10 h-10 bg-gradient-to-br from-blue-600 to-cyan-600 rounded-lg shadow-lg">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd" />
          </svg>
        </div>
        <div>
          <h3 class="text-lg font-semibold text-white">Filter Pull Requests</h3>
          <p class="text-sm text-zinc-400">Select a repository or view all recent PRs</p>
        </div>
      </div>
      <div class="flex flex-col md:flex-row gap-4">
        <div class="flex-1">
          <Deferred data="repositories">
            <template #fallback>
              <DarkSkeleton class="h-12" variant="list" />
            </template>
            <template #default>
              <Select
                :options="repositories"
                v-model="selectedRepositoryProxy"
                :get-option-label="repoLabel"
                :get-option-key="repoKey"
                :placeholder="'Select repository...'"
                :disabled="isLoading"
                label="Repository"
              />
              <button v-if="selectedRepositoryProxy" @click="$emit('clear')" class="ml-2 text-xs text-purple-400 hover:underline">Clear</button>
            </template>
          </Deferred>
        </div>
        <div class="flex-1">
          <slot name="state-filter" />
        </div>
      </div>
    </div>
  </div>
</template>
<script setup lang="ts">
import { computed, PropType } from 'vue';
import Select from '@/Components/ui/Select.vue';
import { DarkSkeleton } from '@/Components/ui/skeleton';
import type { App } from '@/types/generated';
import {Deferred} from "@inertiajs/vue3";
const props = defineProps({
  repositories: {
    type: Array as PropType<App.Data.RepositoryData[]>,
    default: () => []
  },
  selectedRepository: {
    type: [String, Number],
    default: ''
  },
  isLoading: Boolean
});
const emit = defineEmits(['update:selected-repository', 'clear']);
const selectedRepositoryProxy = computed({
  get: () => props.selectedRepository,
  set: (val: string | number) => emit('update:selected-repository', val)
});
const repoLabel = (repo: App.Data.RepositoryData) => repo.name || repo.fullName || repo.id;
const repoKey = (repo: App.Data.RepositoryData) => repo.id;
</script>
