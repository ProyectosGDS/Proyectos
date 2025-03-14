<script setup>
import { onMounted, ref } from 'vue'

const props = defineProps(['steps','components'])

const currentComponent = ref('')
const index = ref(0)


const focusStep = (indice) => {
    index.value = indice
    currentComponent.value = props.steps[indice].name
    props.steps.forEach(step => {
        if(step == props.steps[indice]) {
            props.steps.forEach(i => i.index > props.steps[indice].index ? i.active = false : i.active = true)
        }
    })
}

const next = () => {
    if(index.value < props.steps.length - 1){
        index.value ++
        currentComponent.value = props.steps[index.value].name
        props.steps.forEach(step => {
            if(step == props.steps[index.value]) {
                props.steps.forEach(i => i.index > props.steps[index.value].index ? i.active = false : i.active = true)
            }
        })
    }
}

const previus = () => {
    if(index.value >= 1) {
        index.value --
        currentComponent.value = props.steps[index.value].name
        props.steps.forEach(step => {
            if(step == props.steps[index.value]) {
                props.steps.forEach(i => i.index > props.steps[index.value].index ? i.active = false : i.active = true)
            }
        })
    }
}


onMounted(() => {
    currentComponent.value = props.steps[0].name
    props.steps.forEach(el => el.active = false)
    props.steps[0].active = true
})

</script>

<template>
<div class="flex justify-center">
    <div @click="focusStep(i)" v-for="(step,i) in steps" class="flex items-center group">
        <span class="h-1 sm:w-20 group-first:hidden" :class="step.active ? 'bg-violet-500' : 'bg-gray-300'"></span>
        <Tool-Tip :message="step.text" class="mt-10 text-gray-400">
            <div class="w-12 h-12 rounded-full text-2xl text-white font-semibold flex items-center justify-center" :class="step.active ? 'bg-violet-500' : 'bg-gray-300'" >
                {{ step.index }}
            </div>        
        </Tool-Tip>
        <span class="h-1 sm:w-16 group-last:hidden" :class="step.active ? 'bg-violet-500' : 'bg-gray-300'"></span>
    </div>
</div>
<br>
<component :is="components[currentComponent]"></component>
<!-- <br>
<div class="flex justify-evenly">
    <Button @click="previus" icon="fas fa-arrow-left" class="btn-primary" :disabled="!index > 0" />
    <Button @click="next" icon="fas fa-arrow-right" class="btn-primary" :disabled="!(index < steps.length - 1)" />
</div> -->
</template>