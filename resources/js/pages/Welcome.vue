<template>
    <Head title="Home"  />

    <div class="flex min-h-screen flex-col dark bg-black text-white">
        <!-- Header -->
        <header class="sticky top-0 z-50 w-full border-b border-zinc-800 bg-black/80 backdrop-blur supports-[backdrop-filter]:bg-black/60">
            <div class="container flex h-16 items-center justify-between">
                <div class="flex items-center gap-2">
          <span class="text-xl font-bold bg-gradient-to-r from-purple-400 to-pink-600 text-transparent bg-clip-text">
          letsmerge.it
          </span>
                </div>
                <div class="flex items-center gap-4">
                    <a href="#features" class="text-sm font-medium text-zinc-400 hover:text-white transition-colors">
                        Features
                    </a>
                    <a
                        v-if="!$page.props.auth.user"
                        :href="route('account.redirect')"
                        class="text-sm font-medium text-zinc-400 hover:text-white transition-colors hidden sm:block"
                    >
                        Login with GitHub
                    </a>
                    <Link v-if="$page.props.auth.user"
                          :href="route('dashboard')"
                          class="text-sm font-medium text-zinc-400 hover:text-white transition-colors hidden sm:block">
                        Dashboard
                    </Link>
                    <a    v-if="!$page.props.auth.user"     :href="route('account.redirect')" class="px-4 py-2 rounded-md bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 text-white border-0">
                        Get Started
                    </a>
                </div>
            </div>
        </header>

        <main class="flex-1">
            <!-- Hero Section -->
            <section
                ref="heroSection"
                class="w-full py-12 md:py-24 lg:py-32 xl:py-48 relative overflow-hidden"
            >
                <div class="absolute inset-0 bg-gradient-to-br from-purple-900/20 to-black z-0"></div>
                <div class="absolute inset-0 bg-[url('https://placehold.co/1920x1080')] opacity-10 mix-blend-overlay"></div>
                <div class="container px-4 md:px-6 relative z-10">
                    <div class="grid gap-6 lg:grid-cols-[1fr_400px] lg:gap-12 xl:grid-cols-[1fr_600px]">
                        <div class="flex flex-col justify-center space-y-4">
                            <div class="space-y-2">
                                <h1 class="text-3xl font-bold tracking-tighter sm:text-5xl xl:text-6xl/none bg-gradient-to-r from-white to-zinc-300 text-transparent bg-clip-text">
                                    AI-Powered Pull Requests, Ready in Seconds
                                </h1>
                                <p class="max-w-[600px] text-zinc-400 md:text-xl">
                                    The intelligent GitHub pull request manager that writes perfect pull requests with AI, links to Jira tickets, and lets you customize templates your way.
                                </p>
                            </div>
                            <div class="flex flex-col gap-2 min-[400px]:flex-row">
                                <Link
                                    :href="route('register')"
                                    class="px-4 py-3 rounded-md gap-1 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 text-white border-0 flex items-center justify-center"
                                >
                                    Connect with GitHub
                                </Link>
                                <Link
                                    :href="route('register')"
                                    class="px-4 py-3 rounded-md border border-zinc-700 text-white hover:bg-zinc-800"
                                >
                                    Create a pull request
                                </Link>
                            </div>
                            <p class="text-xs text-zinc-500">Completely free. We only need your GitHub!</p>
                        </div>
                        <div class="relative group">
                            <div class="absolute -inset-1 bg-gradient-to-r from-purple-600 to-pink-600 rounded-xl blur opacity-25 group-hover:opacity-40 transition duration-1000"></div>
                            <img
                                :src="gitmanager"
                                alt="Dashboard Preview"
                                class="relative mx-auto aspect-video overflow-hidden rounded-xl object-fit object-center sm:w-full lg:order-last border border-zinc-800 shadow-2xl"
                            />
                        </div>
                    </div>
                </div>
            </section>

            <!-- Features Section -->
            <section
                ref="featuresSection"
                id="features"
                class="w-full py-12 md:py-24 lg:py-32 relative overflow-hidden"
            >
                <div class="absolute inset-0 bg-gradient-to-br from-purple-900/20 to-black z-0"></div>
                <div class="absolute inset-0 bg-[url('https://placehold.co/1920x1080')] opacity-10 mix-blend-overlay"></div>
                <div class="container px-4 md:px-6 relative z-10">
                    <div class="flex flex-col items-center justify-center space-y-4 text-center">
                        <div class="space-y-2">
                            <div class="inline-block rounded-lg bg-gradient-to-r from-purple-600 to-pink-600 px-3 py-1 text-sm text-white">
                                Features
                            </div>
                            <h2 class="text-3xl font-bold tracking-tighter md:text-4xl/tight bg-gradient-to-r from-white to-zinc-300 text-transparent bg-clip-text">
                                Supercharge your pull request workflow with AI
                            </h2>
                            <p class="max-w-[900px] text-zinc-400 md:text-xl/relaxed lg:text-base/relaxed xl:text-xl/relaxed">
                                letsmerge.it combines powerful AI with deep GitHub integration to help your team create better PRs faster.
                            </p>
                        </div>
                    </div>
                    <div class="mx-auto grid max-w-5xl items-start gap-6 py-12 md:grid-cols-2 lg:grid-cols-4">
                        <div v-for="(feature, index) in features" :key="index" class="bg-zinc-800/50 border border-zinc-700 rounded-lg p-6 backdrop-blur-sm hover:bg-zinc-800/80 transition-colors h-full">
                            <div class="pb-2">
                                <h3 class="text-lg font-semibold text-white">{{ feature.title }}</h3>
                            </div>
                            <div>
                                <p class="text-sm text-zinc-400">
                                    {{ feature.description }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Testimonials Section -->
<!--            <section-->
<!--                ref="testimonialsSection"-->
<!--                id="testimonials"-->
<!--                class="w-full py-12 md:py-24 lg:py-32 relative overflow-hidden"-->
<!--            >-->
<!--                <div class="absolute inset-0 bg-gradient-to-br from-purple-900/20 to-black z-0"></div>-->
<!--                <div class="absolute inset-0 bg-[url('https://placehold.co/1920x1080')] opacity-10 mix-blend-overlay"></div>-->
<!--                <div class="container px-4 md:px-6 relative z-10">-->
<!--                    <div class="flex flex-col items-center justify-center space-y-4 text-center">-->
<!--                        <div class="space-y-2">-->
<!--                            <div class="inline-block rounded-lg bg-gradient-to-r from-purple-600 to-pink-600 px-3 py-1 text-sm text-white">-->
<!--                                Testimonials-->
<!--                            </div>-->
<!--                            <h2 class="text-3xl font-bold tracking-tighter md:text-4xl/tight bg-gradient-to-r from-white to-zinc-300 text-transparent bg-clip-text">-->
<!--                                Loved by developers worldwide-->
<!--                            </h2>-->
<!--                            <p class="max-w-[900px] text-zinc-400 md:text-xl/relaxed lg:text-base/relaxed xl:text-xl/relaxed">-->
<!--                                See what our users have to say about how letsmerge.it has transformed their PR workflow.-->
<!--                            </p>-->
<!--                        </div>-->
<!--                    </div>-->
<!--                    <div class="mx-auto grid max-w-5xl gap-6 py-12 lg:grid-cols-3">-->
<!--                        <div v-for="(testimonial, index) in testimonials" :key="index"-->
<!--                             :class="[-->
<!--               'bg-zinc-800/30 backdrop-blur-sm hover:bg-zinc-800/50 transition-colors rounded-lg p-6 border',-->
<!--               index === 0 ? 'border-purple-500/50' : 'border-zinc-700'-->
<!--             ]">-->
<!--                            <div class="flex items-center gap-4 mb-4">-->
<!--                                <div class="relative">-->
<!--                                    <div class="absolute -inset-1 bg-gradient-to-r from-purple-600 to-pink-600 rounded-full blur opacity-70"></div>-->
<!--                                    <img-->
<!--                                        :src="testimonial.avatar"-->
<!--                                        :alt="testimonial.name"-->
<!--                                        class="relative rounded-full border border-zinc-700 w-10 h-10"-->
<!--                                    />-->
<!--                                </div>-->
<!--                                <div>-->
<!--                                    <h3 class="text-base font-semibold text-white">{{ testimonial.name }}</h3>-->
<!--                                    <p class="text-zinc-400 text-sm">{{ testimonial.title }}</p>-->
<!--                                </div>-->
<!--                            </div>-->
<!--                            <p class="text-sm text-zinc-300">-->
<!--                                "{{ testimonial.quote }}"-->
<!--                            </p>-->
<!--                        </div>-->
<!--                    </div>-->
<!--                </div>-->
<!--            </section>-->

            <!-- Final CTA Section -->
            <section
                ref="ctaSection"
                class="w-full py-12 md:py-24 lg:py-32 relative overflow-hidden"
            >
                <div class="absolute inset-0 bg-gradient-to-br from-purple-900/20 to-black z-0"></div>
                <div class="absolute inset-0 bg-[url('https://placehold.co/1920x1080')] opacity-5 mix-blend-overlay"></div>
                <div class="container px-4 md:px-6 relative z-10">
                    <div class="flex flex-col items-center justify-center space-y-4 text-center">
                        <div class="space-y-2">
                            <h2 class="text-3xl font-bold tracking-tighter md:text-4xl/tight bg-gradient-to-r from-white to-zinc-300 text-transparent bg-clip-text">
                                Ready to transform your pull request workflow?
                            </h2>
                            <p class="max-w-[600px] text-zinc-400 md:text-xl/relaxed lg:text-base/relaxed xl:text-xl/relaxed">
                                Join developers who are saving time and writing better PRs with letsmerge.it.
                            </p>
                        </div>
                        <div class="flex flex-col gap-2 min-[400px]:flex-row">
                            <Link
                                :href="route('register')"
                                class="px-4 py-3 rounded-md gap-1 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 text-white border-0 flex items-center justify-center"
                            >
                                Connect with GitHub
                            </Link>
                        </div>
                        <p class="text-xs text-zinc-500">100% free. We only need your GitHub! Start creating better PRs today.</p>
                    </div>
                </div>
            </section>
        </main>

        <!-- Footer -->
        <footer class="w-full border-t border-zinc-800 bg-black py-12">
            <div class="container px-4 md:px-6">
                <div class="grid gap-8 lg:grid-cols-3">
                    <div class="space-y-4">
                        <div class="flex items-center gap-2">
              <span class="text-xl font-bold bg-gradient-to-r from-purple-400 to-pink-600 text-transparent bg-clip-text">
                letsmerge.it
              </span>
                        </div>
                        <p class="text-sm text-zinc-400">AI-powered pull request management for GitHub. Fast, free, and forever.</p>
                    </div>
                    <div class="space-y-4">
                        <h3 class="text-sm font-medium text-white">Product</h3>
                        <nav class="flex flex-col gap-2">
                            <Link href="#" class="text-sm text-zinc-400 hover:text-white transition-colors">
                                Features
                            </Link>
                        </nav>
                    </div>
                </div>
                <div class="mt-8 border-t border-zinc-800 pt-8 text-center text-sm text-zinc-500">
                    &copy; {{ new Date().getFullYear() }} letsmerge.it. All rights reserved.
                </div>
            </div>
        </footer>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import {Head, Link} from '@inertiajs/vue3';
import gitmanager from '../../../public/images/maker.png'

// Refs for animation
const heroSection = ref(null);
const featuresSection = ref(null);
const testimonialsSection = ref(null);
const ctaSection = ref(null);

// Features data
const features = [
    {
        title: 'AI-Generated PRs',
        description: 'Let AI analyze your changes and create detailed, professional pull request descriptions with summaries of all modified files.'
    },
    {
        title: 'Jira Integration',
        description: 'Automatically link PRs to Jira tickets for better tracking. Pull context from tickets to create more detailed pull request descriptions.'
    },
    {
        title: 'Custom Templates',
        description: 'Configure your own pull request templates that match your team\'s standards. The AI will follow your guidelines every time.'
    },
    {
        title: 'Smart Assignment',
        description: 'Easily assign reviewers and labels to your PRs. Track pull request status and get reminders for stalled reviews.'
    }
];

// Testimonials data
const testimonials = [
    {
        name: 'Alex Rivera',
        title: 'Senior Developer, CodeStack',
        avatar: 'https://placehold.co/40x40',
        quote: 'I used to spend 30 minutes writing each pull request description. With letsmerge.it, I click a button and get a perfect PR in seconds. The AI understands my code better than some of my colleagues!'
    },
    {
        name: 'Mira Zhang',
        title: 'Engineering Manager, DevFlow',
        avatar: 'https://placehold.co/40x40',
        quote: 'The Jira integration is a game-changer for our team. Our pull request descriptions are now consistent, detailed, and automatically linked to tickets. This has improved our review process tremendously.'
    },
    {
        name: 'Jordan Lee',
        title: 'Lead Developer, TechBlaze',
        avatar: 'https://placehold.co/40x40',
        quote: 'Custom templates have been fantastic for enforcing our team\'s standards. Plus, it\'s completely free - that\'s unheard of for a tool this powerful. I recommend it to every dev team I know.'
    }
];

</script>

<style scoped>
.container {
    width: 100%;
    margin-left: auto;
    margin-right: auto;
    max-width: 1280px;
}

@media (min-width: 640px) {
    .container {
        max-width: 640px;
    }
}

@media (min-width: 768px) {
    .container {
        max-width: 768px;
    }
}

@media (min-width: 1024px) {
    .container {
        max-width: 1024px;
    }
}

@media (min-width: 1280px) {
    .container {
        max-width: 1280px;
    }
}
</style>
