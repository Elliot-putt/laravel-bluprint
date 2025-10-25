<template>
    <div v-if="message" class="text-sm text-red-600">
        {{ message }}
    </div>
</template>

<script setup>
defineProps({
    message: {
        type: String,
        default: null,
    },
});
</script>
