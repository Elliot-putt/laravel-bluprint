<template>
  <div class="mb-1">
    <label v-if="label" :for="id" class="block text-sm font-medium text-zinc-300 mb-1">{{ label }}</label>
    <div class="relative" ref="dropdownContainer">
      <!-- Searchable Input -->
      <div class="relative">
        <input
          ref="searchInput"
          type="text"
          v-model="search"
          @focus="showDropdown = true"
          @input="handleSearchInput"
          @keydown="handleKeydown"
          :placeholder="placeholder"
          :disabled="disabled"
          class="w-full px-4 py-2 border border-zinc-600 shadow-sm focus:ring-purple-500 focus:border-purple-500 bg-zinc-700/70 text-white pr-10 transition-shadow hover:bg-zinc-600/70 select-custom disabled:opacity-50 disabled:cursor-not-allowed"
        />
        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-purple-400">
          <svg v-if="!isLoadingMore" class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
          </svg>
          <svg v-if="isLoadingMore" class="h-4 w-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
        </div>
      </div>

      <!-- Dropdown -->
      <div
        v-show="showDropdown && (filteredOptions.length > 0 || shouldShowCustomOption || isLoadingMore)"
        ref="dropdownMenu"
        @scroll="handleScroll"
        class="absolute z-50 mt-1 w-full border border-zinc-600 rounded-sm bg-zinc-800 shadow-lg max-h-60 overflow-y-auto"
      >
        <!-- Custom option -->
        <div
          v-if="allowCustom && shouldShowCustomOption"
          @click="selectCustomOption"
          class="p-3 cursor-pointer hover:bg-zinc-700 text-white border-b border-zinc-600 bg-zinc-750"
        >
          <div class="flex items-center gap-2">
            <svg class="h-4 w-4 text-purple-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
            </svg>
            <span class="text-purple-400">Use custom value:</span>
            <span class="font-mono">{{ search }}</span>
          </div>
        </div>

        <!-- Options -->
        <div
          v-for="option in filteredOptions"
          :key="getOptionKey(option)"
          @click="toggleOption(option)"
          class="p-3 cursor-pointer hover:bg-zinc-700 text-white flex flex-col justify-between items-start"
        >
          <div class="flex items-center gap-2 min-w-0">
            <slot name="option-prefix" :option="option" />
            <span class="truncate">{{ getOptionLabel(option) }}</span>
          </div>
          <div v-if="optionDescription && optionDescription(option)" class="text-xs text-purple-400 mt-1 truncate w-full">
            {{ optionDescription(option) }}
          </div>
          <span v-if="isSelected(option)" class="text-purple-400 absolute right-3 top-3">
            <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
            </svg>
          </span>
        </div>
        
        <!-- Loading more indicator -->
        <div v-if="isLoadingMore" class="p-3 text-center text-zinc-400">
          <div class="flex items-center justify-center gap-2">
            <svg class="h-4 w-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span>Loading more...</span>
          </div>
        </div>
        
        <!-- No results message -->
        <div v-if="filteredOptions.length === 0 && !(allowCustom && shouldShowCustomOption) && !isLoadingMore" class="p-3 text-zinc-400 text-center">
          No options found
        </div>
        
        <!-- Load more button for end of results -->
        <div v-if="canLoadMore && !isLoadingMore && filteredOptions.length > 0" class="p-2 border-t border-zinc-600">
          <button
            @click="loadMore"
            class="w-full text-sm text-purple-400 hover:text-purple-300 hover:bg-zinc-700 px-3 py-2 rounded transition-colors"
          >
            Load more results...
          </button>
        </div>
      </div>
    </div>
    <div v-if="optionDescription && selectedOptionDetails" class="mt-1 text-xs text-zinc-400">
      {{ optionDescription(selectedOptionDetails) }}
    </div>
    <div v-if="description" class="mt-1 text-xs text-purple-400">{{ description }}</div>
    <div v-if="multiple && selectedOptionsArray.length" class="flex flex-wrap gap-2 mt-3">
      <div
        v-for="option in selectedOptionsArray"
        :key="getOptionKey(option)"
        class="px-3 py-1 rounded-full text-white text-xs font-semibold flex items-center gap-1.5 shadow-sm bg-purple-900/30 border border-purple-500"
      >
        <span v-if="$slots['option-prefix']">
          <slot name="option-prefix" :option="option" />
        </span>
        <span>{{ getOptionLabel(option) }}</span>
        <button @click.stop="removeOption(option)" class="hover:bg-black/20 rounded-full p-0.5">
          <svg class="h-3 w-3" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
          </svg>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, watch, onMounted, onUnmounted, ComputedRef, nextTick } from 'vue';

const props = defineProps({
  options: {
    type: Array,
    default: () => []
  },
  modelValue: {
    type: [String, Number, Array],
    default: ''
  },
  label: String,
  placeholder: {
    type: String,
    default: 'Select...'
  },
  multiple: {
    type: Boolean,
    default: false
  },
  allowCustom: {
    type: Boolean,
    default: false
  },
  optionIcon: Function, // (option) => VNode | String
  optionDescription: Function, // (option) => String
  disabled: {
    type: Boolean,
    default: false
  },
  getOptionLabel: {
    type: Function,
    default: (option: any) => option.label || option.name || option.key || option.id
  },
  getOptionKey: {
    type: Function,
    default: (option: any) => option.id || option.key || option.name
  },
  id: String,
  description: {
    type: String,
    default: ''
  },
  // Pagination props
  canLoadMore: {
    type: Boolean,
    default: false
  },
  isLoadingMore: {
    type: Boolean,
    default: false
  },
  searchDelayMs: {
    type: Number,
    default: 300
  },
});

const emit = defineEmits(['update:modelValue', 'addCustom', 'loadMore', 'search']);

const search = ref('');
const showDropdown = ref(false);
const dropdownContainer = ref(null);
const dropdownMenu = ref(null);
const searchInput = ref(null);
const searchTimeout = ref<number | null>(null);
const lastSearchTerm = ref('');
const autoLoadTimeout = ref<number | null>(null);
const maxAutoLoadAttempts = ref(20); // Prevent infinite loading
const currentAutoLoadAttempts = ref(0);

const isSelected = (option: any) => {
  if (props.multiple) {
    return Array.isArray(props.modelValue) && props.modelValue.includes(props.getOptionKey(option));
  }
  return props.getOptionKey(option) === props.modelValue;
};

const selectedOptions: ComputedRef<any[]> = computed(() => {
  if (!props.multiple) return [];
  if (!Array.isArray(props.modelValue)) return [];
  return props.options.filter(option => Array.isArray(props.modelValue) && props.modelValue.includes(props.getOptionKey(option)));
});

const selectedOptionsArray = computed(() => {
  if (!props.multiple) return [];
  if (!Array.isArray(props.modelValue)) return [];
  return props.options.filter(option => Array.isArray(props.modelValue) && props.modelValue.includes(props.getOptionKey(option)));
});

const selectedOptionDetails = computed(() => {
  if (props.multiple) return null;
  return props.options.find(option => props.getOptionKey(option) === props.modelValue);
});

const filteredOptions = computed(() => {
  if (!search.value) return props.options;
  const searchTerm = search.value.toLowerCase();
  return props.options.filter(option =>
    props.getOptionLabel(option).toLowerCase().includes(searchTerm)
  );
});

const shouldShowCustomOption = computed(() => {
  if (!props.allowCustom || !search.value) return false;
  return !props.options.some(option =>
    props.getOptionLabel(option).toLowerCase() === search.value.toLowerCase()
  );
});

const toggleOption = (option: any) => {
  if (props.disabled) return;
  const key = props.getOptionKey(option);
  if (props.multiple) {
    let newValue = Array.isArray(props.modelValue) ? [...props.modelValue] : [];
    const idx = newValue.indexOf(key);
    if (idx === -1) {
      newValue.push(key);
    } else {
      newValue.splice(idx, 1);
    }
    emit('update:modelValue', newValue);
  } else {
    emit('update:modelValue', key);
    showDropdown.value = false;
    search.value = props.getOptionLabel(option);
  }
};

const removeOption = (option: any) => {
  if (props.disabled || !props.multiple) return;
  const key = props.getOptionKey(option);
  let newValue = Array.isArray(props.modelValue) ? [...props.modelValue] : [];
  newValue = newValue.filter((k: any) => k !== key);
  emit('update:modelValue', newValue);
};

const selectCustomOption = () => {
  if (props.disabled) return;
  if (props.multiple) {
    const currentValue = Array.isArray(props.modelValue) ? props.modelValue : [];
    emit('update:modelValue', [...currentValue, search.value]);
  } else {
    emit('update:modelValue', search.value);
    showDropdown.value = false;
  }
  emit('addCustom', search.value);
};

const handleKeydown = (event: KeyboardEvent) => {
  if (event.key === 'Enter') {
    event.preventDefault();
    
    // If there's only one filtered option and it's not the custom option, select it
    const availableOptions = filteredOptions.value.filter(option => 
      !props.allowCustom || !shouldShowCustomOption.value || 
      props.getOptionLabel(option).toLowerCase() !== search.value.toLowerCase()
    );
    
    if (availableOptions.length === 1) {
      toggleOption(availableOptions[0]);
    } else if (props.allowCustom && shouldShowCustomOption.value) {
      // If there's a custom option available, select it
      selectCustomOption();
    }
  }
};

const handleSearchInput = () => {
  if (!props.multiple && !search.value && props.modelValue) {
    emit('update:modelValue', '');
  }
  
  // Debounce search to avoid too many requests
  if (searchTimeout.value) {
    clearTimeout(searchTimeout.value);
  }
  
  searchTimeout.value = setTimeout(() => {
    if (search.value !== lastSearchTerm.value) {
      lastSearchTerm.value = search.value;
      // Reset auto-load attempts for new search
      currentAutoLoadAttempts.value = 0;
      emit('search', search.value);
      
      // Auto-load more pages until we find results or no more pages
      nextTick(() => {
        autoLoadUntilResults();
      });
    }
  }, props.searchDelayMs);
};

const autoLoadUntilResults = () => {
  // Clear any existing timeout
  if (autoLoadTimeout.value) {
    clearTimeout(autoLoadTimeout.value);
  }
  
  // Only auto-load if we're searching, have no results, can load more, not already loading, and haven't exceeded max attempts
  if (search.value && 
      filteredOptions.value.length === 0 && 
      props.canLoadMore && 
      !props.isLoadingMore && 
      currentAutoLoadAttempts.value < maxAutoLoadAttempts.value) {
    
    currentAutoLoadAttempts.value++;
    
    // Debounce the load more call to prevent too aggressive loading
    autoLoadTimeout.value = setTimeout(() => {
      loadMore();
    }, 200);
  }
};

const handleScroll = (event: Event) => {
  const target = event.target as HTMLElement;
  if (!target) return;
  
  const scrollTop = target.scrollTop;
  const scrollHeight = target.scrollHeight;
  const clientHeight = target.clientHeight;
  
  // Check if we're near the bottom (within 10px)
  if (scrollTop + clientHeight >= scrollHeight - 10) {
    if (props.canLoadMore && !props.isLoadingMore) {
      loadMore();
    }
  }
};

const loadMore = () => {
  if (props.canLoadMore && !props.isLoadingMore) {
    emit('loadMore', search.value);
  }
};

const handleClickOutside = (event: MouseEvent) => {
  if (dropdownContainer.value && !(dropdownContainer.value as HTMLElement).contains(event.target as Node)) {
    showDropdown.value = false;
  }
};

// Public method to update search field (for external use)
const updateSearchField = (value: string) => {
  search.value = value;
};

// Expose the method for parent components
defineExpose({
  updateSearchField
});

watch(() => props.modelValue, (newValue) => {
  if (!props.multiple && newValue) {
    const option = props.options.find(option => props.getOptionKey(option) === newValue);
    if (option) {
      search.value = props.getOptionLabel(option);
    } else if (typeof newValue === 'string') {
      search.value = newValue;
    }
  } else if (!props.multiple && !newValue) {
    search.value = '';
  }
});

// Watch for loading state changes to continue auto-loading if needed
watch(() => props.isLoadingMore, (isLoading, wasLoading) => {
  // When loading finishes, check if we need to load more
  if (wasLoading && !isLoading) {
    nextTick(() => {
      autoLoadUntilResults();
    });
  }
});

// Watch for new options to trigger auto-loading if needed
watch(() => props.options.length, () => {
  nextTick(() => {
    autoLoadUntilResults();
  });
});

onMounted(() => {
  document.addEventListener('click', handleClickOutside);
  if (!props.multiple && props.modelValue) {
    const option = props.options.find(option => props.getOptionKey(option) === props.modelValue);
    if (option) {
      search.value = props.getOptionLabel(option);
    }
  }
});

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside);
  if (searchTimeout.value) {
    clearTimeout(searchTimeout.value);
  }
  if (autoLoadTimeout.value) {
    clearTimeout(autoLoadTimeout.value);
  }
});
</script>
