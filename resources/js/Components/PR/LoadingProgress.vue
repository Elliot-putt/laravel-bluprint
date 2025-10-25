<template>
    <div v-if="isGenerating" class="mb-6 overflow-hidden">
        <div class="bg-zinc-800 border border-zinc-700 rounded-md p-4 shadow-md">
            <!-- Progress Bar -->
            <div class="w-full bg-zinc-700 rounded-full h-2 mb-4">
                <div
                    class="bg-gradient-to-r from-purple-600 to-pink-600 h-2 rounded-full transition-all duration-500 ease-in-out"
                    :style="{ width: `${progressPercentage}%` }"
                ></div>
            </div>

            <!-- Loading Message with Animation -->
            <div class="relative h-8">
                <transition
                    name="message-transition"
                    mode="out-in"
                >
                    <div
                        :key="currentStage"
                        class="absolute inset-0 flex items-center text-white font-medium"
                    >
                        <span v-if="currentStage > 0" class="mr-2 text-purple-400">
                            <component :is="currentIcon" class="h-5 w-5" />
                        </span>
                        {{ currentMessage }}
                    </div>
                </transition>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, onUnmounted, h } from 'vue';

// Define SVG icons as render functions
const SvgIconCode = {
    render() {
        return h('svg', {
            xmlns: 'http://www.w3.org/2000/svg',
            fill: 'none',
            viewBox: '0 0 24 24',
            stroke: 'currentColor'
        }, [
            h('path', {
                'stroke-linecap': 'round',
                'stroke-linejoin': 'round',
                'stroke-width': '2',
                d: 'M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4'
            })
        ]);
    }
};

const SvgIconAnalyze = {
    render() {
        return h('svg', {
            xmlns: 'http://www.w3.org/2000/svg',
            fill: 'none',
            viewBox: '0 0 24 24',
            stroke: 'currentColor'
        }, [
            h('path', {
                'stroke-linecap': 'round',
                'stroke-linejoin': 'round',
                'stroke-width': '2',
                d: 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z'
            })
        ]);
    }
};

const SvgIconTemplate = {
    render() {
        return h('svg', {
            xmlns: 'http://www.w3.org/2000/svg',
            fill: 'none',
            viewBox: '0 0 24 24',
            stroke: 'currentColor'
        }, [
            h('path', {
                'stroke-linecap': 'round',
                'stroke-linejoin': 'round',
                'stroke-width': '2',
                d: 'M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z'
            })
        ]);
    }
};

const SvgIconAI = {
    render() {
        return h('svg', {
            xmlns: 'http://www.w3.org/2000/svg',
            fill: 'none',
            viewBox: '0 0 24 24',
            stroke: 'currentColor'
        }, [
            h('path', {
                'stroke-linecap': 'round',
                'stroke-linejoin': 'round',
                'stroke-width': '2',
                d: 'M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z'
            })
        ]);
    }
};

const SvgIconFinalize = {
    render() {
        return h('svg', {
            xmlns: 'http://www.w3.org/2000/svg',
            fill: 'none',
            viewBox: '0 0 24 24',
            stroke: 'currentColor'
        }, [
            h('path', {
                'stroke-linecap': 'round',
                'stroke-linejoin': 'round',
                'stroke-width': '2',
                d: 'M5 13l4 4L19 7'
            })
        ]);
    }
};

const props = defineProps({
    isGenerating: {
        type: Boolean,
        default: false
    }
});

// Define the loading stages with icons
const stages = [
    {
        id: 1,
        message: "Fetching your code differences...",
        duration: 2000,
        icon: 'svg-icon-code'
    },
    {
        id: 2,
        message: "Analyzing repository changes...",
        duration: 2000,
        icon: 'svg-icon-analyze'
    },
    {
        id: 3,
        message: "Fetching your custom template...",
        duration: 1500,
        icon: 'svg-icon-template'
    },
    {
        id: 4,
        message: "Generating your PR with AI...",
        duration: 3000,
        icon: 'svg-icon-ai'
    },
    {
        id: 5,
        message: "Finalizing your pull request...",
        duration: 1500,
        icon: 'svg-icon-finalize'
    }
];

// State
const currentStage = ref(0);
const progressPercentage = ref(0);
const stageInterval = ref(null);

// Computed
const currentMessage = computed(() => {
    if (currentStage.value === 0) return "";
    return stages[currentStage.value - 1].message;
});

const currentIcon = computed(() => {
    if (currentStage.value === 0) return null;

    const iconName = stages[currentStage.value - 1].icon;
    switch(iconName) {
        case 'svg-icon-code':
            return SvgIconCode;
        case 'svg-icon-analyze':
            return SvgIconAnalyze;
        case 'svg-icon-template':
            return SvgIconTemplate;
        case 'svg-icon-ai':
            return SvgIconAI;
        case 'svg-icon-finalize':
            return SvgIconFinalize;
        default:
            return null;
    }
});

const nextStage = () => {
    if (currentStage.value < stages.length) {
        currentStage.value++;

        // Calculate progress percentage
        progressPercentage.value = (currentStage.value / stages.length) * 100;

        // Schedule the next stage
        const currentDuration = stages[currentStage.value - 1].duration;

        stageInterval.value = setTimeout(() => {
            if (props.isGenerating) {
                nextStage();
            }
        }, currentDuration);
    }
};

const stopLoadingAnimation = () => {
    if (stageInterval.value) {
        clearTimeout(stageInterval.value);
        stageInterval.value = null;
    }

    // Complete the progress bar
    progressPercentage.value = 100;

    // Reset after a delay
    setTimeout(() => {
        if (!props.isGenerating) {
            currentStage.value = 0;
            progressPercentage.value = 0;
        }
    }, 1000);
};

const startLoadingAnimation = () => {
    // Reset state
    currentStage.value = 0;
    progressPercentage.value = 0;

    // Clear any existing interval
    if (stageInterval.value) {
        clearInterval(stageInterval.value);
    }

    // Start the animation sequence
    nextStage();
};

// Start the loading animation when isGenerating becomes true
watch(() => props.isGenerating, (newValue) => {
    if (newValue) {
        startLoadingAnimation();
    } else {
        stopLoadingAnimation();
    }
}, { immediate: true });

// Methods



// Clean up on component unmount
onUnmounted(() => {
    if (stageInterval.value) {
        clearTimeout(stageInterval.value);
    }
});
</script>

<style scoped>
.message-transition-enter-active,
.message-transition-leave-active {
    transition: all 0.5s ease;
}

.message-transition-enter-from {
    opacity: 0;
    transform: translateY(20px);
}

.message-transition-leave-to {
    opacity: 0;
    transform: translateY(-20px);
}
</style>
