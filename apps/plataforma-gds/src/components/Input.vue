<script setup>

    import { computed } from 'vue'

    const props = defineProps({
        icon: '',
        title: '',
        option: '',
        modelValue: '',
        error: false,
        items: [],
    })

    const items = computed(() => props.items)

    defineEmits(['update:modelValue'])
    
    defineOptions({
        inheritAttrs: false
    })
</script>

<template>
    <div v-if="props.option === 'label'" class="relative w-full">
        <input
            v-bind="$attrs"
            :class="{'border-red-500' : props.error }"
            class="peer m-0 block bg-white w-full rounded border focus-within:border-color-4 bg-transparent bg-clip-padding px-3 py-3.5 text-base font-normal leading-tight text-neutral-700 transition duration-200 ease-linear placeholder:text-transparent focus:border-primary focus:pb-[0.625rem] focus:pt-[1.625rem] focus:text-neutral-700 focus:outline-none peer-focus:text-primary [&:not(:placeholder-shown)]:pb-[0.625rem] [&:not(:placeholder-shown)]:pt-[1.625rem]"
            placeholder="name@example.com"
            :value="props.modelValue" @input="$emit('update:modelValue', $event.target.value)"
        />
        <label
            class="pointer-events-none absolute left-0 top-0 origin-[0_0] border border-solid border-transparent px-3 py-3.5 text-color-4 transition-[opacity,_transform] duration-200 ease-linear peer-focus:-translate-y-2 peer-focus:translate-x-[0.15rem] peer-focus:scale-[0.85] peer-focus:text-gray-400 peer-[:not(:placeholder-shown)]:-translate-y-2 peer-[:not(:placeholder-shown)]:translate-x-[0.15rem] peer-[:not(:placeholder-shown)]:scale-[0.85] motion-reduce:transition-none uppercase">
            {{ props.title }}
        </label>
    </div>
    <div v-else-if="props.option === 'select'" class="relative w-full">
        <select
            v-bind="$attrs"
            :class="{'border-red-500' : props.error }"
            class="peer m-0 block h-[58px] bg-white w-full rounded border focus-within:border-color-4 bg-transparent bg-clip-padding px-3 py-4 text-base font-normal leading-tight text-neutral-700 transition duration-200 ease-linear placeholder:text-transparent focus:border-primary focus:pb-[0.625rem] focus:pt-[1.625rem] focus:text-neutral-700 focus:outline-none peer-focus:text-primary [&:not(:placeholder-shown)]:pb-[0.625rem] [&:not(:placeholder-shown)]:pt-[1.625rem]"
            :value="props.modelValue" @input="$emit('update:modelValue', $event.target.value)"
        >
        <slot></slot>
        </select>
        <label
        class="pointer-events-none absolute left-0 top-0 origin-[0_0] border border-solid border-transparent px-3 py-4 text-color-4 transition-[opacity,_transform] duration-200 ease-linear peer-focus:-translate-y-2 peer-focus:translate-x-[0.15rem] peer-focus:scale-[0.85] peer-focus:text-gray-400 peer-[:not(:placeholder-shown)]:-translate-y-2 peer-[:not(:placeholder-shown)]:translate-x-[0.15rem] peer-[:not(:placeholder-shown)]:scale-[0.85] motion-reduce:transition-none uppercase">
            {{ props.title }}
        </label>
    </div>
    <div v-else-if="props.option === 'text-area'" class="relative w-full grid border rounded focus-within:border-color-4" :class="{'border-red-500' : props.error }">
        <label class="motion-reduce:transition-none uppercase text-color-4 px-3 pt-4">
            {{ props.title }}
        </label>
        <textarea 
            v-bind="$attrs" 
            :value="props.modelValue" 
            @input="$emit('update:modelValue', $event.target.value)" 
            class="focus:outline-none h-auto w-full px-3 bg-white">
        </textarea>
    </div>
    <div v-else-if="props.option === 'date-picker'" class="relative w-full grid border rounded focus-within:border-color-4" :class="{'border-red-500' : props.error }">
        <label class="motion-reduce:transition-none uppercase text-color-4 px-3 pt-4">
            {{ props.title }}
        </label>
        <Date-Picker v-bind="$attrs" 
            :value="props.modelValue" 
            @input="$emit('update:modelValue', $event.target.value)" 
            class="focus:outline-none h-auto w-full px-3 bg-white" />
    </div>
    <div v-else class="input flex items-center bg-white text-color-4" :class="{'border-red-400 focus-within:border-red-400' : props.error }" >
        <Icon v-if="props.icon" :icon="props.icon" class="mr-2" />
        <input v-bind="$attrs" :value="props.modelValue" @input="$emit('update:modelValue', $event.target.value)"  class="focus:outline-none  disabled:bg-white text-lg w-full placeholder:text-color-4">
    </div>
</template>