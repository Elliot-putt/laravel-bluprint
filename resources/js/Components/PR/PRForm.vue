<template>
    <LoadingProgress :isGenerating="isGenerating" />

    <PRTitleInput
        v-model:title="prTitle"
        :is-generating="isGenerating"
        :is-generated="isGenerated"
        @update:title="updateTitle"
    />

    <PRBodyEditor
        v-model:body="prBody"
        :is-generating="isGenerating"
        :is-generated="isGenerated"
        @update:body="updateBody"
    />

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <PRLabelSelector
            :labels="labels"
            :selected-labels="selectedLabels"
            :is-loading-repo-data="isLoadingRepoData"
            :is-generated="isGenerated"
            @update:selected-labels="updateSelectedLabels"
        />
        <PRReviewerSelector
            :reviewers="reviewers"
            :selected-reviewers="selectedReviewers"
            :is-loading-repo-data="isLoadingRepoData"
            :is-generated="isGenerated"
            @update:selected-reviewers="updateSelectedReviewers"
        />
    </div>

    <PRActionButton
        :is-currently-git-hub-p-r="isCurrentlyGitHubPR"
        :is-generated="isGenerated"
        :is-submitting="isSubmitting"
        :is-draft="isDraft"
        @create-pr="createPR"
        @update-pr="updatePR"
        @update:draft="updateDraft"
    />
</template>

<script setup lang="ts">
import { ref, watch, PropType } from 'vue';
import LoadingProgress from '@/Components/PR/LoadingProgress.vue';
import PRTitleInput from './PRTitleInput.vue';
import PRBodyEditor from './PRBodyEditor.vue';
import PRLabelSelector from './PRLabelSelector.vue';
import PRReviewerSelector from './PRReviewerSelector.vue';
import PRActionButton from './PRActionButton.vue';

const props = defineProps({
    title: {
        type: String,
        default: ''
    },
    body: {
        type: String,
        default: ''
    },
    jiraIssueType: {
        type: String,
        default: ''
    },
    labels: {
        type: Array as PropType<App.Data.LabelData[]>,
        default: () => []
    },
    reviewers: {
        type: Array as PropType<App.Data.ReviewerData[]>,
        default: () => []
    },
    selectedLabels: {
        type: Array as PropType<(string | number)[]>,
        default: () => []
    },
    selectedReviewers: {
        type: Array as PropType<(string | number)[]>,
        default: () => []
    },
    template: {
        type: Object as PropType<App.Data.TemplateData | null>,
        default: null
    },
    isGenerated: {
        type: Boolean,
        default: false
    },
    isGenerating: {
        type: Boolean,
        default: false
    },
    isLoadingRepoData: {
        type: Boolean,
        default: false
    },
    isCurrentlyGitHubPR: {
        type: Boolean,
        default: false,
    },
    isSubmitting: {
        type: Boolean,
        default: false
    },
    isDraft: {
        type: Boolean,
        default: false
    }
});

const emit = defineEmits([
    'update:title',
    'update:body',
    'update:selectedLabels',
    'update:selectedReviewers',
    'update:draft',
    'createPR',
    'updatePR',
]);

const prTitle = ref(props.title);
const prBody = ref(props.body);
const selectedLabels = ref([...props.selectedLabels]);
const selectedReviewers = ref([...props.selectedReviewers]);
const isDraft = ref(props.isDraft || false);

// Function to match default labels from template with available labels
const matchDefaultLabels = () => {
    if (!props.template?.defaultLabels || !props.labels?.length) {
        return;
    }
    
    const defaultLabels = props.template.defaultLabels;
    const matchedLabelIds: (string | number)[] = [];
    
    defaultLabels.forEach(defaultLabel => {
        const matchedLabel = props.labels.find(label => 
            label.name.toLowerCase() === defaultLabel.toLowerCase()
        );
        if (matchedLabel) {
            matchedLabelIds.push(matchedLabel.id);
        }
    });
    
    if (matchedLabelIds.length > 0) {
        // Only update if we're not already selecting these labels
        const newLabels = matchedLabelIds.filter(id => !selectedLabels.value.includes(id));
        if (newLabels.length > 0) {
            const newSelectedLabels = [...selectedLabels.value, ...newLabels];
            selectedLabels.value = newSelectedLabels;
            emit('update:selectedLabels', newSelectedLabels);
        }
    }
};

watch(() => props.title, (newValue) => {
    prTitle.value = newValue;
});

watch(() => props.body, (newValue) => {
    prBody.value = newValue;
    if (props.jiraIssueType) {
        const jiraType = props.jiraIssueType.toLowerCase();
        switch (jiraType) {
            case 'bug':
                const bugLabels = props.labels
                    .filter(label => label.name.toLowerCase().includes('bug'))
                    .map(label => label.id);
                selectedLabels.value = bugLabels;
                emit('update:selectedLabels', bugLabels);
                break;
            case 'feature':
                const featureLabels = props.labels
                    .filter(label =>
                        label.name.toLowerCase().includes('enhancement') ||
                        label.name.toLowerCase().includes('feature')
                    )
                    .map(label => label.id);
                selectedLabels.value = featureLabels;
                emit('update:selectedLabels', featureLabels);
                break;
            default:
                break;
        }
    }
});

watch(() => props.selectedLabels, (newValue) => {
    selectedLabels.value = [...newValue];
});

watch(() => props.selectedReviewers, (newValue) => {
    selectedReviewers.value = [...newValue];
});

watch(() => props.isDraft, (newValue) => {
    isDraft.value = newValue;
}, { immediate: true });

watch(() => props.jiraIssueType, (newType) => {
    if (newType) {
        const jiraType = newType.toLowerCase();
        switch (jiraType) {
            case 'bug':
                const bugLabels = props.labels
                    .filter(label => label.name.toLowerCase().includes('bug'))
                    .map(label => label.id);
                selectedLabels.value = bugLabels;
                emit('update:selectedLabels', bugLabels);
                break;
            case 'feature':
                const featureLabels = props.labels
                    .filter(label =>
                        label.name.toLowerCase().includes('enhancement') ||
                        label.name.toLowerCase().includes('feature')
                    )
                    .map(label => label.id);
                selectedLabels.value = featureLabels;
                emit('update:selectedLabels', featureLabels);
                break;
            default:
                break;
        }
    }
});

// Watch for template changes to apply default labels
watch(() => props.template, () => {
    if (props.isGenerated && props.labels?.length && props.template?.defaultLabels) {
        setTimeout(() => matchDefaultLabels(), 50);
    }
}, { deep: true });

// Watch for labels to be loaded and apply default labels
watch(() => props.labels, () => {
    if (props.isGenerated && props.labels?.length && props.template?.defaultLabels) {
        setTimeout(() => matchDefaultLabels(), 50);
    }
});

// Watch for isGenerated to apply default labels when PR is generated
watch(() => props.isGenerated, (isGenerated) => {
    if (isGenerated && props.labels?.length && props.template?.defaultLabels) {
        setTimeout(() => matchDefaultLabels(), 100);
    }
});

const updateTitle = () => {
    emit('update:title', prTitle.value);
};

const updateBody = () => {
    emit('update:body', prBody.value);
};

const updateSelectedLabels = (newLabels: (string | number)[]) => {
    selectedLabels.value = [...newLabels];
    emit('update:selectedLabels', newLabels);
};

const updateSelectedReviewers = (newReviewers: (string | number)[]) => {
    selectedReviewers.value = [...newReviewers];
    emit('update:selectedReviewers', newReviewers);
};

const updateDraft = (draftValue: boolean) => {
    isDraft.value = draftValue;
    emit('update:draft', draftValue);
};

const createPR = () => {
    emit('createPR');
};
const updatePR = () => {
    emit('updatePR');
};
</script>
