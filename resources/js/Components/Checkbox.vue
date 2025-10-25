<template>
    <input
        :id="id"
        :name="name"
        :value="value"
        :checked="checked"
        :class="checkboxClasses"
        type="checkbox"
        @change="$emit('update:checked', $event.target.checked)"
        v-bind="$attrs"
    />
</template>

<script setup>
import { computed } from 'vue';

defineProps({
    id: {
        type: String,
        default: null,
    },
    name: {
        type: String,
        default: null,
    },
    value: {
        type: [String, Number, Boolean],
        default: null,
    },
    checked: {
        type: Boolean,
        default: false,
    },
});

defineEmits(['update:checked']);

const checkboxClasses = computed(() => {
    return 'rounded border-gray-300 text-black shadow-sm focus:border-black focus:ring focus:ring-black focus:ring-opacity-50';
});
</script>
