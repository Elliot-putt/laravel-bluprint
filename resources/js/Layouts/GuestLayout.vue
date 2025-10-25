<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div
        class="flex min-h-screen flex-col items-center bg-gradient-to-br from-black to-zinc-900 pt-6 sm:justify-center sm:pt-0"
    >
        <div>
            <Link href="/">
                <ApplicationLogo class="h-20 w-30 fill-current text-purple-400" />
            </Link>
        </div>

        <div
            class="mt-6 w-full overflow-hidden bg-black px-6 py-4 shadow-md shadow-purple-900/10 border border-zinc-800 sm:max-w-md sm:rounded-lg"
        >
            <slot />
        </div>
    </div>
</template>

<style scoped>
/* Any additional custom styles */
</style>
