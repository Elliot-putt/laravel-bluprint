<template>
    <div v-if="$page.props.auth.hasConfiguredJira">
        <label for="jiraTicket" class="block text-sm font-medium text-zinc-300 mb-1">JIRA Ticket</label>
        <input
            type="text"
            id="jiraTicket"
            v-model="ticketKey"
            class="w-full px-4 py-2 border border-zinc-600 shadow-sm focus:ring-purple-500 focus:border-purple-500 bg-zinc-700/70 text-white transition-shadow hover:bg-zinc-600/70"
            placeholder="Enter JIRA ticket number"
            @input="updateTicketKey"
        />
        <p class="text-zinc-400 text-xs mt-1">Enter a Jira ticket for AI to use when generating pull request context.</p>
    </div>
</template>

<script setup>
import { ref, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';

const $page = usePage();

const props = defineProps({
    ticketKey: {
        type: String,
        default: ''
    }
});

const emit = defineEmits(['update:ticketKey']);

const ticketKey = ref(props.ticketKey);

// Watch for prop changes
watch(() => props.ticketKey, (newValue) => {
    ticketKey.value = newValue;
});

// Methods
const updateTicketKey = () => {
    emit('update:ticketKey', ticketKey.value);
};
</script>
