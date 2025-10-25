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
    return 'block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-black';
});
</script>
