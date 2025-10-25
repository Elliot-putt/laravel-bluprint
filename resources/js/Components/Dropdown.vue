<template>
    <div class="relative">
        <div @click="open = !open">
            <slot name="trigger" />
        </div>

        <div
            v-show="open"
            @click="open = false"
            class="fixed inset-0 z-40"
        ></div>

        <transition
            enter-active-class="transition ease-out duration-200"
            enter-from-class="transform opacity-0 scale-95"
            enter-to-class="transform opacity-100 scale-100"
            leave-active-class="transition ease-in duration-75"
            leave-from-class="transform opacity-100 scale-100"
            leave-to-class="transform opacity-0 scale-95"
        >
            <div
                v-show="open"
                class="absolute z-50 mt-2 rounded-md shadow-lg"
                :class="[
                    align === 'right' ? 'right-0' : 'left-0',
                    width === '48' ? 'w-48' : 'w-56'
                ]"
            >
                <div class="py-1 bg-white rounded-md ring-1 ring-black ring-opacity-5">
                    <slot name="content" />
                </div>
            </div>
        </transition>
    </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';

defineProps({
    align: {
        type: String,
        default: 'right',
    },
    width: {
        type: String,
        default: '48',
    },
});

const open = ref(false);

const closeOnEscape = (e) => {
    if (open.value && e.keyCode === 27) {
        open.value = false;
    }
};

onMounted(() => {
    document.addEventListener('keydown', closeOnEscape);
});

onUnmounted(() => {
    document.removeEventListener('keydown', closeOnEscape);
});
</script>
