<template>
    <div>
        <!-- Upload Media Link -->
        <button
            @click="openModal"
            class="text-purple-400 hover:text-purple-300 flex items-center gap-1 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
            :disabled="disabled"
        >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
            </svg>
            <span>Add media</span>
        </button>

        <!-- Media Modal -->
        <div v-if="isModalOpen" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <!-- Background overlay -->
                <div class="fixed inset-0 bg-zinc-900/75 backdrop-blur-sm transition-opacity" aria-hidden="true" @click="closeModal"></div>


                <!-- Modal panel -->
                <div class="inline-block align-bottom bg-zinc-800 border border-zinc-700 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full relative">
                    <!-- Upload Loading Overlay (inside modal) -->
                    <div v-if="isUploading" class="absolute inset-0 z-10 flex items-center justify-center bg-zinc-800/90 backdrop-blur-sm">
                        <div class="text-center p-6">
                            <svg class="animate-spin h-12 w-12 text-purple-500 mx-auto mb-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <h3 class="text-xl font-medium text-white mb-2">Uploading Media</h3>
                            <p class="text-zinc-300">Please wait while your media is being uploaded...</p>
                        </div>
                    </div>
                    <div class="bg-zinc-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mt-3 text-center sm:mt-0 sm:text-left w-full">
                                <h3 class="text-lg leading-6 font-medium text-white" id="modal-title">
                                    Media Library
                                </h3>

                                <!-- Upload Section -->
                                <div class="mt-4 border-b border-zinc-700 pb-4">
                                    <div class="flex items-center justify-between">
                                        <h4 class="text-sm font-medium text-zinc-300">Upload New Media</h4>
                                        <div v-if="isUploading" class="text-xs text-purple-400">Uploading...</div>
                                    </div>

                                    <div class="mt-2">
                                        <div
                                            class="border-2 border-dashed border-zinc-600 rounded-lg p-6 text-center hover:border-purple-500 transition-colors cursor-pointer"
                                            @click="triggerFileInput"
                                            @dragover.prevent="isDragging = true"
                                            @dragleave.prevent="isDragging = false"
                                            @drop.prevent="handleFileDrop"
                                            :class="{ 'border-purple-500 bg-purple-900/20': isDragging }"
                                        >
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 mx-auto text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                            </svg>
                                            <p class="mt-2 text-sm text-zinc-400">
                                                Drag and drop files here, or click to select files
                                            </p>
                                            <input
                                                type="file"
                                                ref="fileInput"
                                                class="hidden"
                                                @change="handleFileChange"
                                                accept="image/*,video/*"
                                            />
                                        </div>
                                    </div>
                                </div>

                                <!-- Media Gallery -->
                                <div class="mt-4">
                                    <h4 class="text-sm font-medium text-zinc-300 mb-2">Your Media</h4>

                                    <!-- Loading State -->
                                    <div v-if="isLoading" class="flex justify-center items-center py-8">
                                        <svg class="animate-spin h-8 w-8 text-purple-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                    </div>

                                    <!-- Empty State -->
                                    <div v-else-if="!mediaItems.length" class="text-center py-8 text-zinc-400">
                                        <p>No media found. Upload some files to get started.</p>
                                    </div>

                                    <!-- Media Grid -->
                                    <div v-else class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4 mt-2">
                                        <div
                                            v-for="item in mediaItems"
                                            :key="item.id"
                                            class="relative group border border-zinc-700 rounded-lg overflow-hidden bg-zinc-900/50"
                                        >
                                            <!-- Image Preview -->
                                            <div v-if="item.is_image" class="aspect-square">
                                                <img
                                                    :src="item.thumbnail_url || item.url"
                                                    :alt="item.name || 'Image'"
                                                    class="w-full h-full object-cover"
                                                    @error="($event.target as HTMLImageElement).src = item.url"
                                                />
                                            </div>

                                            <!-- Video Preview -->
                                            <div v-else-if="item.is_video" class="aspect-square relative">
                                                <img
                                                    v-if="item.thumbnail_url"
                                                    :src="item.thumbnail_url"
                                                    :alt="item.name || 'Video'"
                                                    class="w-full h-full object-cover"
                                                />
                                                <div v-else class="w-full h-full flex items-center justify-center bg-zinc-800">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                    </svg>
                                                </div>
                                                <!-- Play button overlay for videos -->
                                                <div class="absolute inset-0 flex items-center justify-center">
                                                    <div class="bg-black/50 rounded-full p-2">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-white" fill="currentColor" viewBox="0 0 24 24">
                                                            <path d="M8 5v14l11-7z"/>
                                                        </svg>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- File Preview -->
                                            <div v-else class="aspect-square flex items-center justify-center bg-zinc-800">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                </svg>
                                            </div>

                                            <!-- Overlay with Actions -->
                                            <div class="absolute inset-0 bg-zinc-900/75 opacity-0 group-hover:opacity-100 transition-opacity flex flex-col justify-between p-2">
                                                <div class="text-xs text-zinc-300 truncate">{{ item.display_name || item.name || 'Untitled' }}</div>

                                                <div class="flex justify-between">
                                                    <button
                                                        @click="insertMedia(item)"
                                                        class="text-xs bg-purple-600 hover:bg-purple-700 text-white px-2 py-1 rounded"
                                                    >
                                                        Insert
                                                    </button>

                                                    <button
                                                        @click="copyMediaLink(item)"
                                                        class="text-xs bg-zinc-700 hover:bg-zinc-600 text-white px-2 py-1 rounded"
                                                    >
                                                        Copy Link
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Pagination -->
                                    <div v-if="pagination.total > pagination.per_page" class="flex justify-center mt-4">
                                        <nav class="flex items-center">
                                            <button
                                                @click="changePage(pagination.current_page - 1)"
                                                :disabled="pagination.current_page === 1"
                                                class="px-2 py-1 rounded-l border border-zinc-700 bg-zinc-800 text-zinc-300 disabled:opacity-50 disabled:cursor-not-allowed"
                                            >
                                                Previous
                                            </button>

                                            <div class="px-3 py-1 border-t border-b border-zinc-700 bg-zinc-800 text-zinc-300">
                                                {{ pagination.current_page }} of {{ pagination.last_page }}
                                            </div>

                                            <button
                                                @click="changePage(pagination.current_page + 1)"
                                                :disabled="pagination.current_page === pagination.last_page"
                                                class="px-2 py-1 rounded-r border border-zinc-700 bg-zinc-800 text-zinc-300 disabled:opacity-50 disabled:cursor-not-allowed"
                                            >
                                                Next
                                            </button>
                                        </nav>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Modal Footer -->
                    <div class="bg-zinc-900 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button
                            type="button"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-zinc-700 shadow-sm px-4 py-2 bg-zinc-800 text-base font-medium text-white hover:bg-zinc-700 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
                            @click="closeModal"
                        >
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';

const props = defineProps({
    disabled: {
        type: Boolean,
        default: false
    }
});

const emit = defineEmits(['insert-media']);

// State
const isModalOpen = ref(false);
const isLoading = ref(false);
const isUploading = ref(false);
const isDragging = ref(false);
const mediaItems = ref<App.Data.MediaData[]>([]);
const fileInput = ref<HTMLInputElement | null>(null);
const pagination = ref({
    current_page: 1,
    per_page: 12,
    total: 0,
    last_page: 1
});

// Methods
const openModal = () => {
    isModalOpen.value = true;
    loadMedia();
};

const closeModal = () => {
    isModalOpen.value = false;
};

const triggerFileInput = () => {
    fileInput.value.click();
};

const handleFileChange = (event) => {
    const file = event.target.files[0];
    if (file) {
        uploadFile(file);
    }
};

const handleFileDrop = (event) => {
    isDragging.value = false;
    const file = event.dataTransfer.files[0];
    if (file) {
        uploadFile(file);
    }
};

const uploadFile = async (file) => {
    if (!file) return;

    // Check if file is an image or video
    if (!file.type.startsWith('image/') && !file.type.startsWith('video/')) {
        alert('Please upload an image or video file.');
        return;
    }

    isUploading.value = true;

    const formData = new FormData();
    formData.append('file', file);

    try {
        const response = await axios.post(route('media.store'), formData, {
            headers: {
                'Content-Type': 'multipart/form-data'
            }
        });

        const newMedia: App.Data.MediaData = response.data;

        // Add the new media to the beginning of the list
        mediaItems.value.unshift(newMedia);

        if (pagination.value.total !== undefined) {
            pagination.value.total += 1;
        }

    } catch (error) {
        console.error('Error uploading file:', error);
        alert('Failed to upload file. File size is too large max 100mb.');
    } finally {
        isUploading.value = false;
    }
};

const loadMedia = async (page = 1) => {
    isLoading.value = true;

    try {
        const response = await axios.get(route('media.index'), {
            params: { page, per_page: pagination.value.per_page }
        });

        const { data, pagination: paginationData } = response.data;

        // Set media items directly (they're already typed properly)
        mediaItems.value = data;

        // Update pagination
        pagination.value = paginationData;
    } catch (error) {
        console.error('Error loading media:', error);
    } finally {
        isLoading.value = false;
    }
};

const changePage = (page) => {
    if (page < 1 || page > pagination.value.last_page) return;
    loadMedia(page);
};



const insertMedia = (media: App.Data.MediaData) => {
    emit('insert-media', media.markdown);
    closeModal();
};

const copyMediaLink = (media: App.Data.MediaData) => {
    const markdown = media.markdown;
    navigator.clipboard.writeText(markdown)
        .then(() => {
            alert('Markdown copied to clipboard!');
        })
        .catch(err => {
            console.error('Failed to copy text: ', err);
        });
};

// Initialize
onMounted(() => {
    // We'll load media when the modal is opened
});
</script>
