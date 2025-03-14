<script setup>
    import { ref } from 'vue';
    import LoadingBar from './LoadingBar.vue'

    const emit = defineEmits(['sendFile'])

    const props = defineProps({
        icon : {
            default : 'fas fa-upload'
        },
        accept : {
            default : '*'
        },
        processing : {
            default : false
        },
        success : {
            default : false
        }

    })


    const file = ref({
        success : false ,
        name : 'No hay archivo seleccionado',
        size : 0,
        progress : 0,
    })

    const fileInputRef = ref(null);


    const handleFileChange = (event) => {
        
        const files = event.target.files[0]

        console.log(files)
        
        file.value.name = files.name
        file.value.size = Math.round(files.size/1024)
        
        if (files) {
            const progressHandler = (event) => {
                file.value.progress = Math.round((event.progress) * 100)
                console.log(event)
            }
            
            emit('sendFile',files,progressHandler)

        }
        file.value.success = true
    }

    function clearFile () {
        file.value = {
            success: false,
            name: 'No hay archivo seleccionado',
            size: 0,
            progress : 0,
        }

        fileInputRef.value.value = ''
    }


</script>

<template>
    <div class="grid relative hover:scale-110">
        <label class="grid text-gray-300 justify-items-center cursor-pointer">
            <div class="flex justify-center">
                <Icon icon="fas fa-file" class="aspect-square absolute h-[100%]" :class="{'text-green-200' : props.success}"/>
                <Icon v-if="props.success" icon="fas fa-check" class="text-green-400 animate-rotate-y absolute aspect-square h-[40%] top-1/3"/>
                <Icon v-if="!props.success" icon="fas fa-arrow-up-from-bracket" class="text-gray-100 animate-bounce absolute aspect-square h-[35%] top-[40%]"/>
                <Icon v-if="props.processing" icon="fas fa-arrows-rotate" class="text-gray-100 animate-spin absolute aspect-square h-[35%] top-1/3"/>
            </div>
            <input ref="fileInputRef" @change="handleFileChange" type="file" :accept="props.accept" hidden :disabled="props.processing || file.success">
            <LoadingBar v-if="props.processing" class="absolute -bottom-2 bg-green-500"/>
        </label>
        <Icon v-if="props.success" @click="clearFile" icon="fas fa-circle-xmark" class="text-red-500 absolute hover:scale-110 aspect-square h-[25%] top-0 right-0"/>

    </div>

</template>