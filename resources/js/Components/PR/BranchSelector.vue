<template>
    <div class="flex flex-col md:flex-row gap-4 mb-6">
        <div class="flex-1">
            <Select
                :options="branches"
                :model-value="baseBranch"
                @update:modelValue="onSelectBaseBranch"
                label="Base Branch"
                placeholder="Search base branch..."
                :getOptionLabel="(branch: any) => branch.name"
                :getOptionKey="(branch: any) => branch.name"
                :disabled="!selectedRepository"
            />
        </div>
        <div class="flex-1">
            <Select
                :options="filteredNewBranches"
                :model-value="newBranch"
                @update:modelValue="onSelectNewBranch"
                label="New Branch"
                placeholder="Search or enter new branch name..."
                :getOptionLabel="(branch: any) => branch.name"
                :getOptionKey="(branch: any) => branch.name"
                :allowCustom="true"
                :disabled="!selectedRepository"
            />
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed, PropType } from 'vue';
import Select from '@/Components/ui/Select.vue';

const props = defineProps({
    branches: {
        type: Array as PropType<any[]>,
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
    }
});

const emit = defineEmits([
    'update:baseBranch',
    'update:baseBranchSha',
    'update:newBranch',
    'update:newBranchSha'
]);

const filteredNewBranches = computed(() => {
    if (!props.baseBranch) return props.branches;
    return props.branches.filter((branch: any) => branch.name !== props.baseBranch);
});

function onSelectBaseBranch(branchName: string) {
    const branch = props.branches.find((b: any) => b.name === branchName);
    emit('update:baseBranch', branchName);
    emit('update:baseBranchSha', branch ? branch.sha : '');
}

function onSelectNewBranch(branchName: string) {
    const branch = props.branches.find((b: any) => b.name === branchName);
    emit('update:newBranch', branchName);
    emit('update:newBranchSha', branch ? branch.sha : '');
}
</script>
