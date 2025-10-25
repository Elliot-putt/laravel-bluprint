<template>
  <div class="mt-auto">
    <div v-if="isCurrentlyGitHubPR" class="w-full">
      <button
        @click="$emit('update-pr')"
        :disabled="!isGenerated || isSubmitting"
        class="w-full px-4 py-3 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 text-white shadow-sm font-medium transition-all disabled:opacity-50 disabled:cursor-not-allowed"
      >
        <span v-if="isSubmitting">Updating...</span>
        <span v-else>Update GitHub Pull Request (PR already exists)</span>
      </button>
    </div>
    <div v-else class="flex items-center gap-3">
      <button
        @click="$emit('create-pr')"
        :disabled="!isGenerated || isSubmitting"
        class="flex-1 px-4 py-3 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 text-white shadow-sm font-medium transition-all disabled:opacity-50 disabled:cursor-not-allowed"
      >
        <span v-if="isSubmitting">Creating...</span>
        <span v-else>Create Pull Request</span>
      </button>
      
      <!-- Draft/Ready Switch -->
      <div class="flex items-center gap-2">
        <label class="text-sm text-zinc-300">Draft</label>
        <button
          @click="toggleDraft"
          :class="[
            'relative inline-flex h-6 w-11 items-center rounded-full transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2',
            localIsDraft ? 'bg-zinc-600' : 'bg-purple-600'
          ]"
          :disabled="isSubmitting"
        >
          <span
            :class="[
              'pointer-events-none relative inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out',
              localIsDraft ? 'translate-x-0' : 'translate-x-5'
            ]"
          />
        </button>
        <label class="text-sm text-zinc-300">Ready</label>
      </div>
      
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, watch, onMounted } from 'vue';

const props = defineProps({
  isCurrentlyGitHubPR: Boolean,
  isGenerated: Boolean,
  isSubmitting: {
    type: Boolean,
    default: false
  },
  isDraft: {
    type: Boolean,
    default: false
  }
});

const emit = defineEmits<{ 
  (e: 'create-pr'): void; 
  (e: 'update-pr'): void;
  (e: 'update:draft', value: boolean): void;
}>();

// Local state for the toggle - default to false (ready, not draft)
const localIsDraft = ref(props.isDraft || false);

// Watch for prop changes and update local state
watch(() => props.isDraft, (newValue) => {
  localIsDraft.value = newValue || false;
}, { immediate: true });

const toggleDraft = () => {
  if (!props.isSubmitting) {
    localIsDraft.value = !localIsDraft.value;
    // localIsDraft = true means Draft, localIsDraft = false means Ready
    emit('update:draft', localIsDraft.value);
  }
};
</script> 