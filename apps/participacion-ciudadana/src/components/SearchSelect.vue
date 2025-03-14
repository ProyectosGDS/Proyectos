<script setup>

import { ref, watchEffect } from 'vue'

const props = defineProps({
    items: {
        type: Array,
        default: []
    },
    title : {
        type : String,
        default : ''
    },
    modelValue: '',
    unique : {
        type : String,
        default : null
    },
    field : {
        type : String,
        default : 'id'
    },
    error : {
        type : Boolean,
        default : false
    },
    multi_fields : {
        type : Boolean,
        default : false
    }

})

const emit = defineEmits(['update:modelValue'])

const searchQuery = ref('')

const onInput = (event) => {
    let selected = {}
    if(props.multi_fields){
        selected = props.items.find(item => `${item.dia} ${item.hora_inicial} - ${item.hora_final}` === event.target.value)
    }else {
        selected = props.items.find(item => item[props.field] === event.target.value)
    }

     emit('update:modelValue', selected ? selected.id : '')    
}

watchEffect(() => {
    if(props.modelValue == '') {
        searchQuery.value = props.modelValue
    }
})

</script>

<template>
    <div class="relative mb-3 w-full">
        <input type="search" 
            :class="{'border-red-500' : props.error }" 
            :list="props.unique" 
            v-model="searchQuery" 
            @input="onInput" 
            class="peer m-0 block h-[59px] bg-white w-full rounded border focus-within:border-violet-400 bg-transparent bg-clip-padding px-3 py-4 text-base font-normal leading-tight text-gray-700 transition duration-200 ease-linear placeholder:text-transparent focus:border-primary focus:pb-[0.625rem] focus:pt-[1.625rem] focus:text-neutral-700 focus:outline-none peer-focus:text-primary [&:not(:placeholder-shown)]:pb-[0.625rem] [&:not(:placeholder-shown)]:pt-[1.625rem]" 
        />
        <datalist :id="props.unique">
            <template v-if="multi_fields">
                <template v-for="item in props.items" :key="item.id">
                    <option v-if="!item.deleted_at" :value="`${item.dia} ${item.hora_inicial} - ${item.hora_final}`"></option>
                </template>
            </template>
            <template v-else>
                <template v-for="item in props.items" :key="item.id">
                    <option v-if="!item.deleted_at" :value="item[props.field]"></option>
                </template>
            </template>
        </datalist>
        <label
            class="pointer-events-none absolute left-0 top-0 origin-[0_0] border border-solid border-transparent px-3 py-4 text-violet-500 transition-[opacity,_transform] duration-200 ease-linear peer-focus:-translate-y-2 peer-focus:translate-x-[0.15rem] peer-focus:scale-[0.85] peer-focus:text-violet-300 peer-[:not(:placeholder-shown)]:-translate-y-2 peer-[:not(:placeholder-shown)]:translate-x-[0.15rem] peer-[:not(:placeholder-shown)]:scale-[0.85] motion-reduce:transition-none uppercase">
            {{ props.title }}
        </label>
    </div>
</template>