<script setup>
    import { ref, onMounted, watch } from 'vue'

    const props = defineProps(['user'])
    const exist = ref(false)

    function verificarImagen(){

        var img = new Image()
        img.src = 'https://udicat.muniguate.com/catastro/GestionServicios/'+props.user?.image

        img.onload = function() {
            exist.value = true
        }
        
        img.onerror = function() {
            exist.value = false
        }
    }


    watch(()=> props.user?.image, () => {
        verificarImagen()
    })
    
    onMounted( () => {
        verificarImagen()
    })
    


</script>

<template>
    <img v-if="exist" :src="'https://udicat.muniguate.com/catastro/GestionServicios/'+props.user?.image" class="rounded-full bg-lime-600 object-cover" >
    <span v-else class="text-blue-muni font-bold text-2xl rounded-full overflow-hidden bg-lime-muni flex justify-center items-center">{{ user?.inicial }}</span>
</template>