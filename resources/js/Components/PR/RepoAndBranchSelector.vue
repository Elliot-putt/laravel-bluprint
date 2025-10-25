<template>
    <div class="mb-6">
        <!-- Repository Selection -->
        <RepositorySelector
            v-if="!isLoadingRepoData"
            ref="repositorySelectorRef"
            :repositories="repositories"
            v-model:selected-repository="selectedRepository"
            @update:selected-repository="handleRepositoryChange"
        />
        <div v-else>
            <label class="block text-sm font-medium text-zinc-300 mb-1">Repositories</label>
            <DarkSkeleton class="h-10 w-full mb-1" variant="list"/>
        </div>
        <!-- Branch Selection -->
        <div v-if="isLoadingBranchData" class="mt-6">
            <div class="flex flex-col md:flex-row gap-4">
                <div class="flex-1">
                    <label class="block text-sm font-medium text-zinc-300 mb-1">Base Branch</label>
                    <DarkSkeleton class="h-10 w-full mb-1" variant="list"/>
                </div>
                <div class="flex-1">
                    <label class="block text-sm font-medium text-zinc-300 mb-1">New Branch</label>
                    <DarkSkeleton class="h-10 w-full mb-1" variant="list"/>
                </div>
            </div>
        </div>
        <BranchSelector v-else :branches="branches" :selected-repository="selectedRepository"
                        v-model:base-branch="baseBranch" v-model:base-branch-sha="baseBranchSha"
                        v-model:new-branch="newBranch" v-model:new-branch-sha="newBranchSha"/>
    </div>
</template>

<script setup lang="ts">
import {ref, watch, PropType} from 'vue';
import RepositorySelector from '@/Components/PR/RepositorySelector.vue';
import BranchSelector from '@/Components/PR/BranchSelector.vue';
import {DarkSkeleton} from '@/Components/ui/skeleton';

const props = defineProps({
    repositories: {
        type: Array as PropType<App.Data.RepositoryData[]>,
        required: true
    },
    branches: {
        type: Array as PropType<App.Data.BranchData[]>,
        required: true
    },
    selectedRepository: {
        type: [String, Number],
        default: ''
    },
    baseBranch: {
        type: String,
        default: ''
    },
    baseBranchSha: {
        type: String,
        default: ''
    },
    newBranch: {
        type: String,
        default: ''
    },
    newBranchSha: {
        type: String,
        default: ''
    },
    isLoadingBranchData: {
        type: Boolean,
        default: false
    },
    isLoadingRepoData: {
        type: Boolean,
        default: false
    }
});

const emit = defineEmits([
    'update:selectedRepository',
    'update:baseBranch',
    'update:baseBranchSha',
    'update:newBranch',
    'update:newBranchSha',
    'repositoryChange'
]);

// Template ref
const repositorySelectorRef = ref(null);

// Local state
const selectedRepository = ref(props.selectedRepository);
const baseBranch = ref(props.baseBranch);
const baseBranchSha = ref(props.baseBranchSha);
const newBranch = ref(props.newBranch);
const newBranchSha = ref(props.newBranchSha);

// Watch for prop changes
watch(() => props.selectedRepository, (newValue) => {
    selectedRepository.value = newValue;
});

watch(() => props.baseBranch, (newValue) => {
    baseBranch.value = newValue;
});

watch(() => props.baseBranchSha, (newValue) => {
    baseBranchSha.value = newValue;
});

watch(() => props.newBranch, (newValue) => {
    newBranch.value = newValue;
});

watch(() => props.newBranchSha, (newValue) => {
    newBranchSha.value = newValue;
});

// Watch for local state changes
watch(selectedRepository, (newValue) => {
    emit('update:selectedRepository', newValue);
});

watch(baseBranch, (newValue) => {
    emit('update:baseBranch', newValue);
});

watch(baseBranchSha, (newValue) => {
    emit('update:baseBranchSha', newValue);
});

watch(newBranch, (newValue) => {
    emit('update:newBranch', newValue);
});

watch(newBranchSha, (newValue) => {
    emit('update:newBranchSha', newValue);
});

// Methods
const handleRepositoryChange = (repoId) => {
    emit('repositoryChange', repoId);
};

const updateRepositorySearch = (repoName) => {
    if (repositorySelectorRef.value) {
        repositorySelectorRef.value.updateSearchField(repoName);
    }
};

defineExpose({
    updateRepositorySearch
});
</script>
