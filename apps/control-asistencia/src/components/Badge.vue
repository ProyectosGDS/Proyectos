<script setup>
    const props = defineProps({
        text : {
            type : String,
            default : '',
        },
        color : {
            type : String,
            default : 'green',
        },
    })
</script>

<template>
    <div class="text-xs px-1.5 py-0.5 rounded-full text-nowrap text-center" 
        :class="`bg-${props.color}-200 text-${props.color}-500`">
        {{ props.text }}
    </div>
</template>
