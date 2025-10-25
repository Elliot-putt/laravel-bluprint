<template>
    <Head title="Welcome" />
    
    <div class="min-h-screen bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="py-16 text-center">
                <h1 class="text-4xl font-bold text-gray-900 sm:text-5xl md:text-6xl">
                    Laravel Blueprint
                </h1>
                <p class="mt-3 max-w-md mx-auto text-base text-gray-500 sm:text-lg md:mt-5 md:text-xl md:max-w-3xl">
                    A clean, modern Laravel application with Vue.js and Inertia.js
                </p>
                <div class="mt-5 max-w-md mx-auto sm:flex sm:justify-center md:mt-8">
                    <div class="rounded-md shadow">
                        <Link
                            :href="route('login')"
                            class="w-full flex items-center justify-center px-8 py-3 border border-transparent text-base font-medium rounded-md text-white bg-black hover:bg-gray-800 md:py-4 md:text-lg md:px-10"
                        >
                            Get started
                        </Link>
                    </div>
                    <div class="mt-3 rounded-md shadow sm:mt-0 sm:ml-3">
                        <Link
                            :href="route('register')"
                            class="w-full flex items-center justify-center px-8 py-3 border border-transparent text-base font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 md:py-4 md:text-lg md:px-10"
                        >
                            Sign up
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Features -->
            <div class="py-16">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="text-center">
                        <h2 class="text-3xl font-bold text-gray-900">Features</h2>
                        <p class="mt-4 text-lg text-gray-600">
                            Everything you need to get started with Laravel
                        </p>
                    </div>

                    <div class="mt-12 grid grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-3">
                        <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                            <div class="p-6">
                                <h3 class="text-lg font-medium text-gray-900">Authentication</h3>
                                <p class="mt-2 text-gray-600">
                                    Complete user authentication system with login, registration, and password reset.
                                </p>
                            </div>
                        </div>

                        <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                            <div class="p-6">
                                <h3 class="text-lg font-medium text-gray-900">Modern UI</h3>
                                <p class="mt-2 text-gray-600">
                                    Clean, responsive design built with Vue.js and Tailwind CSS.
                                </p>
                            </div>
                        </div>

                        <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                            <div class="p-6">
                                <h3 class="text-lg font-medium text-gray-900">Profile Management</h3>
                                <p class="mt-2 text-gray-600">
                                    User profile management with secure password updates and account settings.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
</script>
