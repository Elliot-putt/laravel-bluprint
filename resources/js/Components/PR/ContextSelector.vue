<template>
    <div class="flex flex-col lg:flex-row gap-3 mb-6 items-start">
        <div v-if="hasConfiguredJira" class="flex-1 min-w-0">
            <Deferred data="recentlyMadeBranches">
                <template #fallback>
                    <label class="block text-sm font-medium text-zinc-300 mb-1">JIRA Ticket</label>
                    <DarkSkeleton class="h-10 w-full" variant="list" />
                    <p class="text-zinc-400 text-xs mt-1">Enter a Jira ticket for AI to use when generating PR context.</p>
                </template>

                <template #default>
                    <JiraTicketSelector
                        :jira-tickets="jiraTickets"
                        v-model:ticket-key="ticketKey"
                    />
                </template>
            </Deferred>
        </div>

        <div class="flex-1 min-w-0">
            <div v-if="isLoadingRepoData">
                <label class="block text-sm font-medium text-zinc-300 mb-1">PR Template</label>
                <DarkSkeleton class="h-10 w-full" variant="list" />
                <p class="text-zinc-400 text-xs mt-1">Select a template for AI to use when generating PR content.</p>
            </div>
            <TemplateSelector
                v-else
                :templates="templates"
                :default-template-id="defaultTemplateId"
                v-model:selected-template-id="selectedTemplateId"
                :disabled="isGenerating"
            />
        </div>

        <div class="flex-shrink-0 pt-6">
            <button
                @click="generatePR"
                :disabled="isGenerating || !canGenerate"
                class="px-8 py-2 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 text-white shadow-sm flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed transition-all h-10"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M5 2a1 1 0 011 1v1h1a1 1 0 010 2H6v1a1 1 0 01-2 0V6H3a1 1 0 010-2h1V3a1 1 0 011-1zm0 10a1 1 0 011 1v1h1a1 1 0 110 2H6v1a1 1 0 11-2 0v-1H3a1 1 0 110-2h1v-1a1 1 0 011-1zm7-10a1 1 0 01.707.293l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L14.586 9H7a1 1 0 110-2h7.586l-3.293-3.293A1 1 0 0112 2z" clip-rule="evenodd" />
                </svg>
                {{ isGenerating ? 'Generating...' : 'Generate PR' }}
            </button>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, computed, watch, PropType } from 'vue';
import JiraTicketSelector from '@/Components/PR/JiraTicketSelector.vue';
import TemplateSelector from '@/Components/PR/TemplateSelector.vue';
import { DarkSkeleton } from '@/Components/ui/skeleton';
import {Deferred, usePage} from '@inertiajs/vue3';
import RecentBranchSuggestions from "@/Components/PR/RecentBranchSuggestions.vue";

const page = usePage();
const hasConfiguredJira = computed(() => page.props.auth.hasConfiguredJira);

const props = defineProps({
    templates: {
        type: Array as PropType<App.Data.TemplateData[]>,
        default: () => []
    },
    jiraTickets: {
        type: Array as PropType<App.Data.JiraTicket[]>,
        default: () => []
    },
    defaultTemplateId: {
        type: Number,
        default: null
    },
    ticketKey: {
        type: String,
        default: ''
    },
    selectedTemplateId: {
        type: Number,
        default: null
    },
    isGenerating: {
        type: Boolean,
        default: false
    },
    isLoadingRepoData: {
        type: Boolean,
        default: false
    },
    canGenerate: {
        type: Boolean,
        default: false
    }
});

const emit = defineEmits([
    'update:ticketKey',
    'update:selectedTemplateId',
    'generatePR'
]);

const ticketKey = ref(props.ticketKey);
const selectedTemplateId = ref(props.selectedTemplateId || props.defaultTemplateId);

watch(() => props.ticketKey, (newValue) => {
    ticketKey.value = newValue;
});

watch(() => props.selectedTemplateId, (newValue) => {
    selectedTemplateId.value = newValue;
});

watch(ticketKey, (newValue) => {
    emit('update:ticketKey', newValue);
});

watch(selectedTemplateId, (newValue) => {
    emit('update:selectedTemplateId', newValue);
});

const generatePR = () => {
    emit('generatePR');
};
</script>
