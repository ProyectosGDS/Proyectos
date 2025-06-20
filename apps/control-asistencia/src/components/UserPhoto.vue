<script setup>
    import { ref, onMounted, watch } from 'vue'

    const props = defineProps(['user'])
    const exist = ref(false)

    function verificarImagen(){

        var img = new Image()
        img.src = props.user?.urlImage

        img.onload = function() {
            exist.value = true
        }
        
        img.onerror = function() {
            exist.value = false
        }
    }


    watch(()=> props.user?.urlImage, () => {
        verificarImagen()
    })
    
    onMounted( () => {
        verificarImagen()
    })
    


</script>

<template>
    <img v-if="exist" :src="props.user?.urlImage" class="rounded-full bg-color-1 object-cover" >
    <span v-else class="text-color-1 font-bold text-xl rounded-full overflow-hidden bg-color-4 flex justify-center items-center">
        <Icon icon="fas fa-user" />
    </span>
</template>