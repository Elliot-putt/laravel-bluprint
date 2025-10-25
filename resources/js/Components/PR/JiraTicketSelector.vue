<template>
    <div class="mb-6">
        <Select
            :options="jiraTickets"
            :model-value="ticketKey"
            @update:modelValue="onSelectTicket"
            label="JIRA Ticket"
            placeholder="Search tickets or enter custom ticket key..."
            :getOptionLabel="(ticket: any) => ticket.key"
            :getOptionKey="(ticket: any) => ticket.key"
            :optionDescription="(ticket: any) => ticket.summary || 'No summary available'"
            :allowCustom="true"
            description="Enter a Jira ticket for AI to use when generating PR context."
        />
    </div>
</template>

<script setup lang="ts">
import { computed, PropType } from 'vue';
import Select from '@/Components/ui/Select.vue';

const props = defineProps({
    jiraTickets: {
        type: Array as PropType<any[]>,
        default: () => []
    },
    ticketKey: {
        type: String,
        default: ''
    }
});

const emit = defineEmits(['update:ticketKey']);

const selectedTicketDetails = computed(() => {
    if (!props.ticketKey) return null;
    return props.jiraTickets.find((ticket: any) => ticket.key === props.ticketKey);
});

const ticketDescription = computed(() => {
    if (!props.ticketKey) return 'Enter a Jira ticket for AI to use when generating PR context.';
    const ticket = props.jiraTickets.find((ticket: any) => ticket.key === props.ticketKey);
    return ticket?.summary || 'Custom ticket key entered';
});

function onSelectTicket(ticketKey: string) {
    emit('update:ticketKey', ticketKey);
}
</script>
