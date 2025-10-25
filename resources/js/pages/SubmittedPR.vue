<template>
    <Head title="Pull Request Created Successfully" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-semibold leading-tight text-white">Pull request Created for {{props.repositoryName}}</h2>
            </div>
        </template>

        <div class="py-6 h-[calc(100vh-8rem)] flex items-center justify-center">
            <div class="mx-auto max-w-lg px-4 sm:px-6 lg:px-8">
                <div class="bg-zinc-800/50 border border-zinc-700 overflow-hidden shadow-lg sm:rounded-lg backdrop-blur-sm">
                    <div class="p-8 flex flex-col items-center text-center">
                        <!-- Success Icon -->
                        <div class="h-20 w-20 rounded-full bg-gradient-to-r from-purple-600 to-pink-600 flex items-center justify-center mb-6 shadow-lg">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-white" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                            </svg>
                        </div>

                        <h1 class="text-2xl font-bold text-white mb-3">{{ prLink ? 'Pull Request Successfully Created!' : 'Oops we could not find your pull request!' }}</h1>

                        <p class="text-zinc-300 mb-6" v-if="prLink">
                            Your pull request has been submitted successfully. You can now view and track its progress on GitHub.
                        </p>

                        <!-- Jira Ticket Transition Status -->
                        <div v-if="jiraTransition" class="mb-6 p-4 rounded-lg border" :class="jiraTransition.success ? 'bg-green-900/20 border-green-700' : 'bg-red-900/20 border-red-700'">
                            <div class="flex items-center gap-2 mb-2">
                                <svg v-if="jiraTransition.success" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                                <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                                <span class="font-medium" :class="jiraTransition.success ? 'text-green-400' : 'text-red-400'">
                                    Jira Ticket {{ jiraTransition.success ? 'Updated' : 'Update Failed' }}
                                </span>
                            </div>
                            <p class="text-sm" :class="jiraTransition.success ? 'text-green-300' : 'text-red-300'">
                                {{ jiraTransition.message }}
                            </p>
                        </div>

                        <!-- Countdown Timer -->
                        <div class="mb-6 text-center">
                            <p class="text-zinc-400 text-sm mb-2">Redirecting to dashboard in</p>
                            <div class="text-2xl font-bold text-purple-400">{{ countdown }}s</div>
                        </div>

                        <!-- PR Link Button -->
                        <a
                            v-if="prLink"
                            :href="prLink"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="px-5 py-3 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 text-white shadow-md rounded-sm font-medium flex items-center gap-2 transform transition-all hover:translate-y-[-2px] hover:shadow-lg"
                        >
                            View Pull Request
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L12.586 11H5a1 1 0 110-2h7.586l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </a>

                        <!-- Return Link -->
                        <Link
                            :href="route('dashboard')"
                            class="mt-6 text-purple-400 hover:text-purple-300 transition-colors flex items-center gap-1.5 text-sm"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
                            </svg>
                            Return to Dashboard
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted } from 'vue';

const props = defineProps({
    prLink: {
        type: String,
    },
    repositoryName: {
        type: String,
        default: ''
    },
    jiraTransition: {
        type: Object,
        default: null
    }
});

const countdown = ref(60);
let timer = null;

onMounted(() => {
    timer = setInterval(() => {
        countdown.value--;
        
        if (countdown.value <= 0) {
            clearInterval(timer);
            router.visit(route('dashboard'));
        }
    }, 1000);
});

onUnmounted(() => {
    if (timer) {
        clearInterval(timer);
    }
});
</script>
