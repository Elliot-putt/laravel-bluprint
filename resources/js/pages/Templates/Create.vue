<template>
    <Head title="Create Template" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-semibold leading-tight text-white">Create Template</h2>
                <Link :href="route('templates.index')" class="px-4 py-2 bg-zinc-600 hover:bg-zinc-700 text-white shadow-sm font-medium transition-all">
                    Back to Templates
                </Link>
            </div>
        </template>

        <div class="py-6 h-100vh">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="bg-zinc-800/50 border border-zinc-700 overflow-hidden shadow-lg sm:rounded-lg h-full backdrop-blur-sm">
                    <div class="p-6 h-full flex flex-col">
                        <form @submit.prevent="submit" class="space-y-6">
                            <!-- Template Name -->
                            <div>
                                <label for="name" class="block text-sm font-medium text-zinc-300 mb-1">Template Name</label>
                                <input
                                    type="text"
                                    id="name"
                                    v-model="form.name"
                                    class="w-full px-4 py-2 border border-zinc-600 shadow-sm focus:ring-purple-500 focus:border-purple-500 bg-zinc-700/70 text-white transition-shadow hover:bg-zinc-600/70"
                                    placeholder="Enter template name"
                                />
                                <div v-if="form.errors.name" class="text-red-500 text-sm mt-1">{{ form.errors.name }}</div>
                            </div>

                            <!-- Title Template -->
                            <div>
                                <label for="title_template" class="block text-sm font-medium text-zinc-300 mb-1">Title Template</label>
                                <textarea
                                    id="title_template"
                                    v-model="form.title_template"
                                    rows="3"
                                    class="w-full px-4 py-2 border border-zinc-600 shadow-sm focus:ring-purple-500 focus:border-purple-500 bg-zinc-700/70 text-white transition-shadow hover:bg-zinc-600/70"
                                    placeholder="Enter title template instructions for AI"
                                ></textarea>
                                <div v-if="form.errors.title_template" class="text-red-500 text-sm mt-1">{{ form.errors.title_template }}</div>
                                <p class="text-zinc-400 text-xs mt-1">Instructions for the AI to generate pull request titles. You can use github flavouring.</p>
                            </div>

                            <!-- Body Template -->
                            <div>
                                <label for="body_template" class="block text-sm font-medium text-zinc-300 mb-1">Body Template</label>
                                <textarea
                                    id="body_template"
                                    v-model="form.body_template"
                                    rows="10"
                                    class="w-full px-4 py-2 border border-zinc-600 shadow-sm focus:ring-purple-500 focus:border-purple-500 bg-zinc-700/70 text-white transition-shadow hover:bg-zinc-600/70"
                                    placeholder="Enter body template instructions for AI"
                                ></textarea>
                                <div v-if="form.errors.body_template" class="text-red-500 text-sm mt-1">{{ form.errors.body_template }}</div>
                                <p class="text-zinc-400 text-xs mt-1">Instructions for the AI to generate pull request descriptions. You can use github flavouring.</p>
                            </div>

                            <!-- Default Labels -->
                            <div>
                                <CustomLabelSelector
                                    v-model="form.default_labels"
                                />
                                <div v-if="form.errors.default_labels" class="text-red-500 text-sm mt-1">{{ form.errors.default_labels }}</div>
                            </div>

                            <!-- Set as Default -->
                            <div class="flex items-center">
                                <input
                                    type="checkbox"
                                    id="is_default"
                                    v-model="form.is_default"
                                    class="h-4 w-4 text-purple-600 focus:ring-purple-500 border-zinc-600 bg-zinc-700"
                                />
                                <label for="is_default" class="ml-2 block text-sm text-zinc-300">Set as default template</label>
                            </div>

                            <!-- Submit Button -->
                            <div>
                                <button
                                    type="submit"
                                    :disabled="form.processing"
                                    class="w-full px-4 py-3 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 text-white shadow-sm font-medium transition-all disabled:opacity-50 disabled:cursor-not-allowed"
                                >
                                    Create Template
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import CustomLabelSelector from '@/Components/PR/CustomLabelSelector.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    title_template: '',
    body_template: '',
    default_labels: [],
    is_default: false,
});

const submit = () => {
    form.post(route('templates.store'), {
        onSuccess: () => {
            form.reset();
        },
    });
};
</script>
