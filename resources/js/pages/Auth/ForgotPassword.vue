<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { route } from 'ziggy-js';

const page = usePage();

defineProps({
    status: {
        type: String,
    },
});

const form = useForm({
    _token: page.props.csrf_token,
    email: '',
});

const submit = () => {
    form.post(route('password.email'));
};
</script>

<template>
    <GuestLayout>
        <Head title="Forgot Password" />

        <!-- Header with Logo -->
        <div class="mb-6 text-center">
            <span class="text-xl font-bold text-black">
                Laravel Blueprint
            </span>
            <h2 class="mt-2 text-xl font-semibold text-gray-900">Password Recovery</h2>
        </div>

        <div class="mb-6 text-sm text-gray-600">
            Forgot your password? No problem. Enter your email address and we'll send you a password reset link.
        </div>

        <div
            v-if="status"
            class="mb-4 p-3 bg-green-50 border border-green-200 rounded-md text-sm font-medium text-green-800"
        >
            {{ status }}
        </div>

        <form @submit.prevent="submit">
            <div>
                <InputLabel for="email" value="Email" />

                <TextInput
                    id="email"
                    type="email"
                    class="mt-1 block w-full"
                    v-model="form.email"
                    required
                    autofocus
                    autocomplete="username"
                    placeholder="Enter your email address"
                />

                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div class="mt-6">
                <PrimaryButton
                    class="w-full justify-center"
                    :class="{ 'opacity-70': form.processing }"
                    :disabled="form.processing"
                >
                    {{ form.processing ? 'Sending...' : 'Send Password Reset Link' }}
                </PrimaryButton>
            </div>

            <div class="mt-4 text-center">
                <Link
                    :href="route('login')"
                    class="text-sm text-gray-600 hover:text-black transition-colors"
                >
                    Return to login
                </Link>
            </div>
        </form>
    </GuestLayout>
</template>
