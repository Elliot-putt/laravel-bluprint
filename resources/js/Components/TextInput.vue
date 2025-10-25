<template>
    <input
        :id="id"
        :type="type"
        :value="modelValue"
        :class="inputClasses"
        :required="required"
        :autofocus="autofocus"
        :autocomplete="autocomplete"
        :placeholder="placeholder"
        @input="$emit('update:modelValue', $event.target.value)"
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
    type: {
        type: String,
        default: 'text',
    },
    modelValue: {
        type: [String, Number],
        default: '',
    },
    required: {
        type: Boolean,
        default: false,
    },
    autofocus: {
        type: Boolean,
        default: false,
    },
    autocomplete: {
        type: String,
        default: null,
    },
    placeholder: {
        type: String,
        default: null,
    },
});

defineEmits(['update:modelValue']);

const inputClasses = computed(() => {
    return 'mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-black focus:ring-black sm:text-sm';
});
</script>
