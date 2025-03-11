<script setup>
import { computed, ref, onBeforeMount, nextTick, watchEffect } from 'vue'
import { onClickOutside } from '@vueuse/core'

    const props = defineProps({
        items: {
            type: Array,
            default: () => []
        },
        fields : {
            type : Array,
            default : () => ['id','nombre']
        },
        format : null,
        modelValue: null,
        title : '',
        error : {
            type : Boolean,
            default : false
        },
    })

    const emits = defineEmits(['update:modelValue'])

    const data = computed(() => 
        props.items.filter(item => 
            item[props.fields[1]].toLowerCase().match(search.value.toLocaleLowerCase())
        )
    )

    const search = ref('')
    const item = ref({})
    const target = ref(null)
    const open = ref(false)
    const focusedIndex = ref(-1)
    const listRefs = ref([])
    const dropdownPosition = ref('bottom');

    function selected(val) {
        item.value = val
        open.value = false
        search.value = ''
        emits('update:modelValue', item.value[props.fields[0]])
    }

    const displayValue = computed(() => {
        if (item.value[props.fields[0]]) {
            try {
                return new Function('item', `return ${props.format}`)(item.value)
            } catch (error) {
                console.error('Error al evaluar el formato:', error)
                return ''
            }
        }
        return ''
    })

    const displayValueList = (item) => {
        try {
            return new Function('item', `return ${props.format}`)(item)
        } catch (error) {
            console.error('Error al evaluar el formato:', error)
            return ''
        }
    }

    function clearSelection() {
        item.value = {}
        emits('update:modelValue', '')
    }

    onClickOutside(target, () => open.value = false)


    function handleKeydown(event) {
        if (!open.value) return
        
        if (event.key === 'ArrowDown') {
        
            focusedIndex.value = (focusedIndex.value + 1) % data.value.length
        } else if (event.key === 'ArrowUp') {
        
            focusedIndex.value = (focusedIndex.value - 1 + data.value.length) % data.value.length
        } else if (event.key === 'Enter' || event.key === 'Tab') {
        
            if (focusedIndex.value >= 0 && focusedIndex.value < data.value.length) {
                selected(data.value[focusedIndex.value])
            }
        } else if (event.key ==='Escape') {
            open.value = false
        }


    
        nextTick(() => scrollIntoView())
    }


    function scrollIntoView() {
        const currentItem = listRefs.value[focusedIndex.value]
        if (currentItem) {
            currentItem.scrollIntoView({ behavior: 'smooth', block: 'nearest' })
        }
    }


    function openDropdown() {
        open.value = true
        focusedIndex.value = -1
    }


    watchEffect(async () => {

        if(props.modelValue == '') {
            item.value = {}
        }

        if (open.value) {
            nextTick(() => {
                const dropdown = target.value.getBoundingClientRect();
                const viewportHeight = window.innerHeight;

                if (dropdown.bottom + 230 > viewportHeight) {
                    dropdownPosition.value = 'top';
                } else {
                    dropdownPosition.value = 'bottom';
                }
            });
        }

       
        
    })
    
    onBeforeMount(() => {
        
        if(props.modelValue != '') {
            let objeto = {}
            objeto = props.items.filter(item => item[props.fields[0]] == props.modelValue)            
            item.value = JSON.parse(JSON.stringify(objeto))[0]
        }

        document.addEventListener('keydown', handleKeydown)
    })

</script>

<template>
    <div v-if="props.fields.length > 0" ref="target" class="w-full relative">
        <div class="flex items-center gap-1">
            <input
                placeholder="example" :value="props.format ? displayValue : (item ? item[props.fields[1]] : '')" readonly @click="openDropdown" @focus="openDropdown"
                class="peer m-0 uppercase block bg-white w-full rounded border focus-within:border-color-4 bg-transparent bg-clip-padding px-3 py-3.5 text-base font-normal leading-tight text-neutral-700 transition duration-200 ease-linear placeholder:text-transparent focus:border-primary focus:pb-[0.625rem] focus:pt-[1.625rem] focus:text-neutral-700 focus:outline-none peer-focus:text-primary [&:not(:placeholder-shown)]:pb-[0.625rem] [&:not(:placeholder-shown)]:pt-[1.625rem]"
                :class="{'border-red-500' : props.error }"
                />
            <Icon icon="fas fa-xmark" v-if="item && item[props.fields[0]]" @click="clearSelection" class="text-red-400 cursor-pointer animate-jump" />
            <label
                class="pointer-events-none absolute left-0 top-0 origin-[0_0] border border-solid border-transparent px-3 py-3.5 text-color-4 transition-[opacity,_transform] duration-200 ease-linear peer-focus:-translate-y-2 peer-focus:translate-x-[0.15rem] peer-focus:scale-[0.85] peer-focus:text-gray-400 peer-[:not(:placeholder-shown)]:-translate-y-2 peer-[:not(:placeholder-shown)]:translate-x-[0.15rem] peer-[:not(:placeholder-shown)]:scale-[0.85] motion-reduce:transition-none uppercase">
                {{ props.title }}
            </label>
        </div>
        <div :class="`absolute bg-white border p-4 mt-1 rounded border-color-4 z-30 ${dropdownPosition === 'top' ? 'bottom-full mb-1' : 'top-full mt-1'}`" v-show="open">
            <input v-if="props.items.length > 5" tabindex="0" icon="search" type="search" placeholder="Buscar elemento ..." v-model="search" class="input h-10 focus:outline-none">
            <ul class="h-auto max-h-48 overflow-auto">
                <template v-for="(item, index) in data">
                    <li v-if="!item.deleted_at" @click="selected(item)" ref="listRefs" class="text-xs"
                        :class="{'cursor-pointer uppercase hover:bg-violet-50 p-2 text-gray-500': true, 'bg-violet-100': index === focusedIndex }">
                        {{ props.format ? displayValueList(item) : item[props.fields[1]] }}
                    </li>
                </template>
            </ul>
        </div>
    </div>
</template>

<style scoped>
.bottom-full {
    bottom: 100%;
}
.top-full {
    top: 100%;
}
</style>