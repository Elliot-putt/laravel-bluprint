<template>

  <div>
    <RepositorySelect
      :repositories="repositories"
      :selected-repository="selectedRepository"
      :is-loading="isLoadingRepoData"
      :can-load-more="repositoriesPagination.hasMore"
      :is-loading-more="repositoriesPagination.isLoading"
      @update:selected-repository="$emit('update:selectedRepository', $event)"
      @repository-change="onRepositoryChange"
      @load-more="$emit('load-more-repositories', $event)"
      @search="$emit('search-repositories', $event)"
    />
  </div>

  <div>
    <div class="flex flex-col md:flex-row gap-4 mb-6">
      <BaseBranchSelect
        :branches="branchList"
        :selected-repository="selectedRepository"
        :base-branch="branches && branches.base"
        :base-branch-sha="branches && branches.baseBranchSha"
        :is-loading="isLoadingBranchData"
        :can-load-more="branchesPagination.hasMore"
        :is-loading-more="branchesPagination.isLoading"
        @update:baseBranch="$emit('update:baseBranch', $event)"
        @update:baseBranchSha="$emit('update:baseBranchSha', $event)"
        @load-more="$emit('load-more-branches', $event)"
        @search="$emit('search-branches', $event)"
      />
      <NewBranchSelect
        :branches="branchList"
        :selected-repository="selectedRepository"
        :new-branch="branches && branches.new"
        :new-branch-sha="branches && branches.newBranchSha"
        :is-loading="isLoadingBranchData"
        :can-load-more="branchesPagination.hasMore"
        :is-loading-more="branchesPagination.isLoading"
        @update:newBranch="$emit('update:newBranch', $event)"
        @update:newBranchSha="$emit('update:newBranchSha', $event)"
        @load-more="$emit('load-more-branches', $event)"
        @search="$emit('search-branches', $event)"
      />
    </div>
  </div>

  <div class="flex flex-col lg:flex-row gap-3 mb-6 items-start">
    <div v-if="hasConfiguredJira" class="flex-1 min-w-0">
      <JiraTicketSelect
        :jira-tickets="jiraTickets"
        :ticket-key="ticketKey"
        @update:ticket-key="$emit('update:ticketKey', $event)"
      />
    </div>
    <div class="flex-1 min-w-0">
      <TemplateSelect
        :templates="templates"
        :disabled="isGenerating"
        :selected-template-id="selectedTemplateId"
        @update:selected-template-id="$emit('update:selectedTemplateId', $event)"
      />
    </div>
    <div class="flex-shrink-0 pt-6">
      <button
        @click="$emit('generate-p-r')"
        :disabled="isGenerating || !canGenerate"
        class="px-8 py-2 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 text-white shadow-sm flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed transition-all h-10"
      >
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
          <path fill-rule="evenodd" d="M5 2a1 1 0 011 1v1h1a1 1 0 010 2H6v1a1 1 0 01-2 0V6H3a1 1 0 010-2h1V3a1 1 0 011-1zm0 10a1 1 0 011 1v1h1a1 1 0 110 2H6v1a1 1 0 11-2 0v-1H3a1 1 0 110-2h1v-1a1 1 0 011-1zm7-10a1 1 0 01.707.293l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L14.586 9H7a1 1 0 110-2h7.586l-3.293-3.293A1 1 0 0112 2z" clip-rule="evenodd" />
        </svg>
        {{ isGenerating ? 'Generating...' : 'Generate PR' }}
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, PropType } from 'vue';
import { Deferred, usePage } from '@inertiajs/vue3';
import { DarkSkeleton } from '@/Components/ui/skeleton';
import RepositorySelect from './RepositorySelect.vue';
import BaseBranchSelect from './BaseBranchSelect.vue';
import NewBranchSelect from './NewBranchSelect.vue';
import JiraTicketSelect from './JiraTicketSelect.vue';
import TemplateSelect from './TemplateSelect.vue';

const page = usePage();
const hasConfiguredJira = computed(() => (page.props.auth as any)?.hasConfiguredJira);

const props = defineProps({
  repositories: {
    type: Array as PropType<App.Data.RepositoryData[]>,
    default: () => []
  },
  branchList: {
    type: Array as PropType<App.Data.BranchData[]>,
    default: () => []
  },
  selectedRepository: [String, Number],
  branches: {
    type: Object as PropType<{
      base: string;
      baseBranchSha: string;
      new: string;
      newBranchSha: string;
    }>,
    default: () => ({ base: '', baseBranchSha: '', new: '', newBranchSha: '' })
  },
  isLoadingBranchData: Boolean,
  templates: {
    type: Array as PropType<App.Data.TemplateData[]>,
    default: () => []
  },
  jiraTickets: {
    type: Array as PropType<App.Data.JiraTicketData[]>,
    default: () => []
  },
  defaultTemplateId: Number,
  ticketKey: String,
  selectedTemplateId: Number,
  isGenerating: Boolean,
  canGenerate: Boolean,
  recentlyMadeBranches: {
    type: Array as PropType<App.Data.RecentBranchData[]>,
    default: () => []
  },
  showPRForm: Boolean,
  isLoadingRepoData: Boolean,
  repositoriesPagination: {
    type: Object as PropType<{
      currentPage: number;
      hasMore: boolean;
      isLoading: boolean;
    }>,
    default: () => ({ currentPage: 1, hasMore: true, isLoading: false })
  },
  branchesPagination: {
    type: Object as PropType<{
      currentPage: number;
      hasMore: boolean;
      isLoading: boolean;
    }>,
    default: () => ({ currentPage: 1, hasMore: true, isLoading: false })
  }
});

const emit = defineEmits([
  'repository-change',
  'generate-p-r',
  'select-branch',
  'update:selectedRepository',
  'update:baseBranch',
  'update:baseBranchSha',
  'update:newBranch',
  'update:newBranchSha',
  'update:ticketKey',
  'update:selectedTemplateId',
  'clear-recent-branch',
  'load-more-repositories',
  'search-repositories',
  'load-more-branches',
  'search-branches',
]);

function onRepositoryChange(repoId: string | number) {
  emit('update:selectedRepository', repoId);
  emit('repository-change', repoId);
}
</script>
