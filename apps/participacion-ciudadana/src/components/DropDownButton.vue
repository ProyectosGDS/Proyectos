<script setup>
import { onClickOutside } from '@vueuse/core'
import { ref } from 'vue'

    const props = defineProps(['icon','title'])
    const open = ref(false)
    const target = ref(null)


    onClickOutside(target, (event) => open.value = false)

</script>

<template>
    <div @mouseleave="open = false" class="relative" ref="target">
        <Icon @mouseenter="open = true" :icon="props.icon" class="cursor-pointer px-3 py-2  hover:scale-90 rounded-lg text-gray-500" />
        <Transition name="fade">
            <div v-show="open" class="absolute bg-white p-4 text-xs rounded-md border shadow-xl grid gap-4 right-0 z-20">
               <slot></slot>
            </div>
        </Transition>
    </div>
</template>

<style scoped>
    .fade-enter-active, .fade-leave-active {
    transition: opacity 0.5s ease;
    }

    .fade-enter-from, .fade-leave-to {
    opacity: 0;
    }
</style>

