<template>
    <div>
        <Select
            :options="labels"
            :model-value="selectedLabels"
            @update:modelValue="onSelectLabels"
            label="Labels"
            placeholder="Search labels..."
            :getOptionLabel="(label: any) => label.name"
            :getOptionKey="(label: any) => label.id"
            :multiple="true"
            :disabled="disabled"
        >
            <template #option-prefix="{ option }">
                <span class="w-3 h-3 rounded-full inline-block mr-1" :style="{ backgroundColor: `#${option.color}` }"></span>
            </template>
        </Select>
    </div>
</template>

<script setup lang="ts">
import { PropType, h } from 'vue';
import Select from '@/Components/ui/Select.vue';

const props = defineProps({
    labels: {
        type: Array as PropType<any[]>,
        required: true
    },
    selectedLabels: {
        type: Array as PropType<any[]>,
        default: () => []
    },
    disabled: {
        type: Boolean,
        default: false
    }
});

const emit = defineEmits(['update:selectedLabels']);

function onSelectLabels(ids: any[]) {
    emit('update:selectedLabels', ids);
}
</script>
