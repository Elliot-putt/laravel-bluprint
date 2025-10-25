<template>
    <nav v-if="totalPages > 1" class="flex items-center justify-between border-t border-zinc-700/50 bg-zinc-800/30 px-4 py-3 sm:px-6">
        <!-- Mobile pagination -->
        <div class="flex flex-1 justify-between sm:hidden">
            <button
                v-if="hasPrevious"
                @click="goToPage(currentPage - 1)"
                :disabled="loading"
                class="relative inline-flex items-center rounded-md border border-zinc-600 bg-zinc-700 px-4 py-2 text-sm font-medium text-zinc-200 hover:bg-zinc-600 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
            >
                Previous
            </button>
            <span v-else class="relative inline-flex items-center rounded-md border border-zinc-600 bg-zinc-800 px-4 py-2 text-sm font-medium text-zinc-500">
        Previous
      </span>

            <button
                v-if="hasNext"
                @click="goToPage(currentPage + 1)"
                :disabled="loading"
                class="relative ml-3 inline-flex items-center rounded-md border border-zinc-600 bg-zinc-700 px-4 py-2 text-sm font-medium text-zinc-200 hover:bg-zinc-600 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
            >
                Next
            </button>
            <span v-else class="relative ml-3 inline-flex items-center rounded-md border border-zinc-600 bg-zinc-800 px-4 py-2 text-sm font-medium text-zinc-500">
        Next
      </span>
        </div>

        <!-- Desktop pagination -->
        <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
            <div>
                <p class="text-sm text-zinc-400">
                    Showing
                    <span class="font-medium text-zinc-200">{{ from }}</span>
                    to
                    <span class="font-medium text-zinc-200">{{ to }}</span>
                    of
                    <span class="font-medium text-zinc-200">{{ total }}</span>
                    results
                </p>
            </div>

            <div class="flex items-center gap-2">
                <!-- Per page selector -->
                <div class="flex items-center gap-2 mr-4">
                    <label class="text-sm text-zinc-400">Show:</label>
                    <select
                        :value="perPage"
                        @change="changePerPage($event.target.value)"
                        :disabled="loading"
                        class="bg-zinc-700 border border-zinc-600 text-zinc-200 text-sm rounded-sm px-2 py-1 focus:ring-purple-500 focus:border-purple-500 disabled:opacity-50"
                    >
                        <option v-for="option in perPageOptions" :key="option" :value="option">
                            {{ option }}
                        </option>
                    </select>
                </div>

                <!-- Page navigation -->
                <nav class="isolate inline-flex -space-x-px rounded-md shadow-sm">
                    <!-- Previous button -->
                    <button
                        v-if="hasPrevious"
                        @click="goToPage(currentPage - 1)"
                        :disabled="loading"
                        class="relative inline-flex items-center rounded-l-md px-2 py-2 text-zinc-400 ring-1 ring-inset ring-zinc-600 hover:bg-zinc-700 hover:text-zinc-200 focus:z-20 focus:outline-offset-0 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                    >
                        <span class="sr-only">Previous</span>
                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M12.79 5.23a.75.75 0 01-.02 1.06L8.832 10l3.938 3.71a.75.75 0 11-1.04 1.08l-4.5-4.25a.75.75 0 010-1.08l4.5-4.25a.75.75 0 011.06.02z" clip-rule="evenodd" />
                        </svg>
                    </button>
                    <span v-else class="relative inline-flex items-center rounded-l-md px-2 py-2 text-zinc-600 ring-1 ring-inset ring-zinc-600 bg-zinc-800">
            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M12.79 5.23a.75.75 0 01-.02 1.06L8.832 10l3.938 3.71a.75.75 0 11-1.04 1.08l-4.5-4.25a.75.75 0 010-1.08l4.5-4.25a.75.75 0 011.06.02z" clip-rule="evenodd" />
            </svg>
          </span>

                    <!-- Page numbers -->
                    <template v-for="page in visiblePages" :key="`page-${page}`">
                        <button
                            v-if="page !== '...'"
                            @click="goToPage(page)"
                            :disabled="loading"
                            class="relative inline-flex items-center px-4 py-2 text-sm font-semibold ring-1 ring-inset ring-zinc-600 focus:z-20 focus:outline-offset-0 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                            :class="page === currentPage
                ? 'z-10 bg-purple-600 text-white focus:z-20 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-purple-600'
                : 'text-zinc-300 hover:bg-zinc-700 hover:text-zinc-200'"
                        >
                            {{ page }}
                        </button>
                        <span v-else class="relative inline-flex items-center px-4 py-2 text-sm font-semibold text-zinc-500 ring-1 ring-inset ring-zinc-600">
              ...
            </span>
                    </template>

                    <!-- Next button -->
                    <button
                        v-if="hasNext"
                        @click="goToPage(currentPage + 1)"
                        :disabled="loading"
                        class="relative inline-flex items-center rounded-r-md px-2 py-2 text-zinc-400 ring-1 ring-inset ring-zinc-600 hover:bg-zinc-700 hover:text-zinc-200 focus:z-20 focus:outline-offset-0 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                    >
                        <span class="sr-only">Next</span>
                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd" />
                        </svg>
                    </button>
                    <span v-else class="relative inline-flex items-center rounded-r-md px-2 py-2 text-zinc-600 ring-1 ring-inset ring-zinc-600 bg-zinc-800">
            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd" />
            </svg>
          </span>
                </nav>
            </div>
        </div>

        <!-- Loading overlay -->
        <div v-if="loading" class="absolute inset-0 bg-zinc-800/50 rounded-md flex items-center justify-center">
            <div class="flex items-center gap-2 text-zinc-300">
                <div class="w-4 h-4 border-2 border-zinc-600 border-t-purple-500 rounded-full animate-spin"></div>
                <span class="text-sm">Loading...</span>
            </div>
        </div>
    </nav>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
    currentPage: {
        type: Number,
        required: true
    },
    totalPages: {
        type: Number,
        required: true
    },
    total: {
        type: Number,
        required: true
    },
    from: {
        type: Number,
        required: true
    },
    to: {
        type: Number,
        required: true
    },
    perPage: {
        type: Number,
        default: 25
    },
    perPageOptions: {
        type: Array,
        default: () => [10, 25, 50, 100]
    },
    loading: {
        type: Boolean,
        default: false
    }
})

const emit = defineEmits(['page-changed', 'per-page-changed'])

const hasPrevious = computed(() => props.currentPage > 1)
const hasNext = computed(() => props.currentPage < props.totalPages)

const visiblePages = computed(() => {
    const pages = []
    const current = props.currentPage
    const total = props.totalPages

    if (total <= 7) {
        for (let i = 1; i <= total; i++) {
            pages.push(i)
        }
    } else {
        if (current <= 4) {
            for (let i = 1; i <= 5; i++) {
                pages.push(i)
            }
            pages.push('...')
            pages.push(total)
        } else if (current >= total - 3) {
            pages.push(1)
            pages.push('...')
            for (let i = total - 4; i <= total; i++) {
                pages.push(i)
            }
        } else {
            pages.push(1)
            pages.push('...')
            for (let i = current - 1; i <= current + 1; i++) {
                pages.push(i)
            }
            pages.push('...')
            pages.push(total)
        }
    }

    return pages
})

const goToPage = (page) => {
    if (page !== props.currentPage && page >= 1 && page <= props.totalPages && !props.loading) {
        emit('page-changed', page)
    }
}

const changePerPage = (newPerPage) => {
    if (newPerPage !== props.perPage && !props.loading) {
        emit('per-page-changed', parseInt(newPerPage))
    }
}
</script>
