<template>
    <div class="bg-zinc-800/50 border border-zinc-700 rounded-lg shadow-lg">
        <div class="p-6 border-b border-zinc-700/50">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="flex items-center justify-center w-10 h-10 bg-gradient-to-br from-purple-600 to-pink-600 rounded-lg shadow-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M7.707 3.293a1 1 0 010 1.414L5.414 7H11a7 7 0 017 7v2a1 1 0 11-2 0v-2a5 5 0 00-5-5H5.414l2.293 2.293a1 1 0 11-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-white">Pull Requests</h3>
                        <p class="text-sm text-zinc-400">
                            <span v-if="selectedRepository">Selected repository</span>
                            <span v-else>Recent activity across all repositories</span>
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <span class="text-sm text-zinc-400">{{ selectedStateLabel }}</span>
                    <span v-if="localShowOnlyMyPRs" class="text-purple-400">• Only mine</span>
                    <div class="flex items-center gap-2 ml-auto">
                        <label class="text-zinc-300 font-medium text-sm">Only my pull requests</label>
                        <button
                            @click="toggleOnlyMyPRs"
                            class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 focus:ring-offset-zinc-800"
                            :class="localShowOnlyMyPRs ? 'bg-purple-600' : 'bg-zinc-600'"
                        >
                            <span class="sr-only">Toggle only my pull requests</span>
                            <span
                                class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform"
                                :class="localShowOnlyMyPRs ? 'translate-x-6' : 'translate-x-1'"
                            ></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div v-if="isLoading" class="p-6">
            <div class="flex items-center gap-3 text-zinc-400 mb-4">
                <div class="relative">
                    <div class="w-5 h-5 border-2 border-zinc-600 border-t-purple-500 rounded-full animate-spin"></div>
                </div>
                <span class="text-sm">Loading pull requests...</span>
            </div>
            <div class="space-y-4">
                <DarkSkeleton class="h-20" variant="list" />
                <DarkSkeleton class="h-20" variant="list" />
                <DarkSkeleton class="h-20" variant="list" />
            </div>
        </div>
        <div v-else-if="pullRequestItems && pullRequestItems.length > 0">
            <div class="divide-y divide-zinc-700/50">
                <PullRequestItem
                    v-for="item in pullRequestItems"
                    :key="item.pull_request.number"
                    :pull-request="item.pull_request"
                    :build-status="item.build_status"
                    :review-data="item.review_data"
                />
            </div>
            <DynamicPagination
                :current-page="requests.current_page"
                :total-pages="requests.last_page"
                :total="requests.total"
                :from="requests.from"
                :to="requests.to"
                :per-page="currentPerPage"
                :loading="isLoading"
                @page-changed="$emit('page-changed', $event)"
                @per-page-changed="$emit('per-page-changed', $event)"
            />
        </div>
        <div v-else class="p-8 text-center">
            <div class="text-zinc-500 mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
            </div>
            <h3 class="text-lg font-medium text-zinc-300 mb-2">No Pull Requests Found</h3>
            <p class="text-zinc-400 mb-4">
        <span v-if="selectedRepository">
                                      No pull requests found for this repository{{ localShowOnlyMyPRs ? ' authored by you' : '' }}.
        </span>
                <span v-else>
                                      No pull requests found in your recent activity{{ localShowOnlyMyPRs ? ' authored by you' : '' }}.
        </span>
            </p>
            <a :href="route('dashboard')" class="inline-flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 text-white shadow-md font-medium transition-all rounded-sm">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v2H7a1 1 0 100 2h2v2a1 1 0 102 0v-2h2a1 1 0 100-2h-2V7z" clip-rule="evenodd" />
                </svg>
                Create New PR
            </a>
        </div>
    </div>
</template>
<script setup lang="ts">
import { computed, PropType, ref, watch } from 'vue';
import PullRequestItem from './PullRequestItem.vue';
import DynamicPagination from '@/Components/DynamicPagination.vue';
import { DarkSkeleton } from '@/Components/ui/skeleton';
import '@/types/generated';

const props = defineProps({
    requests: {
        type: Object as PropType<{
            data: App.Data.PullRequestData[];
            current_page: number;
            last_page: number;
            total: number;
            from: number;
            to: number;
            per_page: number;
        }>,
        required: true
    },
    isLoading: Boolean,
    selectedRepository: {
        type: [String, Number],
        default: ''
    },
    selectedState: {
        type: String,
        default: 'open'
    },
    currentPerPage: Number,
    showOnlyMyPRs: {
        type: Boolean,
        default: true
    }
});

const emit = defineEmits(['only-my-prs-changed', 'page-changed', 'per-page-changed']);

const localShowOnlyMyPRs = ref(props.showOnlyMyPRs);

watch(() => props.showOnlyMyPRs, (newVal) => {
    localShowOnlyMyPRs.value = newVal;
}, { immediate: true });

const pullRequestItems = computed(() => props.requests?.data || []);

const selectedStateLabel = computed(() => {
    if (props.selectedState === 'open') return 'Open';
    if (props.selectedState === 'closed') return 'Closed';
    if (props.selectedState === 'all') return 'All';
    return props.selectedState;
});

function toggleOnlyMyPRs() {
    localShowOnlyMyPRs.value = !localShowOnlyMyPRs.value;
    emit('only-my-prs-changed', localShowOnlyMyPRs.value);
}
</script>
