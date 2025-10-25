<template>
    <Head title="Generate Pull Request" />

    <AuthenticatedLayout>
        <template #header>
            <DashboardHeader
                :is-generating="isGenerating"
                :show-p-r-form="showPRForm"
                @start-again="refreshPage"
            />
        </template>

        <div class="py-6 h-100vh">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <ServiceUnavailableOverlay
                    :visible="serviceUnavailable"
                    @refresh="refreshPage"
                />

                <RecentBranchSuggestionsPanel
                    ref="recentBranchSuggestionsPanelRef"
                    :recently-made-branches="recentlyMadeBranches || []"
                    :show-p-r-form="showPRForm"
                    @select-branch="handleBranchSelection"
                    @loading-changed="handleLoadingChanged"
                    @refresh-branches="handleRefreshBranches"
                />

                <div class="bg-zinc-800/50 border border-zinc-700  shadow-lg sm:rounded-lg h-full backdrop-blur-sm relative">
                    <div class="p-6 h-full flex flex-col">
                        <PRSpecification
                            v-if="!showPRForm && !isGenerating"
                            :repositories="repositories"
                            :branch-list="branchList"
                            v-model:selected-repository="selectedRepository"
                            :branches="branches"
                            :is-loading-repo-data="isLoadingRepoData"
                            :is-loading-branch-data="isLoadingRepoData"
                            :templates="templates"
                            :jira-tickets="jiraTickets"
                            :default-template-id="defaultTemplateId"
                            v-model:ticket-key="ticketKey"
                            v-model:selected-template-id="selectedTemplateId"
                            :is-generating="isGenerating"
                            :can-generate="!!selectedRepository && !!branches.base && !!branches.new"
                            :recently-made-branches="recentlyMadeBranches"
                            :repositories-pagination="repositoriesPagination"
                            :branches-pagination="branchesPagination"
                            @repository-change="handleRepositoryChange"
                            @generate-p-r="generatePR"
                            @select-branch="handleBranchSelection"
                            @update:baseBranch="branches.base = $event"
                            @update:baseBranchSha="branches.baseBranchSha = $event"
                            @update:newBranch="branches.new = $event"
                            @update:newBranchSha="branches.newBranchSha = $event"
                            @load-more-repositories="loadMoreRepositories"
                            @search-repositories="searchRepositories"
                            @load-more-branches="loadMoreBranches"
                            @search-branches="searchBranches"
                        />

                        <PRForm
                            v-if="showPRForm || isGenerating"
                            v-model:title="prTitle"
                            v-model:body="prBody"
                            :labels="labels"
                            :reviewers="reviewers"
                            v-model:selected-labels="selectedLabels"
                            v-model:selected-reviewers="selectedReviewers"
                            v-model:draft="isDraft"
                            :template="selectedTemplate"
                            :is-generated="isGenerated"
                            :is-generating="isGenerating"
                            :is-currently-git-hub-p-r="isCurrentlyGitHubPR"
                            :is-loading-repo-data="isLoadingRepoData"
                            :is-submitting="isSubmitting"
                            :jira-issue-type="selectedTicket?.issueType || ''"
                            @create-p-r="createPR"
                            @update-p-r="updatePR"
                            @update:draft="updateDraft"
                        />
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { router, usePage } from '@inertiajs/vue3';
import { ref, reactive, computed, watch, nextTick, PropType, onMounted } from 'vue';
import axios from 'axios';
import PRSpecification from "@/Components/PR/PRSpecification.vue";
import RecentBranchSuggestionsPanel from "@/Components/RecentBranchSuggestionsPanel.vue";
import DashboardHeader from "@/Components/DashboardHeader.vue";
import ServiceUnavailableOverlay from "@/Components/ServiceUnavailableOverlay.vue";
import PRForm from '@/Components/PR/PRForm.vue';

const props = defineProps({
    serviceUnavailable:{
        type: Boolean,
        default: false,
    },
    isCurrentlyGitHubPR:{
        type: Boolean,
        default: false,
    },
    jiraTickets: {
        type: Array as PropType<App.Data.JiraTicketData[]>,
        default: () => []
    },
    repositories: {
        type: Array as PropType<App.Data.RepositoryData[]>,
        default: () => []
    },
    branches: {
        type: Array as PropType<App.Data.BranchData[]>,
        default: () => []
    },
    labels: {
        type: Array as PropType<App.Data.LabelData[]>,
        default: () => []
    },
    reviewers: {
        type: Array as PropType<App.Data.ReviewerData[]>,
        default: () => []
    },
    title: String,
    body: String,
    templates: {
        type: Array as PropType<App.Data.TemplateData[]>,
        default: () => []
    },
    defaultTemplateId: {
        type: Number,
        default: null
    },
    recentlyMadeBranches: {
        type: Array as PropType<App.Data.RecentBranchData[]>,
        default: () => []
    }
});

const page = usePage() as any;
const hasConfiguredJira = computed(() => page.props.auth.hasConfiguredJira);

// Pagination state - declare early
const repositoriesPagination = ref({
    currentPage: 1,
    hasMore: true,
    isLoading: false
});

const branchesPagination = ref({
    currentPage: 1,
    hasMore: true,
    isLoading: false
});

const allRepositories = ref<App.Data.RepositoryData[]>([]);
const allBranches = ref<App.Data.BranchData[]>([]);

const repositories = computed(() => {
    // If we have paginated repositories, use those
    if (allRepositories.value.length > 0) {
        return allRepositories.value;
    }
    // Otherwise return the original props (including undefined for loading state)
    return props.repositories;
});

const branchList = ref<App.Data.BranchData[]>([]);

// Initialize repositories on mount
onMounted(() => {
    if (props.repositories?.length) {
        allRepositories.value = [...props.repositories];
    }
});

watch(() => props.branches, (newBranches: App.Data.BranchData[]) => {
    if (newBranches?.length) {
        const processedBranches = newBranches.map((branch: App.Data.BranchData) => ({
            ...branch,
            id: (branch as any).id || branch.name,
            name: branch.name,
            link: (branch as any).html_url,
            sha: branch.sha,
        }));

        // If this is the first load or we're replacing data, replace the array
        if (allBranches.value.length === 0 || branchesPagination.value.currentPage === 1) {
            allBranches.value = processedBranches;
            branchList.value = processedBranches;
        } else {
            // If this is a pagination load, append the data
            const newItems = processedBranches.filter(newBranch =>
                !allBranches.value.some(existing => existing.name === newBranch.name)
            );
            allBranches.value = [...allBranches.value, ...newItems];
            branchList.value = [...allBranches.value];
        }
    } else {
        if (branchesPagination.value.currentPage === 1) {
            branchList.value = [];
            allBranches.value = [];
        }
    }
}, { immediate: true, deep: true });

const selectedRepository = ref<string | number>('');
const selectedRepositoryDetails = computed<App.Data.RepositoryData | null>(() => {
    if (!selectedRepository.value) return null;
    return repositories.value.find((r: App.Data.RepositoryData) => r.id === selectedRepository.value) || null;
});

const branches = reactive<{
    base: string;
    baseBranchSha: string;
    new: string;
    newBranchSha: string;
}>({
    base: '',
    baseBranchSha: '',
    new: '',
    newBranchSha: '',
});

const labels = computed<App.Data.LabelData[]>(() => {
    if (!props.labels?.length) return [];
    return props.labels?.map(label => ({
        id: label.id,
        name: label.name,
        color: label.color,
    })) || [];
});

const reviewers = computed<App.Data.ReviewerData[]>(() => {
    if (!props.reviewers?.length) return [];
    return props.reviewers?.map(reviewer => ({
        id: reviewer.id,
        name: reviewer.name,
        login: reviewer.login,
    })) || [];
});

const prTitle = ref<string>('');
const prBody = ref<string>('');
const selectedLabels = ref<(string | number)[]>([]);
const selectedReviewers = ref<(string | number)[]>([]);
const ticketKey = ref<string>('');
const selectedTemplateId = ref<number | undefined>(props.defaultTemplateId ?? undefined);
const isGenerating = ref<boolean>(false);
const isGenerated = ref<boolean>(false);
const isLoadingRepoData = ref<boolean>(false);
const isSubmitting = ref<boolean>(false);
const showPRForm = ref<boolean>(false);
const repositorySelectorRef = ref<any>(null);
const recentBranchSuggestionsPanelRef = ref<any>(null);
const isDraft = ref<boolean>(false);

const selectedTicket = computed<App.Data.JiraTicketData | null>(() => {
    if (!props.jiraTickets || props.jiraTickets.length === 0) return null;
    return props.jiraTickets.find((ticket: App.Data.JiraTicketData) => ticket.key === ticketKey.value) || null;
});

// Add computed property to get the selected template
const selectedTemplate = computed<App.Data.TemplateData | null>(() => {
    if (!selectedTemplateId.value || !props.templates?.length) {
        return null;
    }
    const template = props.templates.find((template: App.Data.TemplateData) => template.id === selectedTemplateId.value);
    return template || null;
});

// Add a ref to store pending branch selection
const pendingBranchSelection = ref<{branch: App.Data.BranchData, repo: App.Data.RepositoryData} | null>(null);

// Add a function to find repository by loading more pages if needed
const findRepositoryAndSelect = async (targetFullName: string, branch: App.Data.BranchData) => {
    // First check if repository already exists in loaded repositories
    let repo = allRepositories.value.find(r => r.fullName === targetFullName);

    if (repo) {
        // Repository found, proceed with selection
        proceedWithBranchSelection(repo, branch);
        return;
    }

    // Repository not found, keep loading more pages until found
    while (!repo && repositoriesPagination.value.hasMore && !repositoriesPagination.value.isLoading) {
        try {
            await loadMoreRepositories();
            // Check again after loading more
            repo = allRepositories.value.find(r => r.fullName === targetFullName);
        } catch (error) {
            console.error('Error searching for repository:', error);
            break;
        }
    }

    if (repo) {
        // Repository found after loading more pages
        proceedWithBranchSelection(repo, branch);
    } else {
        // Repository still not found, create temporary one as fallback
        const tempRepo = {
            id: `temp-${targetFullName}`,
            name: targetFullName.split('/')[1],
            owner: targetFullName.split('/')[0],
            fullName: targetFullName,
            description: null,
            link: null,
            defaultBranch: 'main'
        };
        allRepositories.value.push(tempRepo);
        proceedWithBranchSelection(tempRepo, branch);

        // Clear loading state since we're proceeding with temporary repo
        if (recentBranchSuggestionsPanelRef.value) {
            recentBranchSuggestionsPanelRef.value.clearLoadingState();
        }
    }
};

const handleLoadingChanged = (isLoading: boolean) => {
    // You can add any additional loading logic here if needed
    // For now, the visual feedback is handled in the RecentBranchSuggestions component
};

const handleRefreshBranches = async () => {
    try {
        router.reload({ only: ['recentlyMadeBranches'] });
    } catch (error) {
        console.error('Error refreshing branches:', error);
    }
};

// Separate function to handle the actual branch selection logic
const proceedWithBranchSelection = (repo: App.Data.RepositoryData, branch: App.Data.BranchData) => {
    // Store the pending selection to apply after repository data loads
    pendingBranchSelection.value = { branch, repo };

    selectedRepository.value = repo.id ?? '';
    handleRepositoryChange(repo.id);

    // Set the new branch and Jira ticket immediately
    branches.new = String(branch.name ?? '');
    branches.newBranchSha = String(branch.sha ?? '');
    const jiraTicket = extractJiraTicketFromBranch(String(branch.name ?? ''));
    if (jiraTicket && hasConfiguredJira.value) {
        ticketKey.value = jiraTicket;
    }

    if (repositorySelectorRef.value) {
        repositorySelectorRef.value.updateSearchField(String(repo.name ?? ''));
    }
};

const handleBranchSelection = ({ repository, branch }: { repository: App.Data.RepositoryData, branch: App.Data.BranchData }) => {
    // Use the new function to find repository and select branch
    findRepositoryAndSelect(repository.fullName, branch);
};

watch(() => branchList.value, (newBranches: App.Data.BranchData[]) => {
    if (newBranches?.length) {
        const defaultBranch = newBranches.find((branch: App.Data.BranchData) => branch.name === selectedRepositoryDetails.value?.defaultBranch);
        if (defaultBranch) {
            branches.base = defaultBranch.name;
            branches.baseBranchSha = defaultBranch.sha;
        } else {
            branches.baseBranchSha = '';
        }
    }
}, { deep: true });

const extractJiraTicketFromBranch = (branchName: string): string | null => {
    if (!branchName) return null;

    const jiraPattern = /([A-Z]+)-(\d+)/i;

    const match = branchName.match(jiraPattern);

    if (match) {
        return `${match[1].toUpperCase()}-${match[2]}`;
    }

    return null;
};

const handleRepositoryChange = (repoId: string | number) => {
    // Reset branch pagination when repository changes
    branchesPagination.value.currentPage = 1;
    branchesPagination.value.hasMore = true;
    branchesPagination.value.isLoading = false;
    allBranches.value = [];

    if (repoId) {
        // Find repository by ID (including temporary IDs)
        const repo = repositories.value.find(r => r.id === repoId);
        if (repo) {
            isLoadingRepoData.value = true;
            router.get(route('dashboard'), {
                repository: repo.name,
                repository_owner: repo.owner,
            }, {
                preserveState: true,
                preserveScroll: true,
                only: ['branches', 'labels', 'reviewers' , 'isCurrentlyGitHubPR'],
                onSuccess: () => {
                    isLoadingRepoData.value = false;

                    // Apply pending branch selection if exists
                    if (pendingBranchSelection.value) {
                        nextTick(() => {
                            // Get the actual repository details with the correct defaultBranch
                            const actualRepo = selectedRepositoryDetails.value;
                            if (actualRepo && actualRepo.defaultBranch) {
                                // Use the actual defaultBranch from the loaded repository data
                                const baseBranchName = actualRepo.defaultBranch;
                                const baseBranch = branchList.value.find((b: App.Data.BranchData) => b.name === baseBranchName);
                                if (baseBranch) {
                                    branches.base = String(baseBranch.name ?? '');
                                    branches.baseBranchSha = String(baseBranch.sha ?? '');
                                } else {
                                    // If default branch not found in the list, use the first branch
                                    const firstBranch = branchList.value[0];
                                    if (firstBranch) {
                                        branches.base = String(firstBranch.name ?? '');
                                        branches.baseBranchSha = String(firstBranch.sha ?? '');
                                    } else {
                                        branches.base = baseBranchName;
                                        branches.baseBranchSha = '';
                                    }
                                }
                            } else {
                                // Fallback to 'main' if no defaultBranch is available
                                const mainBranch = branchList.value.find((b: App.Data.BranchData) => b.name === 'main') ||
                                                 branchList.value.find((b: App.Data.BranchData) => b.name === 'master') ||
                                                 branchList.value[0];
                                if (mainBranch) {
                                    branches.base = String(mainBranch.name ?? '');
                                    branches.baseBranchSha = String(mainBranch.sha ?? '');
                                }
                            }
                            // Clear the pending selection
                            pendingBranchSelection.value = null;

                            // Clear loading state in the RecentBranchSuggestions component
                            if (recentBranchSuggestionsPanelRef.value) {
                                recentBranchSuggestionsPanelRef.value.clearLoadingState();
                            }
                        });
                    }
                },
                onError: () => {
                    isLoadingRepoData.value = false;
                    // Clear pending selection on error
                    pendingBranchSelection.value = null;

                    // Clear loading state on error too
                    if (recentBranchSuggestionsPanelRef.value) {
                        recentBranchSuggestionsPanelRef.value.clearLoadingState();
                    }
                }
            });
        }
    } else {
        branches.base = '';
        branches.new = '';
        isLoadingRepoData.value = false;
        pendingBranchSelection.value = null;
    }

    if (!branches.new) {
        prTitle.value = '';
        prBody.value = '';
        selectedLabels.value = [];
        selectedReviewers.value = [];
        ticketKey.value = '';
        selectedTemplateId.value = props.defaultTemplateId;
        isGenerated.value = false;
    }
};

const generatePR = () => {
    if (!selectedRepository.value || !branches.base || !branches.new) return;
    if (!selectedRepositoryDetails.value) return;

    isGenerating.value = true;
    showPRForm.value = true;

    router.get(route('dashboard'), {
        repository: selectedRepositoryDetails.value?.name,
        repository_owner: selectedRepositoryDetails.value?.owner,
        base_branch_sha: branches.baseBranchSha,
        target_branch_sha: branches.newBranchSha,
        target_branch: branches.new,
        base_branch: branches.base,
        ticket_key: ticketKey.value,
        template_id: selectedTemplateId.value,
    }, {
        preserveState: true,
        preserveScroll: true,
        only: ['title', 'body', 'isCurrentlyGitHubPR', 'labels', 'templates'],
        onSuccess: () => {
            isGenerating.value = false;
            isGenerated.value = true;
            prTitle.value = props.title ?? '';
            prBody.value = props.body ?? '';
        },
        onError: () => {
            isGenerating.value = false;
            showPRForm.value = false;
        }
    });
};

const updatePR = () => {
    if (!selectedRepositoryDetails.value) return;
    isSubmitting.value = true;
    return router.post(route('submit.pr'), {
        repository: selectedRepositoryDetails.value.name,
        repository_owner: selectedRepositoryDetails.value.owner,
        base_branch_sha: branches.baseBranchSha,
        target_branch_sha: branches.newBranchSha,
        head: branches.new,
        base: branches.base,
        ticket_key: ticketKey.value,
        template_id: selectedTemplateId.value,
        labels: selectedLabels.value.map(labelId => {
            const label = labels.value.find(l => l.id === labelId);
            return label ? label.name : '';
        }),
        reviewers: selectedReviewers.value.map(reviewerId => {
            const reviewer = reviewers.value.find(r => r.id === reviewerId);
            return reviewer ? reviewer.name : '';
        }),
        isCurrentlyGitHubPR: props.isCurrentlyGitHubPR,
        title: prTitle.value,
        body: prBody.value,
        isDraft: isDraft.value,
    }, {
        preserveState: false,
        preserveScroll: false,
        onSuccess: () => {
            isSubmitting.value = false;
        },
        onError: () => {
            isSubmitting.value = false;
        }
    });
};

const createPR = () => {
    if (!selectedRepositoryDetails.value) return;
    isSubmitting.value = true;
    return router.post(route('submit.pr'), {
        repository: selectedRepositoryDetails.value.name,
        repository_owner: selectedRepositoryDetails.value.owner,
        base_branch_sha: branches.baseBranchSha,
        target_branch_sha: branches.newBranchSha,
        head: branches.new,
        base: branches.base,
        ticket_key: ticketKey.value,
        template_id: selectedTemplateId.value,
        labels: selectedLabels.value.map(labelId => {
            const label = labels.value.find(l => l.id === labelId);
            return label ? label.name : '';
        }),
        reviewers: selectedReviewers.value.map(reviewerId => {
            const reviewer = reviewers.value.find(r => r.id === reviewerId);
            return reviewer ? reviewer.name : '';
        }),
        isCurrentlyGitHubPR: props.isCurrentlyGitHubPR,
        title: prTitle.value,
        body: prBody.value,
        isDraft: isDraft.value,
    }, {
        preserveState: false,
        preserveScroll: false,
        onSuccess: () => {
            isSubmitting.value = false;
        },
        onError: () => {
            isSubmitting.value = false;
        }
    });
};

const loadMoreRepositories = async (searchTerm: string = '') => {
    if (repositoriesPagination.value.isLoading || !repositoriesPagination.value.hasMore) {
        return;
    }

    repositoriesPagination.value.isLoading = true;

    try {
        const response = await axios.post(route('dashboard.load-more-repositories'), {
            page: repositoriesPagination.value.currentPage + 1,
            per_page: 100,
            search: searchTerm
        });

        const data = response.data;
        const newRepos = data.repositories || [];

        // Filter out duplicates
        const uniqueNewRepos = newRepos.filter((newRepo: App.Data.RepositoryData) =>
            !allRepositories.value.some(existing => existing.id === newRepo.id)
        );

        if (uniqueNewRepos.length > 0) {
            allRepositories.value = [...allRepositories.value, ...uniqueNewRepos];
        }

        repositoriesPagination.value.currentPage = data.pagination.current_page;
        repositoriesPagination.value.hasMore = data.pagination.has_more;
    } catch (error) {
        console.error('Error loading more repositories:', error);
    } finally {
        repositoriesPagination.value.isLoading = false;
    }
};

const loadMoreBranches = async (searchTerm: string = '') => {
    if (branchesPagination.value.isLoading || !branchesPagination.value.hasMore || !selectedRepositoryDetails.value) {
        return;
    }

    branchesPagination.value.isLoading = true;

    try {
        const response = await axios.post(route('dashboard.load-more-branches'), {
            repository: selectedRepositoryDetails.value.name,
            repository_owner: selectedRepositoryDetails.value.owner,
            page: branchesPagination.value.currentPage + 1,
            per_page: 100,
            search: searchTerm
        });

        const data = response.data;
        const newBranches = data.branches || [];

        const processedBranches = newBranches.map((branch: App.Data.BranchData) => ({
            ...branch,
            id: (branch as any).id || branch.name,
            name: branch.name,
            link: (branch as any).html_url,
            sha: branch.sha,
        }));

        // Filter out duplicates
        const uniqueNewBranches = processedBranches.filter((newBranch: App.Data.BranchData) =>
            !allBranches.value.some(existing => existing.name === newBranch.name)
        );

        if (uniqueNewBranches.length > 0) {
            allBranches.value = [...allBranches.value, ...uniqueNewBranches];
            branchList.value = [...allBranches.value];
        }

        branchesPagination.value.currentPage = data.pagination.current_page;
        branchesPagination.value.hasMore = data.pagination.has_more;
    } catch (error) {
        console.error('Error loading more branches:', error);
    } finally {
        branchesPagination.value.isLoading = false;
    }
};

const searchRepositories = (searchTerm: string) => {
    // Reset pagination when searching
    repositoriesPagination.value.currentPage = 1;
    repositoriesPagination.value.hasMore = true;

    if (!searchTerm) {
        // If search is cleared, reset to initial repositories
        allRepositories.value = [...(props.repositories || [])];
    }
};

const searchBranches = (searchTerm: string) => {
    // Reset pagination when searching
    branchesPagination.value.currentPage = 1;
    branchesPagination.value.hasMore = true;
};

const updateDraft = (value: boolean) => {
    isDraft.value = value;
};

const refreshPage = () => {
    window.location.reload();
};

</script>
