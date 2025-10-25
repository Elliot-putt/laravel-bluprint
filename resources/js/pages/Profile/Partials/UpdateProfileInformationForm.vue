
<template>
    <section>
        <header>
            <h2 class="text-lg font-medium text-gray-900">
                Profile Information
            </h2>

            <p class="mt-1 text-sm text-gray-600">
                Update your account's profile information and email address.
            </p>
        </header>

        <form
            @submit.prevent="form.patch(route('profile.update'))"
            class="mt-6 space-y-6"
        >
            <div>
                <InputLabel for="name" value="Name" />

                <TextInput
                    id="name"
                    type="text"
                    class="mt-1 block w-full"
                    v-model="form.name"
                    required
                    autofocus
                    autocomplete="name"
                />

                <InputError class="mt-2" :message="form.errors.name" />
            </div>

            <div>
                <InputLabel for="email" value="Email" />

                <TextInput
                    id="email"
                    type="email"
                    class="mt-1 block w-full"
                    v-model="form.email"
                    required
                    autocomplete="username"
                />

                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div v-if="mustVerifyEmail && user.email_verified_at === null">
                <p class="mt-2 text-sm text-gray-800">
                    Your email address is unverified.
                    <Link
                        :href="route('verification.send')"
                        method="post"
                        as="button"
                        class="rounded-md text-sm text-gray-600 underline hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                    >
                        Click here to re-send the verification email.
                    </Link>
                </p>

                <div
                    v-show="status === 'verification-link-sent'"
                    class="mt-2 text-sm font-medium text-green-600"
                >
                    A new verification link has been sent to your email address.
                </div>
            </div>

            <header class="mt-10">
                <h2 class="text-lg font-medium text-gray-900">
                    Jira Configuration
                </h2>

                <p class="mt-1 text-sm text-gray-600">
                    Configure your Jira integration.
                </p>
            </header>

            <div>
                <InputLabel for="jira_connected_email" value="Jira Email" />

                <TextInput
                    id="jira_connected_email"
                    type="email"
                    class="mt-1 block w-full"
                    v-model="form.jira_connected_email"
                    autocomplete="email"
                    placeholder="Your Jira email address"
                />

                <InputError class="mt-2" :message="form.errors.jira_connected_email" />
            </div>

            <div>
                <InputLabel for="jira_domain" value="Jira Domain" />

                <TextInput
                    id="jira_domain"
                    type="text"
                    class="mt-1 block w-full"
                    v-model="form.jira_domain"
                    placeholder="your-domain.atlassian.net"
                />

                <InputError class="mt-2" :message="form.errors.jira_domain" />
            </div>

            <div>
                <InputLabel for="jira_token" value="Jira API Token" />

                <div class="flex items-center">
                    <TextInput
                        id="jira_token"
                        type="password"
                        class="mt-1 block w-full"
                        v-model="form.jira_token"
                        autocomplete="new-password"
                        placeholder="Your Jira API token"
                    />
                    <span v-if="hasJiraToken" class="ml-2 text-sm text-green-600">Token set</span>
                </div>

                <InputError class="mt-2" :message="form.errors.jira_token" />
            </div>

            <header class="mt-10">
                <h2 class="text-lg font-medium text-gray-900">
                    Jira Status Configuration
                </h2>

                <p class="mt-1 text-sm text-gray-600">
                    Configure the status names for automatic ticket transitions.
                </p>
            </header>

            <div>
                <InputLabel for="jira_in_progress_status" value="Jira In Progress Status" />

                <TextInput
                    id="jira_in_progress_status"
                    type="text"
                    class="mt-1 block w-full"
                    v-model="form.jira_in_progress_status"
                    placeholder="In Progress"
                />

                <InputError class="mt-2" :message="form.errors.jira_in_progress_status" />
                <p class="mt-1 text-sm text-gray-600">
                    The status name for tickets that are 'In Progress' (e.g., In Progress, Development, etc.)
                </p>
            </div>

            <div>
                <InputLabel for="jira_review_status" value="Jira Review Status" />

                <TextInput
                    id="jira_review_status"
                    type="text"
                    class="mt-1 block w-full"
                    v-model="form.jira_review_status"
                    placeholder="Review"
                />

                <InputError class="mt-2" :message="form.errors.jira_review_status" />
                <p class="mt-1 text-sm text-gray-600">
                    The status name for tickets that are 'In Review' (e.g., In Review, Code Review, etc.)
                </p>
            </div>

            <div class="flex items-center gap-4">
                <PrimaryButton :disabled="form.processing">Save</PrimaryButton>

                <Transition
                    enter-active-class="transition ease-in-out"
                    enter-from-class="opacity-0"
                    leave-active-class="transition ease-in-out"
                    leave-to-class="opacity-0"
                >
                    <p
                        v-if="form.recentlySuccessful"
                        class="text-sm text-gray-600"
                    >
                        Saved.
                    </p>
                </Transition>
            </div>
        </form>
    </section>
</template>

<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const user = usePage().props.auth.user;

const form = useForm({
    name: user.name,
    email: user.email,
    jira_token: user.jira_token || '',
    jira_domain: user.jira_domain || '',
    jira_connected_email: user.jira_connected_email || '',
    jira_in_progress_status: user.jira_in_progress_status || 'In Progress',
    jira_review_status: user.jira_review_status || 'Review',
});

const hasJiraToken = computed(() => !!user.jira_token);
</script>
