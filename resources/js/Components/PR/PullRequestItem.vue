<template>
  <div class="p-6 hover:bg-zinc-700/30 transition-colors">
    <div class="flex items-start justify-between gap-4">
      <div class="flex-1 min-w-0">
        <div class="flex items-center gap-3 mb-2">
          <StatusIcon :pull-request="pullRequest" />
          <div class="flex-1 min-w-0">
            <a :href="pullRequest.url" target="_blank" rel="noopener noreferrer"
              class="text-white hover:text-purple-300 transition-colors group">
              <h3 class="text-lg font-medium truncate group-hover:underline">
                {{ pullRequest.title }}
              </h3>
            </a>
            <div class="flex items-center gap-2 text-sm text-zinc-400 mt-1">
              <span>#{{ pullRequest.number }}</span>
              <span>•</span>
              <span>{{ repositoryDisplay }}</span>
              <span>•</span>
              <span>{{ timeAgo }}</span>
              <span v-if="pullRequest.user">by {{ pullRequest.user.login }}</span>
            </div>
          </div>
        </div>
        <BranchInfo :pull-request="pullRequest" />
        <BuildStatusBadge 
          v-if="currentBuildStatus && currentCheckSummary?.total > 0" 
          :build-status="currentBuildStatus" 
          :check-summary="currentCheckSummary" 
          :pull-request="pullRequest"
          @build-status-updated="handleBuildStatusUpdate"
        />
      </div>
      <div class="flex-shrink-0 text-right">
        <PRStats :pull-request="pullRequest" />
        <ReviewStatusBadge v-if="reviewData?.review_summary" :review-summary="reviewData.review_summary" />
      </div>
    </div>
  </div>
</template>
<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import StatusIcon from './StatusIcon.vue';
import BranchInfo from './BranchInfo.vue';
import BuildStatusBadge from './BuildStatusBadge.vue';
import PRStats from './PRStats.vue';
import ReviewStatusBadge from './ReviewStatusBadge.vue';
import type { App } from '@/types/generated';

const props = defineProps<{ 
  pullRequest: App.Data.PullRequest; 
  buildStatus?: App.Data.BuildStatus; 
  reviewData?: App.Data.ReviewData; 
}>();

// Reactive state for current build status (starts with prop value)
const currentBuildStatus = ref(props.buildStatus);

// Handle build status updates from polling
const handleBuildStatusUpdate = (newBuildStatus: any) => {
  currentBuildStatus.value = newBuildStatus;
};

// Watch for prop changes to update current state
watch(() => props.buildStatus, (newValue) => {
  currentBuildStatus.value = newValue;
}, { deep: true });

const repositoryDisplay = computed(() => {
  const { repositoryFullName, repositoryName, repositoryOwner } = props.pullRequest;
  if (repositoryFullName) return repositoryFullName;
  if (repositoryName && repositoryOwner) return `${repositoryOwner}/${repositoryName}`;
  return 'Unknown Repository';
});

const timeAgo = computed(() => {
  const createdAt = props.pullRequest.createdAt || props.pullRequest.created_at;
  if (!createdAt) return 'Invalid Date';
  const createdDate = new Date(createdAt);
  if (isNaN(createdDate.getTime())) return 'Invalid Date';
  const now = new Date();
  const diffInMs = now.getTime() - createdDate.getTime();
  const diffInMinutes = Math.floor(diffInMs / (1000 * 60));
  const diffInHours = Math.floor(diffInMinutes / 60);
  const diffInDays = Math.floor(diffInHours / 24);
  if (diffInMinutes < 60) return `${diffInMinutes}m ago`;
  if (diffInHours < 24) return `${diffInHours}h ago`;
  if (diffInDays < 30) return `${diffInDays}d ago`;
  return createdDate.toLocaleDateString();
});

const currentCheckSummary = computed<{
  total: number;
  success: number;
  failure: number;
  pending: number;
} | null>(() => {
  if (!currentBuildStatus.value) return null;
  const statuses = currentBuildStatus.value.statuses || [];
  const checkRuns = currentBuildStatus.value.check_runs || [];
  const totalChecks = statuses.length + checkRuns.length;
  if (totalChecks === 0) return null;
  const successCount = [
    ...statuses.filter((s: any) => s.state && s.state.toLowerCase() === 'success'),
    ...checkRuns.filter((c: any) => c.conclusion && c.conclusion.toLowerCase() === 'success')
  ].length;
  const failureCount = [
    ...statuses.filter((s: any) => s.state && (s.state.toLowerCase() === 'failure' || s.state.toLowerCase() === 'error')),
    ...checkRuns.filter((c: any) => c.conclusion && (c.conclusion.toLowerCase() === 'failure' || c.conclusion.toLowerCase() === 'cancelled'))
  ].length;
  const pendingCount = [
    ...statuses.filter((s: any) => s.state && s.state.toLowerCase() === 'pending'),
    ...checkRuns.filter((c: any) => c.status && (c.status.toLowerCase() === 'in_progress' || c.status.toLowerCase() === 'queued'))
  ].length;
  return {
    total: totalChecks,
    success: successCount,
    failure: failureCount,
    pending: pendingCount
  };
});
</script> 