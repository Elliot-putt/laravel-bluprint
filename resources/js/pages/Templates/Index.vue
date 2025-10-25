<template>
    <Head title="Templates" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-semibold leading-tight text-white">Pull request Templates</h2>
                <Link :href="route('templates.create')" class="px-4 py-2 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 text-white shadow-sm font-medium transition-all">
                    Create Template
                </Link>
            </div>
        </template>

        <div class="py-6 h-100vh">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="bg-zinc-800/50 border border-zinc-700 overflow-hidden shadow-lg sm:rounded-lg h-full backdrop-blur-sm">
                    <div class="p-6 h-full flex flex-col">
                        <!-- Templates List -->
                        <div v-if="templates.length > 0" class="space-y-4">
                            <div v-for="template in templates" :key="template.id" class="border border-zinc-700 p-4 rounded-md">
                                <div class="flex justify-between items-center mb-2">
                                    <h3 class="text-lg font-medium text-white">{{ template.name }}</h3>
                                    <div class="flex space-x-2">
                                        <span v-if="template.id === defaultTemplateId" class="px-3 py-1 bg-gradient-to-r from-green-500 to-green-600 text-white text-xs rounded-md shadow-sm">Default</span>
                                        <Link :href="route('templates.edit', template.id)" class="px-3 py-1 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 text-white text-xs rounded-md shadow-sm font-medium transition-all">Edit</Link>
                                        <button v-if="template.id !== defaultTemplateId" @click="setDefaultTemplate(template.id)" class="px-3 py-1 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 text-white text-xs rounded-md shadow-sm font-medium transition-all">Set as Default</button>
                                        <button @click="deleteTemplate(template.id)" class="px-3 py-1 bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white text-xs rounded-md shadow-sm font-medium transition-all">Delete</button>
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div class="border border-zinc-600 p-3 rounded">
                                        <h4 class="text-sm font-medium text-zinc-300 mb-1">Title Template</h4>
                                        <p class="text-white text-sm whitespace-pre-wrap">{{ template.title_template }}</p>
                                    </div>
                                    <div class="border border-zinc-600 p-3 rounded">
                                        <h4 class="text-sm font-medium text-zinc-300 mb-1">Body Template</h4>
                                        <p class="text-white text-sm whitespace-pre-wrap">{{ template.body_template }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Empty State -->
                        <div v-else class="flex flex-col items-center justify-center h-64">
                            <div class="text-zinc-400 mb-4">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </div>
                            <h3 class="text-xl font-medium text-white mb-2">No Templates Found</h3>
                            <p class="text-zinc-400 mb-4 text-center">Create your first template to customize how your pull request titles and descriptions are generated.</p>
                            <Link :href="route('templates.create')" class="px-4 py-2 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 text-white shadow-sm font-medium transition-all">
                                Create Template
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    templates: Array,
    defaultTemplateId: Number,
});

const setDefaultTemplate = (templateId) => {
    router.post(route('templates.set-default', templateId), {}, {
        preserveScroll: true,
        onSuccess: () => {
            // Success notification could be added here
        }
    });
};

const deleteTemplate = (templateId) => {
    if (confirm('Are you sure you want to delete this template?')) {
        router.delete(route('templates.destroy', templateId), {
            preserveScroll: true,
            onSuccess: () => {
                // Success notification could be added here
            }
        });
    }
};
</script>
