<script setup>
    import Logo from '@/components/Logo.vue'
    import { useGlobalStore } from '@/stores/global'
    import { useAuthStore } from '@/stores/auth'

    const global = useGlobalStore()
    const auth = useAuthStore()


</script>

<template>
    
    <div class="grid md:grid-cols-2 bg-color-4 text-color-1 h-screen relative overflow-hidden bg-img bg-cover bg-center bg-no-repeat">
        <div class="flex justify-center items-center h-screen bg-color-1 z-10 rounded-br-[8rem]">
            <form @submit.prevent="auth.login()" class="grid gap-4 ">
                <Logo class="h-20 w-auto fill-white" />
                <hr class="border-color-4">
                <Input v-model="auth.user.cui" icon="fas fa-id-card-clip" class="h-12" placeholder="Ingrese dpi" type="text" maxlength="13" minlength="13" required :error="auth.errors.hasOwnProperty('cui')" />
                <Input v-model="auth.user.password" icon="fas fa-key" class="h-12" placeholder="Password" type="password" maxlength="16" minlength="8" required :error="auth.errors.hasOwnProperty('password')" />
                <Button icon="fas fa-door-closed" class="btn-secondary rounded-full" text="Ingresar" :loading="auth.loading"/>
                <hr class="border-color-4">
                <p class="text-color-4 text-xs text-center">Municipalidad de Guatemala {{ global.date.getFullYear() }}</p>
                <Validate-Errors :errors="auth.errors" v-if="auth.errors != 0" />
            </form>
        </div>
        <div>
        </div>
    </div>
    
</template>

<style scoped>

    .bg-img {
        background-image: url('../assets/img/muni.jpg');
    }

</style>
