<script setup>

    import { ref, onMounted } from 'vue'
    import { useGlobalStore } from '../stores/global'

    const global = useGlobalStore()


    const props = defineProps({
        message : {
            type : String,
            default : ''
        },
        type : {
            type : String,
            default : ''
        },
        title : {
            type : String,
            default : ' A T E N C I Ó N '
        },
    })


    let open = ref(true)
    
    onMounted(() => {
        setTimeout( () => {
            global.toasts.shift()
        }, 4000 )        
    })


</script>

<template>
    <div v-if="open"
        :class="{
            'bg-blue-500'   : props.type === 'primary',
            'bg-green-500'  : props.type === 'success',
            'bg-red-500'    : props.type === 'danger',
            'bg-yellow-500' : props.type === 'warning',
            'bg-gray-800' : props.type === 'dark',
        }"
        class="shadow-lg mx-auto w-96 max-w-full text-sm bg-clip-padding rounded block mb-3 bg-opacity-80 border border-gray-200">
        
        <div class="flex font-bold text-white justify-between items-center py-2 px-3 bg-clip-padding border-b border-gray-300 rounded-t">
            
            <Icon v-if="props.type === 'primary'" icon="fa-solid fa-circle-check" class="text-xl"/>
            <Icon v-else-if="props.type === 'dark'" icon="fa-solid fa-circle-check" class="text-xl"/>
            <Icon v-else-if="props.type === 'success'" icon="fa-solid fa-circle-check" class="text-xl"/>
            <Icon v-else-if="props.type === 'danger'" icon="fa-solid fa-circle-xmark" class="text-xl"/>
            <Icon v-else="props.type === 'warning'" icon="fa-solid fa-triangle-exclamation" class="text-xl"/>

            <p>{{ props.title }}</p>

            <Icon @click="open=false" icon="fas fa-xmark" class="hover:scale-125 cursor-pointer" />
        </div>

        <div class="p-3 rounded-b break-words  text-justify"
            :class="{
                'bg-blue-100 text-blue-500'   : props.type === 'primary',
                'bg-green-100 text-green-500'  : props.type === 'success',
                'bg-red-100 text-red-500'    : props.type === 'danger',
                'bg-yellow-50 text-yellow-600' : props.type === 'warning',
                'bg-gray-100 text-gray-800' : props.type === 'dark',
            }">
            {{ props.message }}
        </div>
    </div>
</template>