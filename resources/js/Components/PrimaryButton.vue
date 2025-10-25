<template>
    <button
        :type="type"
        :disabled="disabled"
        :class="buttonClasses"
        v-bind="$attrs"
    >
        <slot />
    </button>
</template>

<script setup>
import { computed } from 'vue';

defineProps({
    type: {
        type: String,
        default: 'submit',
    },
    disabled: {
        type: Boolean,
        default: false,
    },
});

const buttonClasses = computed(() => {
    return 'inline-flex items-center px-4 py-2 bg-black border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-800 focus:bg-gray-800 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-black focus:ring-offset-2 transition ease-in-out duration-150 disabled:opacity-25';
});
</script>
