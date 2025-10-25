<script setup lang="ts">
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

import DeleteUser from '@/Components/DeleteUser.vue';
import HeadingSmall from '@/Components/HeadingSmall.vue';
import InputError from '@/Components/InputError.vue';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import AppLayout from '@/Layouts/AppLayout.vue';
import SettingsLayout from '@/Layouts/settings/Layout.vue';
import { type BreadcrumbItem, type SharedData, type User } from '@/types';

interface Props {
    mustVerifyEmail: boolean;
    status?: string;
}

defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Profile settings',
        href: '/settings/profile',
    },
];

const page = usePage<SharedData>();
const user = page.props.auth.user as User;

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

const submit = () => {
    form.patch(route('profile.update'), {
        preserveScroll: true,
    });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Profile settings" />

        <SettingsLayout>
            <div class="flex flex-col space-y-6">
                <HeadingSmall title="Profile information" description="Update your name and email address" />

                <form @submit.prevent="submit" class="space-y-6">
                    <div class="grid gap-2">
                        <Label for="name">Name</Label>
                        <Input id="name" class="mt-1 block w-full" v-model="form.name" required autocomplete="name" placeholder="Full name" />
                        <InputError class="mt-2" :message="form.errors.name" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="email">Email address</Label>
                        <Input
                            id="email"
                            type="email"
                            class="mt-1 block w-full"
                            v-model="form.email"
                            required
                            autocomplete="username"
                            placeholder="Email address"
                        />
                        <InputError class="mt-2" :message="form.errors.email" />
                    </div>

                    <div v-if="mustVerifyEmail && !user.email_verified_at">
                        <p class="-mt-4 text-sm text-muted-foreground">
                            Your email address is unverified.
                            <Link
                                :href="route('verification.send')"
                                method="post"
                                as="button"
                                class="text-foreground underline decoration-neutral-300 underline-offset-4 transition-colors duration-300 ease-out hover:decoration-current!"
                            >
                                Click here to resend the verification email.
                            </Link>
                        </p>

                        <div v-if="status === 'verification-link-sent'" class="mt-2 text-sm font-medium text-green-600">
                            A new verification link has been sent to your email address.
                        </div>
                    </div>

                    <HeadingSmall title="Jira Configuration" description="Configure your Jira integration" />

                    <div class="grid gap-2">
                        <Label for="jira_connected_email">Jira Email</Label>
                        <Input
                            id="jira_connected_email"
                            type="email"
                            class="mt-1 block w-full"
                            v-model="form.jira_connected_email"
                            autocomplete="email"
                            placeholder="Your Jira email address"
                        />
                        <InputError class="mt-2" :message="form.errors.jira_connected_email" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="jira_domain">Jira Domain</Label>
                        <Input
                            id="jira_domain"
                            type="text"
                            class="mt-1 block w-full"
                            v-model="form.jira_domain"
                            placeholder="your-domain.atlassian.net"
                        />
                        <InputError class="mt-2" :message="form.errors.jira_domain" />
                    </div>

                    

                    <HeadingSmall title="Jira Status Configuration" description="Configure the status names for automatic ticket transitions" />

                    <div class="grid gap-2">
                        <Label for="jira_in_progress_status">In Progress Status</Label>
                        <Input
                            id="jira_in_progress_status"
                            type="text"
                            class="mt-1 block w-full"
                            v-model="form.jira_in_progress_status"
                            placeholder="In Progress"
                        />
                        <InputError class="mt-2" :message="form.errors.jira_in_progress_status" />
                        <p class="text-sm text-muted-foreground">Status name for tickets when PR is in draft</p>
                    </div>

                    <div class="grid gap-2">
                        <Label for="jira_review_status">Review Status</Label>
                        <Input
                            id="jira_review_status"
                            type="text"
                            class="mt-1 block w-full"
                            v-model="form.jira_review_status"
                            placeholder="Review"
                        />
                        <InputError class="mt-2" :message="form.errors.jira_review_status" />
                        <p class="text-sm text-muted-foreground">Status name for tickets when PR is ready for review</p>
                    </div>

                    <div class="flex items-center gap-4">
                        <Button :disabled="form.processing">Save</Button>

                        <Transition
                            enter-active-class="transition ease-in-out"
                            enter-from-class="opacity-0"
                            leave-active-class="transition ease-in-out"
                            leave-to-class="opacity-0"
                        >
                            <p v-show="form.recentlySuccessful" class="text-sm text-neutral-600">Saved.</p>
                        </Transition>
                    </div>
                </form>
            </div>

            <DeleteUser />
        </SettingsLayout>
    </AppLayout>
</template>
