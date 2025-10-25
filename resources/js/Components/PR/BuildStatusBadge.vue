<template>
  <div class="mt-3 border-t border-zinc-700 pt-3">
    <details class="group">
      <summary class="cursor-pointer text-sm text-zinc-400 hover:text-zinc-300 transition-colors flex gap-2">
        <div>
          <span v-if="hasPendingChecks" class="inline-flex items-center gap-1 px-2 py-1 text-xs font-medium bg-yellow-600/20 text-yellow-400 rounded">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 animate-spin" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd"/>
            </svg>
            <span>Checks Pending</span>
            <span v-if="currentCheckSummary" class="ml-1 text-xs opacity-75">({{ currentCheckSummary.pending }}/{{ currentCheckSummary.total }})</span>
          </span>
          <span v-else-if="hasFailingChecks" class="inline-flex items-center gap-1 px-2 py-1 text-xs font-medium bg-red-600/20 text-red-400 rounded">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
            </svg>
            <span>Checks Failed</span>
            <span v-if="currentCheckSummary" class="ml-1 text-xs opacity-75">({{ currentCheckSummary.failure }}/{{ currentCheckSummary.total }})</span>
          </span>
          <span v-else-if="hasPassingChecks" class="inline-flex items-center gap-1 px-2 py-1 text-xs font-medium bg-green-600/20 text-green-400 rounded">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
            <span>All Checks Passed</span>
            <span v-if="currentCheckSummary" class="ml-1 text-xs opacity-75">({{ currentCheckSummary.total }})</span>
          </span>
          <span v-else-if="currentBuildStatus && currentCheckSummary?.total === 0" class="inline-flex items-center gap-1 px-2 py-1 text-xs font-medium bg-zinc-600/20 text-zinc-400 rounded">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
            </svg>
            <span>No Checks</span>
          </span>
        </div>
        <span class="select-none my-auto">
          Build Status Details ({{ currentCheckSummary?.total || 0 }} checks)
          <svg xmlns="http://www.w3.org/2000/svg" class="inline h-4 w-4 transition-transform group-open:rotate-90" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
          </svg>
        </span>
      </summary>
      <div class="mt-2 space-y-2">
        <div v-if="currentBuildStatus?.statuses?.length > 0">
          <h4 class="text-xs font-medium text-zinc-400 mb-1">Status Checks</h4>
          <div class="space-y-1">
            <div v-for="status in currentBuildStatus.statuses" :key="status.context" class="flex items-center gap-2 text-xs">
              <div class="flex-shrink-0">
                <div v-if="status.state && status.state.toLowerCase() === 'success'" class="w-2 h-2 bg-green-400 rounded-full"></div>
                <div v-else-if="status.state && (status.state.toLowerCase() === 'failure' || status.state.toLowerCase() === 'error')" class="w-2 h-2 bg-red-400 rounded-full"></div>
                <div v-else-if="status.state && status.state.toLowerCase() === 'pending'" class="w-2 h-2 bg-yellow-400 rounded-full animate-pulse"></div>
                <div v-else-if="status.state && (status.state.toLowerCase() === 'neutral' || status.state.toLowerCase() === 'skipped')" class="w-2 h-2 bg-zinc-400 rounded-full"></div>
                <div v-else class="w-2 h-2 bg-zinc-400 rounded-full"></div>
              </div>
              <span class="text-zinc-300">{{ status.context }}</span>
              <span class="text-zinc-500">{{ status.description }}</span>
              <a v-if="status.target_url" :href="status.target_url" target="_blank" rel="noopener noreferrer" class="text-blue-400 hover:text-blue-300">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M10.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L12.586 11H5a1 1 0 110-2h7.586l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd"/>
                </svg>
              </a>
            </div>
          </div>
        </div>
        <div v-if="currentBuildStatus?.check_runs?.length > 0">
          <h4 class="text-xs font-medium text-zinc-400 mb-1">Check Runs</h4>
          <div class="space-y-1">
            <div v-for="check in currentBuildStatus.check_runs" :key="check.name" class="flex items-center gap-2 text-xs">
              <div class="flex-shrink-0">
                <div v-if="check.conclusion === 'success'" class="w-2 h-2 bg-green-400 rounded-full"></div>
                <div v-else-if="check.conclusion === 'failure' || check.conclusion === 'cancelled'" class="w-2 h-2 bg-red-400 rounded-full"></div>
                <div v-else-if="check.status === 'in_progress' || check.status === 'queued'" class="w-2 h-2 bg-yellow-400 rounded-full animate-pulse"></div>
                <div v-else class="w-2 h-2 bg-zinc-400 rounded-full"></div>
              </div>
              <span class="text-zinc-300">{{ check.name }}</span>
              <span v-if="check.status === 'in_progress'" class="text-yellow-400">Running...</span>
              <span v-else-if="check.status === 'queued'" class="text-yellow-400">Queued</span>
              <span v-else-if="check.conclusion" class="text-zinc-500 capitalize">{{ check.conclusion }}</span>
              <a v-if="check.detailsUrl" :href="check.detailsUrl" target="_blank" rel="noopener noreferrer" class="text-blue-400 hover:text-blue-300">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M10.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L12.586 11H5a1 1 0 110-2h7.586l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd"/>
                </svg>
              </a>
            </div>
          </div>
        </div>
      </div>
    </details>
  </div>
</template>

<script setup lang="ts">
import { computed, ref, onMounted, onUnmounted, watch } from 'vue';
import axios from 'axios';

// Define props with PR information needed for polling
const props = defineProps<{
  buildStatus: any;
  checkSummary: { total: number; success: number; failure: number; pending: number };
  pullRequest: {
    number: number;
    repositoryOwner: string;
    repositoryName: string;
  };
}>();

// Define emits for updating parent component
const emit = defineEmits<{
  'build-status-updated': [buildStatus: any];
}>();

// Reactive state for current build status (starts with prop value)
const currentBuildStatus = ref(props.buildStatus);
const pollingInterval = ref<number | null>(null);
const isPolling = ref(false);

// Computed properties for the current state
const currentCheckSummary = computed(() => {
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

const hasFailingChecks = computed(() => currentBuildStatus.value?.overall_state === 'failure' || currentBuildStatus.value?.overall_state === 'error');
const hasPassingChecks = computed(() => currentBuildStatus.value?.overall_state === 'success');
const hasPendingChecks = computed(() => currentBuildStatus.value?.overall_state === 'pending');

// Polling function
const fetchBuildStatus = async () => {
  if (isPolling.value) return; // Prevent concurrent requests
  
  try {
    isPolling.value = true;
    const response = await axios.get(`/api/pull-requests/${props.pullRequest.repositoryOwner}/${props.pullRequest.repositoryName}/${props.pullRequest.number}/build-status`);
    
    const newBuildStatus = response.data.build_status;
    
    // Only update if there's a change
    if (JSON.stringify(newBuildStatus) !== JSON.stringify(currentBuildStatus.value)) {
      currentBuildStatus.value = newBuildStatus;
      emit('build-status-updated', newBuildStatus);
    }
    
    // Stop polling if no more pending checks
    if (!hasPendingChecks.value) {
      stopPolling();
    }
  } catch (error) {
    console.error('Failed to fetch build status:', error);
    // Stop polling on error to avoid spam
    stopPolling();
  } finally {
    isPolling.value = false;
  }
};

const startPolling = () => {
  if (pollingInterval.value) return; // Already polling
  
  pollingInterval.value = setInterval(fetchBuildStatus, 10000); // Poll every 10 seconds
};

const stopPolling = () => {
  if (pollingInterval.value) {
    clearInterval(pollingInterval.value);
    pollingInterval.value = null;
  }
};

// Watch for pending checks to start/stop polling
watch(hasPendingChecks, (newValue) => {
  if (newValue) {
    startPolling();
  } else {
    stopPolling();
  }
}, { immediate: true });

// Watch for prop changes to update current state
watch(() => props.buildStatus, (newValue) => {
  currentBuildStatus.value = newValue;
}, { deep: true });

// Lifecycle hooks
onMounted(() => {
  if (hasPendingChecks.value) {
    startPolling();
  }
});

onUnmounted(() => {
  stopPolling();
});
</script> 