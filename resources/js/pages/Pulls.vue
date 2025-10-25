<template>
    <Head title="Pull Requests" />

    <AuthenticatedLayout>
        <template #header>
            <PullsHeader title="Pull Requests" @create-pr="() => router.visit(route('dashboard'))" />
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <RepositoryFilter
                    :repositories="repositories"
                    v-model:selected-repository="selectedRepository"
                    :is-loading="isLoadingRepoData"
                    @update:selected-repository="handleRepositoryChange"
                    @clear="clearRepositoryFilter"
                >
                    <template #state-filter>
                        <StateFilter
                            v-model:selected-state="selectedState"
                            :options="stateOptions"
                        />
                    </template>
                </RepositoryFilter>
                <Deferred data="requests">
                    <template #default>
                        <PullRequestList
                            :requests="requests"
                            :selected-repository="selectedRepository"
                            :selected-state="selectedState"
                            :show-only-my-prs="showOnlyMyPRs"
                            :current-per-page="currentPerPage"
                            @only-my-prs-changed="handleOnlyMyPRsChanged"
                            @page-changed="handlePageChange"
                            @per-page-changed="handlePerPageChange"
                        />
                    </template>
                    <template #fallback>
                        <div class="bg-zinc-800/50 border border-zinc-700 rounded-lg shadow-lg p-6 mt-4">
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
                    </template>
                </Deferred>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Deferred, Head } from '@inertiajs/vue3';
import { router } from '@inertiajs/vue3';
import { ref, computed, watch, PropType } from 'vue';
import { DarkSkeleton } from "@/Components/ui/skeleton";
import DynamicPagination from "@/Components/DynamicPagination.vue";
import RepositoryFilter from '@/Components/PR/RepositoryFilter.vue';
import StateFilter from '@/Components/PR/StateFilter.vue';
import PullRequestList from '@/Components/PR/PullRequestList.vue';
import PullsHeader from '@/Components/PR/PullsHeader.vue';
import '@/types/generated';
import debounce from 'lodash/debounce';

type PullRequestItem = App.Data.PullRequestData;
type RepositoryData = App.Data.RepositoryData;

const props = defineProps({
    repositories: {
        type: Array as PropType<RepositoryData[]>,
        default: () => []
    },
    requests: {
        type: Object as PropType<{
            data: PullRequestItem[];
            current_page: number;
            last_page: number;
            total: number;
            from: number;
            to: number;
            per_page: number;
        }>,
        required: true
    },
    repositoryId: Number,
    filters: {
        type: Object as PropType<{
            repository_owner: string | null;
            repository: string | null;
            state: string;
            show_all: boolean;
            page: number;
            per_page: number;
        }>,
        default: () => ({
            repository_owner: null,
            repository: null,
            state: '',
            show_all: false,
            page: 1,
            per_page: 25,
        })
    }
});

const selectedRepository = ref<string | number | undefined>(props.repositoryId);
const selectedState = ref<string>(props.filters.state || 'open');
const showOnlyMyPRs = ref(!props.filters.show_all);
const currentPerPage = ref<number>(props.filters.per_page || 25);
const isLoadingRepoData = ref(false);

watch(() => props.repositoryId, (newVal) => {
    selectedRepository.value = newVal ?? (props.filters.repository ?? undefined);
});

watch(() => props.filters.repository, (newVal) => {
    if (!props.repositoryId) selectedRepository.value = newVal ?? undefined;
});

watch(() => props.filters.per_page, (newVal) => {
    currentPerPage.value = newVal || 25;
});

watch(() => props.filters.show_all, (newVal) => {
    showOnlyMyPRs.value = !newVal;
});

// Watch for changes in filters.state to update selectedState
watch(() => props.filters.state, (newState) => {
    if (newState && newState !== selectedState.value) {
        selectedState.value = newState;
    }
}, { immediate: true });

// Watch for selectedState changes and trigger filter update
watch(selectedState, (newState, oldState) => {
    if (newState !== oldState && oldState !== undefined) {
        handleFiltersChange();
    }
});

const stateOptions = [
    { value: 'open', label: 'Open', colorClass: 'bg-green-500' },
    { value: 'closed', label: 'Closed', colorClass: 'bg-red-500' },
    { value: 'all', label: 'All', colorClass: 'bg-gray-500' }
];

const repositories = computed<RepositoryData[]>(() => {
    // Handle deferred data that might be null/undefined initially
    return Array.isArray(props.repositories) ? props.repositories : [];
});

const selectedRepositoryName = computed(() => {
    if (!selectedRepository.value) return '';
    const repo = repositories.value.find(r => r.id === selectedRepository.value);
    return repo ? repo.name : '';
});

const currentStateOption = computed(() => {
    return stateOptions.find(s => s.value === selectedState.value) || stateOptions[0];
});

const pullRequestItems = computed<PullRequestItem[]>(() => {
    return props.requests?.data || [];
});

// Watch for repositories being loaded and update selectedRepository if needed
watch(repositories, (newRepositories) => {
    if (newRepositories && newRepositories.length > 0) {
        // If we don't have a repositoryId but have filter values, find the repository
        if (!props.repositoryId && props.filters.repository && props.filters.repository_owner) {
            const repo = newRepositories.find(r => 
                r.name === props.filters.repository && r.owner === props.filters.repository_owner
            );
            if (repo) {
                selectedRepository.value = repo.id;
            }
        }
        // If we have repositoryId but selectedRepository is not set, use repositoryId
        else if (props.repositoryId && !selectedRepository.value) {
            selectedRepository.value = props.repositoryId;
        }
    }
}, { immediate: true });

const handleOnlyMyPRsChanged = (value: boolean) => {
    showOnlyMyPRs.value = value;
    handleFiltersChange();
};



const handleRepositoryChange = (repoId: string | number) => {
    handleFiltersChange();
};

const clearRepositoryFilter = () => {
    selectedRepository.value = '';
    handleFiltersChange();
};

const handlePageChange = (page: number) => {
    const params = buildFilterParams();
    params.page = page;
    router.get(route('pull-requests.index'), params, {
        preserveState: true,
        preserveScroll: false,
        only: ['requests'],
    });
};

const handlePerPageChange = (perPage: number) => {
    currentPerPage.value = perPage;
    const params = buildFilterParams();
    params.per_page = perPage;
    params.page = 1;
    router.get(route('pull-requests.index'), params, {
        preserveState: true,
        preserveScroll: true,
        only: ['requests'],
    });
};

const buildFilterParams = () => {
    const params: any = {
        state: selectedState.value,
        show_all: !showOnlyMyPRs.value,
        per_page: currentPerPage.value,
    };
    if (selectedRepository.value) {
        const repo = repositories.value.find(r => r.id === selectedRepository.value);
        if (repo) {
            params.repository_owner = repo.owner;
            params.repository = repo.name;
        }
    }
    return params;
};

const handleFiltersChange = () => {
    const params = buildFilterParams();
    params.page = 1;
    router.get(route('pull-requests.index'), params, {
        preserveState: true,
        preserveScroll: true,
        only: ['requests'],
    });
};




</script>
