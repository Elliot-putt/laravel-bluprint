<template>
  <div>
    <label class="block text-sm font-medium text-zinc-300 mb-1">Default Labels</label>
    <div class="space-y-2">
      <!-- Input for adding new labels -->
      <div class="flex gap-2">
        <input
          v-model="newLabel"
          @keyup.enter="addLabel"
          type="text"
          placeholder="Type label name and press Enter"
          class="flex-1 px-4 py-2 border border-zinc-600 shadow-sm focus:ring-purple-500 focus:border-purple-500 bg-zinc-700/70 text-white transition-shadow hover:bg-zinc-600/70"
        />
        <button
          @click="addLabel"
          type="button"
          class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white shadow-sm font-medium transition-all"
        >
          Add
        </button>
      </div>
      
      <!-- Display selected labels -->
      <div v-if="selectedLabels.length > 0" class="flex flex-wrap gap-2">
        <div
          v-for="(label, index) in selectedLabels"
          :key="index"
          class="px-3 py-1 rounded-full text-white text-xs font-semibold flex items-center gap-1.5 shadow-sm bg-purple-900/30 border border-purple-500"
        >
          <span class="w-3 h-3 rounded-full inline-block" :style="{ backgroundColor: getRandomColor(label) }"></span>
          <span>{{ label }}</span>
          <button @click="removeLabel(index)" class="hover:bg-black/20 rounded-full p-0.5">
            <svg class="h-3 w-3" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
            </svg>
          </button>
        </div>
      </div>
      
      <p class="text-zinc-400 text-xs">These labels will be automatically selected when using this template.</p>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, watch, PropType } from 'vue';

const props = defineProps({
  modelValue: {
    type: Array as PropType<string[]>,
    default: () => []
  }
});

const emit = defineEmits<{
  (e: 'update:modelValue', value: string[]): void;
}>();

const newLabel = ref('');
const selectedLabels = ref<string[]>([...props.modelValue]);

// Watch for external changes to modelValue
watch(() => props.modelValue, (newValue) => {
  selectedLabels.value = [...(newValue || [])];
}, { immediate: true });

const addLabel = () => {
  const label = newLabel.value.trim();
  if (label && !selectedLabels.value.includes(label)) {
    const updatedLabels = [...selectedLabels.value, label];
    selectedLabels.value = updatedLabels;
    emit('update:modelValue', updatedLabels);
    newLabel.value = '';
  }
};

const removeLabel = (index: number) => {
  const updatedLabels = selectedLabels.value.filter((_, i) => i !== index);
  selectedLabels.value = updatedLabels;
  emit('update:modelValue', updatedLabels);
};

// Generate consistent random colors for labels
const getRandomColor = (label: string): string => {
  // Simple hash function to generate consistent colors
  let hash = 0;
  for (let i = 0; i < label.length; i++) {
    const char = label.charCodeAt(i);
    hash = ((hash << 5) - hash) + char;
    hash = hash & hash; // Convert to 32bit integer
  }
  
  // Generate RGB values based on hash
  const r = Math.abs(hash) % 256;
  const g = Math.abs(hash >> 8) % 256;
  const b = Math.abs(hash >> 16) % 256;
  
  return `rgb(${r}, ${g}, ${b})`;
};
</script> 