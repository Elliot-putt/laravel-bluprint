<template>
  <div class="flex justify-between items-center">
    <h2 class="text-xl font-semibold leading-tight text-white">{{ title }}</h2>
    <button
      @click="$emit('create-pr')"
      class="px-4 py-2 bg-zinc-700 hover:bg-zinc-600 text-white shadow-sm flex items-center gap-2 transition-all cursor-pointer"
    >
      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v2H7a1 1 0 100 2h2v2a1 1 0 102 0v-2h2a1 1 0 100-2h-2V7z" clip-rule="evenodd" />
      </svg>
      Create PR
    </button>
  </div>
</template>
<script setup lang="ts">
const props = defineProps<{ title?: string }>();
defineEmits<{ (e: 'create-pr'): void }>();
</script> 