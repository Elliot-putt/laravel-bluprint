<template>
  <div class="flex-1">
    <template v-if="isLoading">
      <DarkSkeleton class="h-10 w-full mb-1" variant="list" />
    </template>
    <template v-else>
      <Select
        :options="filteredNewBranches"
        :model-value="newBranch"
        @update:modelValue="onSelectNewBranch"
        @loadMore="handleLoadMore"
        @search="handleSearch"
        label="New Branch"
        placeholder="Search or enter new branch name..."
        :getOptionLabel="(branch: any) => branch.name"
        :getOptionKey="(branch: any) => branch.name"
        :allowCustom="true"
        :disabled="!selectedRepository"
        :canLoadMore="canLoadMore"
        :isLoadingMore="isLoadingMore"
      />
    </template>
  </div>
</template>

<script setup lang="ts">
import { computed, PropType } from 'vue';
import Select from '@/Components/ui/Select.vue';
import { DarkSkeleton } from '@/Components/ui/skeleton';

const props = defineProps({
  branches: {
    type: Array as PropType<App.Data.BranchData[]>,
    default: () => []
  },
  baseBranch: String,
  selectedRepository: [String, Number],
  newBranch: String,
  newBranchSha: String,
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

const emit = defineEmits(['update:newBranch', 'update:newBranchSha', 'loadMore', 'search']);

const filteredNewBranches = computed(() => {
  if (!props.baseBranch) return props.branches;
  return props.branches.filter((branch: any) => branch.name !== props.baseBranch);
});

function onSelectNewBranch(branchName: string) {
  const branch = props.branches.find((b: any) => b.name === branchName);
  emit('update:newBranch', branchName);
  emit('update:newBranchSha', branch ? branch.sha : '');
}

function handleLoadMore(searchTerm: string) {
  emit('loadMore', searchTerm);
}

function handleSearch(searchTerm: string) {
  emit('search', searchTerm);
}
</script> 