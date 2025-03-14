<script setup>
    const props = defineProps({
        progress : {
            type : Number,
            default : 50
        },
        text : {
            type : Boolean,
            default : false
        }
    })

    defineOptions({
        inheritAttrs: false
    })

</script>

<template>
    <div class="w-full">
        <div class="bg-gray-300 relative rounded-full border-none overflow-hidden flex items-center">
            <div v-bind="$attrs" class="text-center font-bold leading-none transition-all" :style="{ width: progress + '%' }">
                <div class="absolute top-0.5 left-[50%]"> 
                    {{ props.text ? `${props.progress}%` : '' }}
                </div>
            </div>
        </div>
    </div>
</template>