<template>
  <div class="flex-1">
    <template v-if="isLoading">
      <DarkSkeleton class="h-10 w-full mb-1" variant="list" />
    </template>
    <template v-else>
      <Select
        :options="branches"
        :model-value="baseBranch"
        @update:modelValue="onSelectBaseBranch"
        @loadMore="handleLoadMore"
        @search="handleSearch"
        label="Base Branch"
        placeholder="Search base branch..."
        :getOptionLabel="(branch: any) => branch.name"
        :getOptionKey="(branch: any) => branch.name"
        :disabled="!selectedRepository"
        :canLoadMore="canLoadMore"
        :isLoadingMore="isLoadingMore"
      />
    </template>
  </div>
</template>

<script setup lang="ts">
import { PropType } from 'vue';
import Select from '@/Components/ui/Select.vue';
import { DarkSkeleton } from '@/Components/ui/skeleton';

const props = defineProps({
  branches: {
    type: Array as PropType<App.Data.BranchData[]>,
    default: () => []
  },
  selectedRepository: [String, Number],
  baseBranch: String,
  baseBranchSha: String,
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

const emit = defineEmits(['update:baseBranch', 'update:baseBranchSha', 'loadMore', 'search']);

function onSelectBaseBranch(branchName: string) {
  const branch = props.branches.find((b: any) => b.name === branchName);
  emit('update:baseBranch', branchName);
  emit('update:baseBranchSha', branch ? branch.sha : '');
}

function handleLoadMore(searchTerm: string) {
  emit('loadMore', searchTerm);
}

function handleSearch(searchTerm: string) {
  emit('search', searchTerm);
}
</script> 