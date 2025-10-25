<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    status: {
        type: String,
    },
});

const form = useForm({
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
            <span class="text-xl font-bold bg-gradient-to-r from-purple-400 to-pink-600 text-transparent bg-clip-text">
                letsmerge.it
            </span>
            <h2 class="mt-2 text-xl font-semibold text-white">Password Recovery</h2>
        </div>

        <div class="mb-6 text-sm text-zinc-400">
            Forgot your password? No problem. Enter your email address and we'll send you a password reset link.
        </div>

        <!-- GitHub Login Suggestion -->
        <div class="bg-zinc-900/30 border border-zinc-800 rounded-md p-4 mb-6">
            <h3 class="text-sm font-medium text-white mb-2">Using GitHub?</h3>
            <p class="text-xs text-zinc-400 mb-3">
                If you normally sign in with GitHub, you don't need to reset your password.
            </p>
            <Link
                :href="route('account.redirect')"
                class="flex items-center justify-center w-full px-3 py-2 rounded-md gap-2 bg-black border border-zinc-800 text-white hover:bg-zinc-900 transition-colors text-xs"
            >
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="white">
                    <path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/>
                </svg>
                <span>Sign in with GitHub instead</span>
            </Link>
        </div>

        <div
            v-if="status"
            class="mb-4 p-3 bg-green-900/30 border border-green-500/30 rounded-md text-sm font-medium text-green-400"
        >
            {{ status }}
        </div>

        <!-- Divider -->
        <div class="relative my-6">
            <div class="absolute inset-0 flex items-center">
                <div class="w-full border-t border-zinc-800"></div>
            </div>
            <div class="relative flex justify-center text-xs uppercase">
                <span class="bg-black px-2 text-zinc-500">Email reset</span>
            </div>
        </div>

        <form @submit.prevent="submit">
            <div>
                <InputLabel for="email" value="Email" class="text-zinc-300" />

                <TextInput
                    id="email"
                    type="email"
                    class="mt-1 block w-full bg-black border-zinc-800 text-white focus:border-purple-500 focus:ring-purple-500"
                    v-model="form.email"
                    required
                    autofocus
                    autocomplete="username"
                />

                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div class="mt-6">
                <button
                    type="submit"
                    class="w-full px-4 py-3 rounded-md gap-1 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 text-white border-0 flex items-center justify-center"
                    :class="{ 'opacity-70': form.processing }"
                    :disabled="form.processing"
                >
                    {{ form.processing ? 'Sending...' : 'Send Password Reset Link' }}
                </button>
            </div>

            <div class="mt-4 text-center">
                <Link
                    :href="route('login')"
                    class="text-sm text-zinc-400 hover:text-white transition-colors"
                >
                    Return to login
                </Link>
            </div>
        </form>
    </GuestLayout>
</template>

<style scoped>
/* Custom styles for the forgot password form */
:deep(.text-gray-500) {
    @apply text-purple-400;
}

:deep(.bg-white) {
    @apply bg-black border border-zinc-800 shadow-lg shadow-purple-900/10;
}

:deep(.bg-gray-100) {
    @apply bg-gradient-to-br from-black to-zinc-900;
}
</style>
