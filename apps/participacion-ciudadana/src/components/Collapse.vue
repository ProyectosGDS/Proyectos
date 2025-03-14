<script setup>

    const props = defineProps(['title']);

</script>

<template>
    <details class="border rounded-lg p-5 ">
        <summary class="cursor-pointer text-violet-500 font-medium">
            {{ props.title }}
        </summary>
        <Transition name="fade">
            <slot></slot>
        </Transition>
    </details>
</template>

<style scoped>
    .fade-enter-active, .fade-leave-active {
    transition: opacity 0.5s ease;
    }

    .fade-enter-from, .fade-leave-to {
    opacity: 0;
    }
</style>
