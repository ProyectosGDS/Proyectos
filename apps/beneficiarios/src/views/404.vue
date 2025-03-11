<script setup>
    import { useGlobalStore } from '@/stores/global'
    const global = useGlobalStore()
</script>

<template>
    <div class="h-screen grid lg:grid-cols-2 relative overflow-hidden bg-color-4 bg-img bg-cover bg-center bg-no-repeat">
        <div class="bg-color-1 flex flex-col items-center justify-center h-screen ">
            <h1 class=" text-9xl text-color-4 font-black">404</h1>
            <h1 class="text-3xl sm:text-5xl font-bold text-color-4 drop-shadow-2xl">
                Página no econtrada
            </h1>
            <Button @click="global.goHome()" text="Regresar" icon="fas fa-arrow-left" class="btn-secondary rounded-full" />
        </div>
        <div class="z-10 flex justify-center items-center">
        </div>
    </div>
</template>

<style scoped>

    .bg-img {
        background-image: url('../assets/img/muni.jpg');
    }

</style>