<template>
    <Head title="Standup" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-semibold leading-tight text-white">Standup</h2>
            </div>
        </template>
        <div class="py-6 h-100vh">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="bg-zinc-800/50 border border-zinc-700 rounded-lg shadow-lg backdrop-blur-sm">
                    <div class="p-6">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="flex items-center justify-center w-12 h-12 bg-gradient-to-br from-purple-600 to-pink-600 rounded-lg shadow-lg">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                </svg>
                            </div>
                            <div>
                                <h2 class="text-2xl font-bold text-white">Yesterday's Standup Summary</h2>
                                <p class="text-sm text-zinc-400">Your coding activity from {{ formatDate(new Date(Date.now() - 86400000)) }}</p>
                            </div>
                            <div class="ml-auto flex items-center gap-2">
                                <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                                <span class="text-xs text-zinc-400">Active Session</span>
                            </div>
                        </div>

                        <div class="mb-8 p-6 bg-gradient-to-r from-purple-900/20 via-pink-900/20 to-purple-900/20 rounded-lg relative overflow-hidden ai-summary-breathing">
                            <div class="relative z-10">
                                <h3 class="text-lg font-semibold text-white mb-3 flex items-center gap-2">
                                    <span class="text-2xl">✨</span>
                                    AI Summary
                                </h3>
                                <p class="text-zinc-200 leading-relaxed" v-if="summary" v-html="summary"></p>
                                <p class="text-zinc-200 leading-relaxed" v-else>
                                    No AI summary available for yesterday.
                                </p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                            <div class="bg-zinc-900/50 border border-zinc-600 rounded-lg p-6">
                                <h3 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                                    <div class="w-3 h-3 bg-green-500 rounded-full"></div>
                                    Development
                                </h3>
                                <div class="relative">
                                    <canvas ref="velocityChart" width="300" height="200"></canvas>
                                </div>
                            </div>

                            <div class="bg-zinc-900/50 border border-zinc-600 rounded-lg p-6">
                                <h3 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                                    <div class="w-3 h-3 bg-purple-500 rounded-full"></div>
                                    Review Activity
                                </h3>
                                <div class="relative">
                                    <canvas ref="reviewChart" width="300" height="200"></canvas>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                            <div
                                class="bg-gradient-to-br from-green-600/20 to-emerald-600/20 border border-green-500/30 rounded-lg p-4 relative group cursor-pointer hover:scale-105 transition-transform duration-200"
                                @mouseenter="handleMouseEnter('prs_merged')"
                                @mouseleave="handleMouseLeave"
                            >
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-green-400 text-sm font-medium">PRs Merged</p>
                                        <p class="text-2xl font-bold text-white">{{ metrics.prs_merged.count }}</p>
                                    </div>
                                    <div class="text-green-400">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                </div>

                                <div
                                    v-if="showTooltip === 'prs_merged'"
                                    class="absolute top-full left-0 mt-2 w-80 bg-zinc-800 border border-zinc-600 rounded-lg shadow-xl z-50 p-4"
                                    @mouseenter="handleTooltipEnter"
                                    @mouseleave="handleTooltipLeave"
                                >
                                    <h4 class="text-white font-semibold mb-2">Merged Pull Requests</h4>
                                    <div v-if="metrics.prs_merged.details.length > 0" class="space-y-2">
                                        <div v-for="pr in metrics.prs_merged.details" :key="pr.number" class="border-l-2 border-green-500 pl-3 py-1">
                                            <p class="text-white text-sm font-medium">{{ pr.title }}</p>
                                            <p class="text-zinc-400 text-xs">{{ pr.repository_full_name }} • {{ formatTime(pr.mergedAt) }}</p>
                                            <a :href="pr.url" target="_blank" class="text-green-400 text-xs hover:underline">View PR →</a>
                                        </div>
                                    </div>
                                    <p v-else class="text-zinc-400 text-sm">No PRs were merged yesterday</p>
                                </div>
                            </div>

                            <div
                                class="bg-gradient-to-br from-blue-600/20 to-cyan-600/20 border border-blue-500/30 rounded-lg p-4 relative group cursor-pointer hover:scale-105 transition-transform duration-200"
                                @mouseenter="handleMouseEnter('prs_opened')"
                                @mouseleave="handleMouseLeave"
                            >
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-blue-400 text-sm font-medium">PRs Opened</p>
                                        <p class="text-2xl font-bold text-white">{{ metrics.prs_opened.count }}</p>
                                    </div>
                                    <div class="text-blue-400">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                        </svg>
                                    </div>
                                </div>

                                <div
                                    v-if="showTooltip === 'prs_opened'"
                                    class="absolute top-full left-0 mt-2 w-80 bg-zinc-800 border border-zinc-600 rounded-lg shadow-xl z-50 p-4"
                                    @mouseenter="handleTooltipEnter"
                                    @mouseleave="handleTooltipLeave"
                                >
                                    <h4 class="text-white font-semibold mb-2">Opened Pull Requests</h4>
                                    <div v-if="metrics.prs_opened.details.length > 0" class="space-y-2">
                                        <div v-for="pr in metrics.prs_opened.details" :key="pr.number" class="border-l-2 border-blue-500 pl-3 py-1">
                                            <p class="text-white text-sm font-medium">{{ pr.title }}</p>
                                            <p class="text-zinc-400 text-xs">{{ pr.repository_full_name }} • {{ formatTime(pr.createdAt) }}</p>
                                            <p class="text-zinc-400 text-xs">{{ pr.baseRefName }} ← {{ pr.headRefName }}</p>
                                            <a :href="pr.url" target="_blank" class="text-blue-400 text-xs hover:underline">View PR →</a>
                                        </div>
                                    </div>
                                    <p v-else class="text-zinc-400 text-sm">No PRs were opened yesterday</p>
                                </div>
                            </div>

                            <div
                                class="bg-gradient-to-br from-yellow-600/20 to-orange-600/20 border border-yellow-500/30 rounded-lg p-4 relative group cursor-pointer hover:scale-105 transition-transform duration-200"
                                @mouseenter="handleMouseEnter('tickets_transitioned')"
                                @mouseleave="handleMouseLeave"
                            >
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-yellow-400 text-sm font-medium">Tickets Transitioned</p>
                                        <p class="text-2xl font-bold text-white">{{ metrics.tickets_transitioned.count }}</p>
                                    </div>
                                    <div class="text-yellow-400">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                        </svg>
                                    </div>
                                </div>

                                <div
                                    v-if="showTooltip === 'tickets_transitioned'"
                                    class="absolute top-full left-0 mt-2 w-80 bg-zinc-800 border border-zinc-600 rounded-lg shadow-xl z-50 p-4"
                                    @mouseenter="handleTooltipEnter"
                                    @mouseleave="handleTooltipLeave"
                                >
                                    <h4 class="text-white font-semibold mb-2">Transitioned Tickets</h4>
                                    <div v-if="metrics.tickets_transitioned.details.length > 0" class="space-y-2">
                                        <div v-for="ticket in metrics.tickets_transitioned.details" :key="ticket.key" class="border-l-2 border-yellow-500 pl-3 py-1">
                                            <p class="text-white text-sm font-medium">{{ ticket.key }}: {{ ticket.fields?.summary || 'No Title' }}</p>
                                            <div v-if="getMostRecentTransition(ticket.status_transitions)" class="mt-1">
                                                <div class="text-xs text-zinc-400">
                                                    <span class="text-red-400">{{ getMostRecentTransition(ticket.status_transitions).from_status }}</span> → <span class="text-green-400">{{ getMostRecentTransition(ticket.status_transitions).to_status }}</span>
                                                    <span class="text-zinc-500 ml-1">at {{ formatTime(getMostRecentTransition(ticket.status_transitions).changed_at) }}</span>
                                                </div>
                                            </div>
                                            <a :href="ticket.jira_url" target="_blank" class="text-yellow-400 text-xs hover:underline">View Ticket →</a>
                                        </div>
                                    </div>
                                    <p v-else class="text-zinc-400 text-sm">No tickets were transitioned yesterday</p>
                                </div>
                            </div>

                            <div
                                class="bg-gradient-to-br from-pink-600/20 to-purple-600/20 border border-pink-500/30 rounded-lg p-4 relative group cursor-pointer hover:scale-105 transition-transform duration-200"
                                @mouseenter="handleMouseEnter('code_reviews')"
                                @mouseleave="handleMouseLeave"
                            >
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-pink-400 text-sm font-medium">Code Reviews</p>
                                        <p class="text-2xl font-bold text-white">{{ metrics.code_reviews.count }}</p>
                                    </div>
                                    <div class="text-pink-400">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </div>
                                </div>

                                <div
                                    v-if="showTooltip === 'code_reviews'"
                                    class="absolute top-full left-0 mt-2 w-80 bg-zinc-800 border border-zinc-600 rounded-lg shadow-xl z-50 p-4"
                                    @mouseenter="handleTooltipEnter"
                                    @mouseleave="handleTooltipLeave"
                                >
                                    <h4 class="text-white font-semibold mb-2">Code Reviews Completed</h4>
                                    <div v-if="metrics.code_reviews.details.length > 0" class="space-y-2">
                                        <div v-for="review in metrics.code_reviews.details" :key="review.id" class="border-l-2 border-pink-500 pl-3 py-1">
                                            <p class="text-white text-sm font-medium">{{ review.pr_title }}</p>
                                            <div class="flex items-center gap-2 mt-1">
                                                <span
                                                    :class="{
                                                        'text-green-400': review.state === 'APPROVED',
                                                        'text-red-400': review.state === 'CHANGES_REQUESTED',
                                                        'text-yellow-400': review.state === 'COMMENTED',
                                                        'text-gray-400': review.state === 'DISMISSED'
                                                    }"
                                                    class="text-xs font-medium"
                                                >
                                                    {{ review.state.replace('_', ' ') }}
                                                </span>
                                            </div>
                                            <p class="text-zinc-400 text-xs">{{ review.repository_full_name }} • {{ formatTime(review.submitted_at) }}</p>
                                            <a :href="`https://github.com/${review.repository_full_name}/pull/${review.pr_number}`" target="_blank" class="text-pink-400 text-xs hover:underline">View PR →</a>
                                        </div>
                                    </div>
                                    <p v-else class="text-zinc-400 text-sm">No code reviews were completed yesterday</p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-zinc-900/50 border border-zinc-600 rounded-lg p-6">
                            <h3 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                                <div class="w-3 h-3 bg-cyan-500 rounded-full"></div>
                                Activity Timeline
                            </h3>
                            <div class="space-y-4" v-if="timeline.length > 0">
                                <div v-for="item in timeline" :key="item.type + item.time" class="flex items-start gap-4">
                                    <div :class="getTimelineIconClass(item.color)" class="flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center mt-1">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path v-if="item.icon === 'check'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                            <path v-else-if="item.icon === 'plus'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                            <path v-else-if="item.icon === 'eye'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            <path v-else-if="item.icon === 'tag'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                            <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                        </svg>
                                    </div>
                                    <div class="flex-1">
                                        <p class="text-white font-medium">{{ item.title }}</p>
                                        <p class="text-zinc-400 text-sm">{{ item.description }} • {{ formatTime(item.time) }}</p>
                                    </div>
                                </div>
                            </div>
                            <div v-else class="text-center py-8">
                                <div class="text-zinc-500 text-lg mb-2">📋</div>
                                <p class="text-zinc-400">No activity recorded for yesterday</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import {Head, Link} from '@inertiajs/vue3';
import { ref, onMounted, computed } from 'vue';
import Chart from 'chart.js/auto';

const props = defineProps({
    summary:String,
    github: Object,
    jira: Object,
    metrics: Object,
    timeline: Array,
    charts: Object
});

const velocityChart = ref(null);
const reviewChart = ref(null);
const showTooltip = ref(null);
const tooltipTimeout = ref(null);

const hasActivity = computed(() => {
    return props.metrics.prs_merged.count > 0 ||
        props.metrics.prs_opened.count > 0 ||
        props.metrics.tickets_transitioned.count > 0 ||
        props.metrics.code_reviews.count > 0;
});

const getProductivityLevel = () => {
    const totalActivity = props.metrics.prs_merged.count + props.metrics.prs_opened.count +
        props.metrics.tickets_transitioned.count + props.metrics.code_reviews.count;

    if (totalActivity >= 8) return 'highly productive';
    if (totalActivity >= 5) return 'productive';
    if (totalActivity >= 3) return 'moderately productive';
    return 'steady';
};

const getJiraActivity = () => {
    if (props.metrics.tickets_transitioned.count === 0) return '';

    const ticketText = props.metrics.tickets_transitioned.count === 1 ? 'ticket' : 'tickets';
    return ` You transitioned ${props.metrics.tickets_transitioned.count} JIRA ${ticketText}.`;
};

const getReviewActivity = () => {
    if (props.metrics.code_reviews.count === 0) return '';

    const reviewText = props.metrics.code_reviews.count === 1 ? 'review' : 'reviews';
    return ` Your code reviews were on point - you completed ${props.metrics.code_reviews.count} ${reviewText} for the team.`;
};

const handleMouseEnter = (tooltipType) => {
    if (tooltipTimeout.value) {
        clearTimeout(tooltipTimeout.value);
    }
    showTooltip.value = tooltipType;
};

const handleMouseLeave = () => {
    tooltipTimeout.value = setTimeout(() => {
        showTooltip.value = null;
    }, 300);
};

const handleTooltipEnter = () => {
    if (tooltipTimeout.value) {
        clearTimeout(tooltipTimeout.value);
    }
};

const getMostRecentTransition = (transitions) => {
    if (!transitions || transitions.length === 0) return null;
    return transitions.reduce((latest, current) => {
        return new Date(current.changed_at) > new Date(latest.changed_at) ? current : latest;
    });
};

const getOverallAssessment = () => {
    const totalActivity = props.metrics.prs_merged.count + props.metrics.prs_opened.count +
        props.metrics.tickets_transitioned.count + props.metrics.code_reviews.count;

    if (totalActivity >= 8) return 'excellent';
    if (totalActivity >= 5) return 'great';
    if (totalActivity >= 3) return 'good';
    return 'solid';
};

const getTimelineIconClass = (color) => {
    const baseClass = 'bg-';
    switch (color) {
        case 'green': return 'bg-green-600';
        case 'blue': return 'bg-blue-600';
        case 'pink': return 'bg-pink-600';
        case 'yellow': return 'bg-yellow-600';
        default: return 'bg-gray-600';
    }
};

const formatDate = (date) => {
    return new Intl.DateTimeFormat('en-US', {
        month: 'long',
        day: 'numeric',
        year: 'numeric'
    }).format(date);
};

const formatTime = (timeString) => {
    if (!timeString) return '';

    try {
        const date = new Date(timeString);
        if (isNaN(date.getTime())) return 'Invalid date';

        return new Intl.DateTimeFormat('en-US', {
            hour: 'numeric',
            minute: '2-digit',
            hour12: true
        }).format(date);
    } catch (error) {
        return 'Invalid date';
    }
};

const createVelocityChart = () => {
    const ctx = velocityChart.value.getContext('2d');

    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: props.charts.velocity.labels,
            datasets: [{
                data: props.charts.velocity.data,
                backgroundColor: [
                    'rgba(34, 197, 94, 0.8)',
                    'rgba(59, 130, 246, 0.8)',
                    'rgba(168, 85, 247, 0.8)',
                    'rgba(156, 163, 175, 0.8)'
                ],
                borderColor: [
                    'rgba(34, 197, 94, 1)',
                    'rgba(59, 130, 246, 1)',
                    'rgba(168, 85, 247, 1)',
                    'rgba(156, 163, 175, 1)'
                ],
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        color: '#d4d4d8',
                        font: {
                            size: 12
                        },
                        padding: 15
                    }
                }
            }
        }
    });
};

const createReviewChart = () => {
    const ctx = reviewChart.value.getContext('2d');

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: props.charts.reviews.labels,
            datasets: [{
                data: props.charts.reviews.data,
                backgroundColor: [
                    'rgba(34, 197, 94, 0.8)',
                    'rgba(236, 72, 153, 0.8)',
                    'rgba(251, 191, 36, 0.8)',
                    'rgba(239, 68, 68, 0.8)'
                ],
                borderColor: [
                    'rgba(34, 197, 94, 1)',
                    'rgba(236, 72, 153, 1)',
                    'rgba(251, 191, 36, 1)',
                    'rgba(239, 68, 68, 1)'
                ],
                borderWidth: 2,
                borderRadius: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        color: '#d4d4d8',
                        stepSize: 1
                    },
                    grid: {
                        color: 'rgba(113, 113, 122, 0.3)'
                    }
                },
                x: {
                    ticks: {
                        color: '#d4d4d8'
                    },
                    grid: {
                        display: false
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });
};

onMounted(() => {
    if (velocityChart.value && reviewChart.value) {
        createVelocityChart();
        createReviewChart();
    }
});
</script>

<style scoped>
.ai-summary-breathing {
    border: 2px solid rgba(147, 51, 234, 0.6);
    animation: ai-breathe 3s ease-in-out infinite;
}

@keyframes ai-breathe {
    0%, 100% {
        border-color: rgba(147, 51, 234, 0.6);
        box-shadow: 0 0 10px rgba(147, 51, 234, 0.3);
    }
    50% {
        border-color: rgba(236, 72, 153, 0.8);
        box-shadow: 0 0 25px rgba(236, 72, 153, 0.5), 0 0 40px rgba(147, 51, 234, 0.3);
    }
}
</style>
