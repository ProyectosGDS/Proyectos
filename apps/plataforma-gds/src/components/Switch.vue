<script setup>
    import { ref, watchEffect } from 'vue'
    
    const props = defineProps({
        modelValue : '',
        values : {
            type : Array,
            default : () => []
        },
        error : {
            type : Boolean,
            default : false
        }
    })
    const emit = defineEmits(['update:modelValue'])

    const isChecked = ref(false)

    const toggleChecked = () => {
        isChecked.value = !isChecked.value 
        emit('update:modelValue',  props.modelValue == props.values[0] ? props.values[1] : props.values[0])
    }

    watchEffect(() => {
        isChecked.value = props.modelValue == props.values[0]
    })

</script>
<template>
    <label class="relative cursor-pointer block aspect-[1.6/0.75] rounded-full transition-all duration-500">
        <input class="peer/input hidden" type="checkbox" :checked="isChecked" :value="props.modelValue" @change="toggleChecked">
        <div class="absolute right-[6%] top-1/2 aspect-square h-[70%] -translate-y-1/2  rounded-full transition-all duration-500 peer-checked/input:right-[57%]" :class="props.error ? 'bg-red-500' : 'bg-white'"></div>
    </label>
</template>
