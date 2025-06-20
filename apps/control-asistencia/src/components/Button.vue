<script setup>
    const props = defineProps({
        text : '',
        icon : '',
        loading : {
            default : false
        },
        option : ''
    })

</script>

<template>
    <button class="btn relative shadow-md" :class="$props.text ? ' px-4 py-2 shadow-lg ' : ' rounded-full h-10 w-10 '"  :disabled="props.loading">
        <div :class="{'flex items-center justify-center gap-1.5' : $props.text }">
            <Icon v-if="props.icon && !props.loading" :icon="icon" class="text-base"/>
            <small v-if="$props.text">
                {{ props.loading ? 'Procesando' : props.text }}
            </small>
            <Icon v-if="props.loading" icon="fas fa-spinner" spin-pulse class="text-base text-gray-200"/>
        </div>
    </button>
</template>