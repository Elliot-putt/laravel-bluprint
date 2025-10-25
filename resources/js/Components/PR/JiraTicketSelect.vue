<template>
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
</template>

<script setup lang="ts">
import { PropType } from 'vue';
import Select from '@/Components/ui/Select.vue';

const props = defineProps({
  jiraTickets: Array as PropType<App.Data.JiraTicket[]>,
  ticketKey: String
});

const emit = defineEmits(['update:ticketKey']);

function onSelectTicket(ticketKey: string) {
  emit('update:ticketKey', ticketKey);
}
</script> 