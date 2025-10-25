<template>
    <!-- Recent Branches Card Container -->
    <div v-if="recentBranches?.length && !isRefreshing"  class="bg-zinc-800/50 border border-zinc-700 rounded-lg shadow-lg backdrop-blur-sm mb-6">
        <!-- Header -->
        <div class="p-6 border-b border-zinc-700/50">
            <div class="flex items-center justify-between">
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
                
                <!-- Refresh Button -->
                <button
                    @click="refreshBranches"
                    class="group relative p-3 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 rounded-lg shadow-lg transition-all duration-300 hover:shadow-xl hover:scale-105"
                >
                    <!-- Animated background ring -->
                    <div class="absolute inset-0 rounded-lg bg-gradient-to-r from-purple-400 to-pink-500 opacity-0 group-hover:opacity-20 transition-opacity duration-300"></div>
                    
                    <!-- Icon container with rotation animation -->
                    <div class="relative z-10">
                        <i class="fas fa-sync-alt h-5 w-5 text-white transition-all duration-300 group-hover:scale-110"></i>
                    </div>
                </button>
            </div>
        </div>

        <!-- Content Area -->
        <div class="p-6">
            <!-- Branch Cards -->
            <div class="space-y-3">
                <div
                    v-for="branch in displayedBranches"
                    :key="`${branch.repositoryOwner}/${branch.repository}/${branch.name}`"
                    class="group bg-gradient-to-r from-zinc-800/80 to-zinc-700/60 border border-zinc-600/50 rounded-lg p-4 hover:from-zinc-700/80 hover:to-zinc-600/60 hover:border-zinc-500/70 transition-all duration-200 hover:shadow-lg hover:shadow-purple-500/10"
                >
                    <div class="flex items-center justify-between">
                        <!-- Branch Info -->
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-3 mb-2">
                                <!-- Repository -->
                                <div class="flex items-center gap-2">
                                    <div class="w-2 h-2 bg-gradient-to-r from-purple-500 to-pink-500 rounded-full"></div>
                                    <span class="text-sm font-medium text-zinc-300">
                    {{ branch.repositoryOwner }}/<span class="text-white">{{ branch.repository }}</span>
                  </span>
                                </div>

                                <!-- Time indicator -->
                                <div class="flex items-center gap-1 text-xs text-zinc-400">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
                                    </svg>
                                    <span>{{ formatTimeAgo(branch.lastCommitDate) }}</span>
                                </div>
                            </div>

                            <!-- Branch Name -->
                            <div class="flex items-center gap-2 mb-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                                </svg>
                                <span class="font-mono text-sm text-green-400 font-medium">{{ branch.name }}</span>

                                <!-- New branch indicator -->
                                <span
                                    v-if="branch.isRecentlyCreated"
                                    class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gradient-to-r from-blue-500/20 to-cyan-500/20 text-blue-300 border border-blue-500/30"
                                >
                  New
                </span>
                            </div>

                            <!-- Commit Message -->
                            <div class="flex items-start gap-2 mb-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-zinc-400 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-3.582 8-8 8a8.001 8.001 0 01-7.227-4.612 11.95 11.95 0 010-6.776A8.001 8.001 0 0113 4c4.418 0 8 3.582 8 8z" />
                                </svg>
                                <p class="text-sm text-zinc-300 truncate flex-1" :title="branch.lastCommitMessage">
                                    {{ branch.lastCommitMessage }}
                                </p>
                            </div>

                            <!-- Metadata -->
                            <div class="flex items-center gap-4 text-xs text-zinc-400">
                                <div class="flex items-center gap-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                                    </svg>
                                    <span>{{ branch.lastCommitAuthor }}</span>
                                </div>

                                <div v-if="branch.hasOpenPr" class="flex items-center gap-1 text-green-400">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                    </svg>
                                    <span>PR Exists</span>
                                </div>
                            </div>
                        </div>

                        <!-- Action Button -->
                        <div class="ml-4 flex-shrink-0">
                            <button
                                v-if="(!branch.hasOpenPr && branch.suggestedForPr && !isBranchSelected(branch)) || (branch.hasOpenPr && !isBranchSelected(branch))"
                                @click="selectBranch(branch)"
                                :disabled="isBranchLoading(branch)"
                                class="px-4 py-2 text-white shadow-sm flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed transition-all"
                                :class="[
                                    isBranchLoading(branch) 
                                    ? 'bg-gradient-to-r from-blue-600 to-purple-600' 
                                    : branch.hasOpenPr
                                        ? 'bg-gradient-to-r from-orange-600 to-red-600 hover:from-orange-700 hover:to-red-700'
                                        : 'bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700'
                                ]"
                            >
                                <div v-if="isBranchLoading(branch)" class="w-5 h-5 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
                                <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path v-if="!branch.hasOpenPr" fill-rule="evenodd" d="M5 2a1 1 0 011 1v1h1a1 1 0 010 2H6v1a1 1 0 01-2 0V6H3a1 1 0 010-2h1V3a1 1 0 011-1zm0 10a1 1 0 011 1v1h1a1 1 0 110 2H6v1a1 1 0 11-2 0v-1H3a1 1 0 110-2h1v-1a1 1 0 011-1zm7-10a1 1 0 01.707.293l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L14.586 9H7a1 1 0 110-2h7.586l-3.293-3.293A1 1 0 0112 2z" clip-rule="evenodd" />
                                    <path v-else fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd" />
                                </svg>
                                {{ isBranchLoading(branch) ? 'Loading...' : branch.hasOpenPr ? 'Overwrite PR' : 'Create PR' }}
                            </button>

                            <div
                                v-else-if="isBranchSelected(branch)"
                                class="px-4 py-2 bg-blue-600/20 border border-blue-500/30 text-blue-300 text-sm font-medium rounded-lg flex items-center gap-2"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                                Selected
                            </div>

                            <div
                                v-else
                                class="px-4 py-2 bg-zinc-600/50 border border-zinc-500/30 text-zinc-400 text-sm font-medium rounded-lg flex items-center gap-2"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M13.477 14.89A6 6 0 015.11 6.524l8.367 8.368zm1.414-1.414L6.524 5.11a6 6 0 008.367 8.367zM18 10a8 8 0 11-16 0 8 8 0 0116 0z" clip-rule="evenodd" />
                                </svg>
                                Skip
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Expand/Collapse for many branches -->
                <div v-if="recentBranches.length > 3" class="pt-3 text-center border-t border-zinc-700/50">
                    <button
                        @click="showAll = !showAll"
                        class="text-sm text-zinc-400 hover:text-white transition-colors flex items-center gap-1 mx-auto group"
                    >
                        <span>{{ showAll ? 'Show Less' : `Show All (${recentBranches.length})` }}</span>
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="h-4 w-4 transition-transform group-hover:scale-110"
                            :class="{ 'rotate-180': showAll }"
                            viewBox="0 0 20 20"
                            fill="currentColor"
                        >
                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Skeleton Loader when refreshing -->
    <div v-if="isRefreshing" class="bg-zinc-800/50 border border-zinc-700 rounded-lg shadow-lg backdrop-blur-sm mb-6">
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
                <span class="text-sm">Refreshing recently pushed branches...</span>
            </div>
            <div class="space-y-2">
                <DarkSkeleton class="h-4" variant="list" />
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import {ref, computed, PropType} from 'vue';
import { DarkSkeleton } from "@/Components/ui/skeleton";

const props = defineProps({
    recentBranches: {
        type: Array as PropType<App.Data.RecentBranchData[]>,
        default: () => []
    },
});

const emit = defineEmits(['select-branch', 'loading-changed', 'refresh-branches']);

const showAll = ref(false);
const isRefreshing = ref(false);

const selectedBranchKey = ref<string | null>(null);
const loadingBranchKey = ref<string | null>(null);

const displayedBranches = computed(() => {
    if (!props.recentBranches?.length) return [];
    return showAll.value ? props.recentBranches : props.recentBranches.slice(0, 3);
});

const isBranchSelected = (branch: any) => {
    return selectedBranchKey.value === `${branch.repositoryOwner}/${branch.repository}/${branch.name}`;
};

const isBranchLoading = (branch: any) => {
    return loadingBranchKey.value === `${branch.repositoryOwner}/${branch.repository}/${branch.name}`;
};

const selectBranch = (branch: any) => {
    const branchKey = `${branch.repositoryOwner}/${branch.repository}/${branch.name}`;
    selectedBranchKey.value = branchKey;
    loadingBranchKey.value = branchKey;
    
    // Emit loading started
    emit('loading-changed', true);
    
    // Create repository object directly from branch data since it might not be in current paginated results
    emit('select-branch', {
        repository: {
            id: null, // Will be handled by the parent component
            name: branch.repository,
            owner: branch.repositoryOwner,
            fullName: branch.repositoryFullName,
            description: null,
            link: null,
            defaultBranch: null
        },
        branch: {
            name: branch.name,
            sha: branch.sha
        }
    });
};

// Add method to clear loading state (will be called by parent)
const clearLoadingState = () => {
    loadingBranchKey.value = null;
    emit('loading-changed', false);
};

const clearRefreshState = () => {
    isRefreshing.value = false;
};

// Expose the method so parent can call it
defineExpose({
    clearLoadingState,
    clearRefreshState
});

const refreshBranches = async () => {
    if (isRefreshing.value) return;
    
    isRefreshing.value = true;
    emit('refresh-branches');
    
    // Clear the loading state after a delay to allow data to refresh
    setTimeout(() => {
        isRefreshing.value = false;
    }, 3000);
};

const formatTimeAgo = (dateString: string) => {
    const date = new Date(dateString);
    const now = new Date();
    const diffInSeconds = Math.floor((now.getTime() - date.getTime()) / 1000);

    if (diffInSeconds < 60) return 'just now';
    if (diffInSeconds < 3600) return `${Math.floor(diffInSeconds / 60)}m ago`;
    if (diffInSeconds < 86400) return `${Math.floor(diffInSeconds / 3600)}h ago`;
    if (diffInSeconds < 604800) return `${Math.floor(diffInSeconds / 86400)}d ago`;
    return date.toLocaleDateString();
};
</script>
