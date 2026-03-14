<template>
    <label class="block text-sm font-medium text-gray-900 mb-1">
        <slot>{{ value }}</slot>
    </label>
</template>

<script setup lang="ts">
defineProps<{ value?: string }>();
</script>
