<script setup>
    const props = defineProps(['message'])

    defineOptions({
        inheritAttrs: false
    })
    
</script>

<template>
    <label class="has-nuve cursor-pointer">
        <span v-bind="$attrs" class='nuve p-2 text-xs font-bold whitespace-nowrap'>
            {{ props.message }}
        </span>
        <slot></slot>
    </label>

</template>

<style scoped>
     .nuve {
        @apply invisible absolute;
        left: 50%;
        transform: translateX(-50%);
    }

    .has-nuve {
        @apply relative;
    }

    .has-nuve:hover .nuve {
        @apply visible z-30;
    }
</style>