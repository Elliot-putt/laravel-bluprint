<template>
    <Link
        :href="href"
        :method="method"
        :as="as"
        :class="linkClasses"
        v-bind="$attrs"
    >
        <slot />
    </Link>
</template>

<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    href: {
        type: String,
        required: true,
    },
    active: {
        type: Boolean,
        default: false,
    },
    method: {
        type: String,
        default: 'get',
    },
    as: {
        type: String,
        default: 'a',
    },
});

const linkClasses = computed(() => {
    return props.active
        ? 'block pl-3 pr-4 py-2 border-l-4 border-black text-base font-medium text-black bg-gray-50 focus:outline-none focus:text-black focus:bg-gray-100 focus:border-gray-700'
        : 'block pl-3 pr-4 py-2 border-l-4 border-transparent text-base font-medium text-gray-600 hover:text-black hover:bg-gray-50 hover:border-gray-300 focus:outline-none focus:text-black focus:bg-gray-50 focus:border-gray-300';
});
</script>
