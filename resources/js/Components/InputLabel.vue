<template>
    <label :for="for" class="block text-sm font-medium text-gray-900 mb-1">
        <slot />
    </label>
</template>

<script setup>
defineProps({
    for: {
        type: String,
        required: true,
    },
});
</script>
