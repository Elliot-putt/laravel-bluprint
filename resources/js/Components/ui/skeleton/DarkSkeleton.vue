<template>
    <div
        v-if="variant === 'default'"
        data-slot="dark-skeleton"
        :class="cn('animate-pulse rounded-md bg-zinc-700/50 shadow-inner border border-zinc-600/30',
                    height ? `h-${height}` : 'h-full',
                    props.class)"
    />

    <!-- Card Skeleton -->
    <div
        v-else-if="variant === 'card'"
        data-slot="dark-skeleton-card"
        :class="cn('flex flex-col space-y-3 p-4 rounded-lg bg-zinc-800/60 shadow-md border border-zinc-700/50',
                    height ? `h-${height}` : 'min-h-[180px]',
                    props.class)"
    >
        <div class="flex gap-3">
            <!-- Avatar -->
            <div class="animate-pulse rounded-full bg-zinc-700/50 shadow-inner border border-zinc-600/30 h-12 w-12" />
            <div class="flex-1 space-y-2">
                <!-- Name -->
                <div class="animate-pulse rounded-md bg-zinc-700/50 shadow-inner border border-zinc-600/30 h-4 w-3/4" />
                <!-- Role -->
                <div class="animate-pulse rounded-md bg-zinc-700/50 shadow-inner border border-zinc-600/30 h-3 w-1/2" />
            </div>
        </div>
        <!-- Content -->
        <div class="space-y-2 flex-grow">
            <div class="animate-pulse rounded-md bg-zinc-700/50 shadow-inner border border-zinc-600/30 h-4 w-full" />
            <div class="animate-pulse rounded-md bg-zinc-700/50 shadow-inner border border-zinc-600/30 h-4 w-full" />
            <div class="animate-pulse rounded-md bg-zinc-700/50 shadow-inner border border-zinc-600/30 h-4 w-4/5" />
        </div>
        <!-- Footer -->
        <div class="flex justify-between pt-2">
            <div class="animate-pulse rounded-md bg-zinc-700/50 shadow-inner border border-zinc-600/30 h-8 w-20" />
            <div class="animate-pulse rounded-md bg-zinc-700/50 shadow-inner border border-zinc-600/30 h-8 w-20" />
        </div>
    </div>

    <!-- List Skeleton -->
    <div
        v-else-if="variant === 'list'"
        data-slot="dark-skeleton-list"
        :class="cn('flex flex-col space-y-3 p-4 rounded-lg shadow-md border border-zinc-600/30 bg-zinc-800/60 ',
                    height ? `h-${height}` : 'min-h-[80px]',
                    props.class)"
    >
        <div v-for="i in items" :key="i" class="flex items-center space-x-3">
            <div class="animate-pulse rounded-md bg-zinc-700/50 shadow-inner border border-zinc-600/30 h-10 w-10" />
            <div class="flex-1 space-y-2">
                <div class="animate-pulse rounded-md bg-zinc-700/50 shadow-inner border border-zinc-600/30 h-4 w-full" />
                <div class="animate-pulse rounded-md bg-zinc-700/50 shadow-inner border border-zinc-600/30 h-3 w-4/5" />
            </div>
            <div class="animate-pulse rounded-md bg-zinc-700/50 shadow-inner border border-zinc-600/30 h-8 w-8" />
        </div>
    </div>

    <!-- Dashboard Skeleton -->
    <div
        v-else-if="variant === 'dashboard'"
        data-slot="dark-skeleton-dashboard"
        :class="cn('space-y-3 p-4 rounded-lg bg-zinc-800/60 shadow-md border border-zinc-700/50',
                    height ? `h-${height}` : 'min-h-[230px]',
                    props.class)"
    >
        <div class="flex justify-between">
            <div class="animate-pulse rounded-md bg-zinc-700/50 shadow-inner border border-zinc-600/30 h-6 w-1/3" />
            <div class="animate-pulse rounded-md bg-zinc-700/50 shadow-inner border border-zinc-600/30 h-6 w-16" />
        </div>
        <div class="animate-pulse rounded-md bg-zinc-700/50 shadow-inner border border-zinc-600/30 h-40 w-full" />
    </div>
</template>

<style scoped>
@keyframes darkPulse {
    0%, 100% {
        opacity: 0.7;
    }
    50% {
        opacity: 0.4;
    }
}

.animate-pulse {
    animation: darkPulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}
</style>

<script setup lang="ts">
import type { HTMLAttributes } from 'vue'
import { cn } from '@/lib/utils'

interface DarkSkeletonProps {
    class?: HTMLAttributes['class']
    variant?: 'card' | 'list' | 'dashboard' | 'default'
    height?: string | number
    items?: number
}

const props = withDefaults(defineProps<DarkSkeletonProps>(), {
    variant: 'default',
    items: 1
})
</script>
