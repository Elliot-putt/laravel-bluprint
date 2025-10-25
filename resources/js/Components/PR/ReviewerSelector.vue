<template>
    <div>
        <Select
            :options="reviewers"
            :model-value="selectedReviewers"
            @update:modelValue="onSelectReviewers"
            label="Reviewers"
            placeholder="Search reviewers..."
            :getOptionLabel="(reviewer: any) => reviewer.name"
            :getOptionKey="(reviewer: any) => reviewer.id"
            :multiple="true"
            :disabled="disabled"
        >
            <template #option-prefix="{ option }">
                <span class="flex-shrink-0 h-6 w-6 rounded-full bg-gradient-to-br from-purple-500 to-pink-600 flex items-center justify-center text-xs text-white shadow-sm">
                    {{ option.name.charAt(0) }}
                </span>
            </template>
        </Select>
    </div>
</template>

<script setup lang="ts">
import { PropType } from 'vue';
import Select from '@/Components/ui/Select.vue';

const props = defineProps({
    reviewers: {
        type: Array as PropType<any[]>,
        required: true
    },
    selectedReviewers: {
        type: Array as PropType<any[]>,
        default: () => []
    },
    disabled: {
        type: Boolean,
        default: false
    }
});

const emit = defineEmits(['update:selectedReviewers']);

function onSelectReviewers(ids: any[]) {
    emit('update:selectedReviewers', ids);
}
</script>
