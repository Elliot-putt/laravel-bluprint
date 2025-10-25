<template>
    <div class="mb-6">
        <Select
            :options="repositories"
            :model-value="selectedRepository"
            @update:modelValue="onSelectRepository"
            :label="'Repository'"
            :placeholder="'Search repositories...'"
            :getOptionLabel="(repo: any) => repo && repo.name ? repo.name : ''"
            :getOptionKey="(repo: any) => repo && repo.id ? repo.id : ''"
            :optionDescription="(repo: any) => (repo && repo.description ? repo.description : '')"
        />
        <p class="mt-1 text-xs text-zinc-400">{{ selectedRepoDescription || '' }}</p>
        <div v-if="selectedRepoDetails" class="mt-2 flex items-center">
            <a :href="selectedRepoDetails.link" target="_blank" rel="noopener noreferrer"
               class="inline-flex items-center gap-1.5 text-sm text-purple-400 hover:text-purple-300 transition-colors group">
                <span>View on GitHub</span>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transform transition-transform group-hover:translate-x-0.5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L12.586 11H5a1 1 0 110-2h7.586l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd" />
                </svg>
            </a>
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed, PropType } from 'vue';
import Select from '@/Components/ui/Select.vue';

const props = defineProps({
    repositories: {
        type: Array as PropType<App.Data.RepositoryData[]>,
        required: true
    },
    selectedRepository: {
        type: [String, Number],
        default: ''
    }
});

const emit = defineEmits(['update:selectedRepository', 'clear']);

const selectedRepoDetails = computed(() => {
    if (!props.selectedRepository) return null;
    return props.repositories.find((r: any) => r.id === props.selectedRepository);
});

const selectedRepoDescription = computed(() => {
    if (!props.selectedRepository) return 'Please select a repository to continue';
    const repo = props.repositories.find((r: any) => r.id === props.selectedRepository);
    return typeof repo?.description === 'string' ? repo.description : '';
});

function onSelectRepository(repoId: string | number) {
    emit('update:selectedRepository', repoId);
}
</script>
