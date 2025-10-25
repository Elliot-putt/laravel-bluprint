<template>
  <div class="mb-6">
    <template v-if="isLoading">
      <DarkSkeleton class="h-10 w-full mb-1" variant="list" />
    </template>
    <template v-else>
      <Select
        :options="repositories"
        :model-value="selectedRepository === null ? undefined : selectedRepository"
        @update:modelValue="onSelectRepository"
        @loadMore="handleLoadMore"
        @search="handleSearch"
        label="Repository"
        :placeholder="'Search repositories...'"
        :getOptionLabel="(repo: any) => repo && repo.name ? repo.name : ''"
        :getOptionKey="(repo: any) => (repo && repo.id !== null && repo.id !== undefined) ? repo.id : ''"
        :optionDescription="(repo: any) => (repo && repo.description && typeof repo.description === 'string' ? repo.description : undefined)"
        :canLoadMore="canLoadMore"
        :isLoadingMore="isLoadingMore"
      />
      <div v-if="selectedRepoDetails" class="mt-2 flex items-center">
        <a :href="selectedRepoDetails.link" target="_blank" rel="noopener noreferrer"
           class="inline-flex items-center gap-1.5 text-sm text-purple-400 hover:text-purple-300 transition-colors group">
          <span>View on GitHub</span>
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transform transition-transform group-hover:translate-x-0.5" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M10.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L12.586 11H5a1 1 0 110-2h7.586l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd" />
          </svg>
        </a>
      </div>
    </template>
  </div>
</template>

<script setup lang="ts">
import { computed, ref, PropType, watch } from 'vue';
import Select from '@/Components/ui/Select.vue';
import { DarkSkeleton } from '@/Components/ui/skeleton';

const props = defineProps({
  repositories: {
    type: Array as PropType<App.Data.RepositoryData[]>,
    required: true
  },
  selectedRepository: {
    type: [String, Number],
    default: undefined
  },
  isLoading: {
    type: Boolean,
    default: false
  },
  canLoadMore: {
    type: Boolean,
    default: false
  },
  isLoadingMore: {
    type: Boolean,
    default: false
  }
});

const emit = defineEmits(['update:selectedRepository', 'repository-change', 'loadMore', 'search']);

const selectedRepository = ref(props.selectedRepository ?? undefined);

// Add watcher to sync local ref with prop
watch(() => props.selectedRepository, (newVal) => {
  selectedRepository.value = newVal ?? undefined;
});

const selectedRepoDetails = computed(() => {
  if (selectedRepository.value === undefined || selectedRepository.value === null) return undefined;
  return props.repositories.find((r: any) => r.id === selectedRepository.value);
});

function onSelectRepository(repoId: string | number) {
  selectedRepository.value = repoId;
  emit('update:selectedRepository', repoId);
  emit('repository-change', repoId);
}

function handleLoadMore(searchTerm: string) {
  emit('loadMore', searchTerm);
}

function handleSearch(searchTerm: string) {
  emit('search', searchTerm);
}
</script>
