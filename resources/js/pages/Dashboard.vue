<template>
    <Head title="dash" />

    <AuthenticatedLayout>
        <template #header>
          hi
        </template>

    </AuthenticatedLayout>
</template>

<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
</script>
