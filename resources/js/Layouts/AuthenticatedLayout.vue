<template>
    <div>
        <div class="min-h-screen bg-black text-white relative overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-br from-purple-900/20 to-black z-0"></div>
            <div class="absolute inset-0 bg-[url('https://placehold.co/1920x1080')] opacity-10 mix-blend-overlay"></div>
            <nav class="sticky top-0 z-50 w-full border-b border-zinc-800 bg-black/80 backdrop-blur supports-[backdrop-filter]:bg-black/60">
                <!-- Primary Navigation Menu -->
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="flex h-16 justify-between">
                        <div class="flex">
                            <!-- Logo -->
                            <div class="flex shrink-0 items-center">
                                <Link :href="'/'">
                                    <span class="text-xl font-bold bg-gradient-to-r from-purple-400 to-pink-600 text-transparent bg-clip-text">
                                      letsmerge.it
                                    </span>
                                </Link>
                            </div>

                            <!-- Navigation Links -->
                            <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                                <NavLink
                                    :href="route('dashboard')"
                                    :active="route().current('dashboard')"
                                    class="text-sm font-medium text-zinc-400 hover:text-white transition-colors"
                                >
                                    Dashboard
                                </NavLink>
                                <NavLink
                                    :href="route('templates.index')"
                                    :active="route().current('templates.index')"
                                    class="text-sm font-medium text-zinc-400 hover:text-white transition-colors"
                                >
                                    Templates
                                </NavLink>
                                <NavLink
                                    :href="route('pull-requests.index')"
                                    :active="route().current('pull-requests.index')"
                                    class="text-sm font-medium text-zinc-400 hover:text-white transition-colors"
                                >
                                    Pull Requests
                                </NavLink>
                                <NavLink
                                    :href="route('standup.index')"
                                    :active="route().current('standup.index')"
                                    class="text-sm font-medium text-zinc-400 hover:text-white transition-colors"
                                >
                                    Standup
                                </NavLink>
                            </div>
                        </div>

                        <div class="hidden sm:ms-6 sm:flex sm:items-center">
                            <!-- Settings Dropdown -->
                            <div class="relative ms-3">
                                <Dropdown align="right" width="48">
                                    <template #trigger>
                                        <span class="inline-flex rounded-md">
                                            <button
                                                type="button"
                                                class="inline-flex items-center rounded-md border border-zinc-700 bg-black px-3 py-2 text-sm font-medium leading-4 text-zinc-400 transition duration-150 ease-in-out hover:text-white focus:outline-none"
                                            >
                                                {{ $page.props.auth.user.name }}

                                                <svg
                                                    class="-me-0.5 ms-2 h-4 w-4"
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    viewBox="0 0 20 20"
                                                    fill="currentColor"
                                                >
                                                    <path
                                                        fill-rule="evenodd"
                                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                        clip-rule="evenodd"
                                                    />
                                                </svg>
                                            </button>
                                        </span>
                                    </template>

                                    <template #content>
                                        <div class="bg-zinc-800 border border-zinc-700 rounded-md shadow-lg py-1">
                                            <DropdownLink
                                                :href="route('profile.edit')"
                                                class="block px-4 py-2 text-sm text-zinc-400 hover:text-white hover:bg-zinc-700"
                                            >
                                                Profile
                                            </DropdownLink>
                                            <DropdownLink
                                                :href="route('logout')"
                                                method="post"
                                                as="button"
                                                class="block w-full text-left px-4 py-2 text-sm text-zinc-400 hover:text-white hover:bg-zinc-700"
                                            >
                                                Log Out
                                            </DropdownLink>
                                        </div>
                                    </template>
                                </Dropdown>
                            </div>
                        </div>

                        <!-- Hamburger -->
                        <div class="-me-2 flex items-center sm:hidden">
                            <button
                                @click="
                                    showingNavigationDropdown =
                                        !showingNavigationDropdown
                                "
                                class="inline-flex items-center justify-center rounded-md p-2 text-zinc-400 transition duration-150 ease-in-out hover:bg-zinc-800 hover:text-white focus:bg-zinc-800 focus:text-white focus:outline-none"
                            >
                                <svg
                                    class="h-6 w-6"
                                    stroke="currentColor"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        :class="{
                                            hidden: showingNavigationDropdown,
                                            'inline-flex':
                                                !showingNavigationDropdown,
                                        }"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M4 6h16M4 12h16M4 18h16"
                                    />
                                    <path
                                        :class="{
                                            hidden: !showingNavigationDropdown,
                                            'inline-flex':
                                                showingNavigationDropdown,
                                        }"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"
                                    />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Responsive Navigation Menu -->
                <div
                    :class="{
                        block: showingNavigationDropdown,
                        hidden: !showingNavigationDropdown,
                    }"
                    class="sm:hidden"
                >
                    <div class="space-y-1 pb-3 pt-2">
                        <ResponsiveNavLink
                            :href="route('dashboard')"
                            :active="route().current('dashboard')"
                            class="block py-2 px-4 text-base font-medium text-zinc-400 hover:text-white hover:bg-zinc-800"
                        >
                            Dashboard
                        </ResponsiveNavLink>
                        <ResponsiveNavLink
                            :href="route('templates.index')"
                            :active="route().current('templates.index')"
                            class="block py-2 px-4 text-base font-medium text-zinc-400 hover:text-white hover:bg-zinc-800"
                        >
                            Templates
                        </ResponsiveNavLink>
                    </div>

                    <!-- Responsive Settings Options -->
                    <div
                        class="border-t border-zinc-800 pb-1 pt-4"
                    >
                        <div class="px-4">
                            <div
                                class="text-base font-medium text-white"
                            >
                                {{ $page.props.auth.user.name }}
                            </div>
                            <div class="text-sm font-medium text-zinc-400">
                                {{ $page.props.auth.user.email }}
                            </div>
                        </div>

                        <div class="mt-3 space-y-1">
                            <ResponsiveNavLink
                                :href="route('profile.edit')"
                                class="block py-2 px-4 text-base font-medium text-zinc-400 hover:text-white hover:bg-zinc-800"
                            >
                                Profile
                            </ResponsiveNavLink>
                            <ResponsiveNavLink
                                :href="route('logout')"
                                method="post"
                                as="button"
                                class="block w-full text-left py-2 px-4 text-base font-medium text-zinc-400 hover:text-white hover:bg-zinc-800"
                            >
                                Log Out
                            </ResponsiveNavLink>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Page Heading -->
            <header
                class="bg-zinc-900/70 border-b border-zinc-800 shadow-md backdrop-blur-sm relative z-10"
                v-if="$slots.header"
            >
                <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                    <slot name="header" />
                </div>
            </header>

            <!-- Page Content -->
            <main class="relative z-10">
                <slot />
            </main>
        </div>
    </div>
</template>
<script setup>
import { ref } from 'vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import { Link } from '@inertiajs/vue3';

const showingNavigationDropdown = ref(false);
</script>
