<script setup lang="ts">
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const appName = computed(() => page.props.appName ?? 'Laravel Blueprint');

const props = defineProps({
    email: {
        type: String,
        required: true,
    },
    token: {
        type: String,
        required: true,
    },
});

const form = useForm({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('password.store'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Reset Password" />

        <div class="mb-8 text-center">
            <span class="text-xl font-bold text-black">
                {{ appName }}
            </span>
            <h2 class="mt-2 text-2xl font-semibold text-gray-900">Reset Your Password</h2>
            <p class="text-sm text-gray-600">Create a new secure password for your account</p>
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
                    readonly
                />
                <InputError class="mt-2" :message="form.errors.email" />
                <p class="mt-1 text-xs text-gray-500">This email address cannot be changed</p>
            </div>

            <div class="mt-4">
                <InputLabel for="password" value="New Password" />
                <TextInput
                    id="password"
                    type="password"
                    class="mt-1 block w-full"
                    v-model="form.password"
                    required
                    autocomplete="new-password"
                />
                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div class="mt-4">
                <InputLabel for="password_confirmation" value="Confirm New Password" />
                <TextInput
                    id="password_confirmation"
                    type="password"
                    class="mt-1 block w-full"
                    v-model="form.password_confirmation"
                    required
                    autocomplete="new-password"
                />
                <InputError class="mt-2" :message="form.errors.password_confirmation" />
            </div>

            <div class="mt-6">
                <PrimaryButton
                    class="w-full justify-center"
                    :class="{ 'opacity-70': form.processing }"
                    :disabled="form.processing"
                >
                    {{ form.processing ? 'Resetting...' : 'Reset Password' }}
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
