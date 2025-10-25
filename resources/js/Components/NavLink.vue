<template>
    <Link
        :href="href"
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
});

const linkClasses = computed(() => {
    return props.active
        ? 'border-black text-black focus:border-gray-700'
        : 'border-transparent text-gray-600 hover:text-black hover:border-gray-300 focus:text-black focus:border-gray-300';
});
</script>
