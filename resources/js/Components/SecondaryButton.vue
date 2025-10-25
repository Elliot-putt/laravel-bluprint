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
        default: 'button',
    },
    disabled: {
        type: Boolean,
        default: false,
    },
});

const buttonClasses = computed(() => {
    return 'inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-black focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150';
});
</script>
