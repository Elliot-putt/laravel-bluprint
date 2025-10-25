<template>
    <div class="flex-1 mb-6">
        <Select
            :options="options"
            :model-value="selectedOption"
            :get-option-label="stateLabel"
            :get-option-key="stateKey"
            :placeholder="'Open Pull Requests'"
            label="State"
            @update:model-value="handleStateChange"
        >
            <template #option-prefix="{ option }">
                <span class="w-3 h-3 rounded-full mr-2" :class="option.colorClass"></span>
            </template>
        </Select>
    </div>
</template>

<script setup lang="ts">
import { computed, PropType } from 'vue';
import Select from '@/Components/ui/Select.vue';

const props = defineProps({
    options: {
        type: Array as PropType<{ value: string; label: string; colorClass: string }[]>,
        default: () => []
    },
    selectedState: {
        type: String,
        default: 'open'
    }
});

const emit = defineEmits(['update:selected-state']);

const selectedOption = computed(() => {
    return props.options.find(option => option.value === props.selectedState) || props.options[0];
});

const handleStateChange = (option: any) => {
    const stateValue = typeof option === 'string' ? option : option?.value;
    if (stateValue) {
        emit('update:selected-state', stateValue);
    }
};

const stateLabel = (state: { value: string; label: string }) => state.label;
const stateKey = (state: { value: string }) => state.value;
</script>
