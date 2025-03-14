<script setup>
    import { useRouter } from 'vue-router'

    const router = useRouter()
</script>

<template>
    <div class="h-screen grid lg:grid-cols-2 relative overflow-hidden bg-[#83A6CE] bg-img bg-cover bg-center bg-no-repeat">
        <div class="bg-[#273385] flex flex-col items-center justify-center h-screen">
            <h1 class=" text-9xl text-[#83A6CE] font-black">404</h1>
            <h1 class="text-3xl sm:text-5xl font-bold text-[#83A6CE] drop-shadow-2xl">
                Página no econtrada
            </h1>
            <Button @click="router.push({ name : 'Home' })" text="Regresar" icon="fas fa-arrow-left" class="btn-secondary rounded-full" />
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