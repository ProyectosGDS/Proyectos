<script setup>
    import { useAuthStore } from '@/stores/auth'
    import { useProfileStore } from '@/stores/profile'

    const auth = useAuthStore()
    const store = useProfileStore()

</script>

<template>
    <Card class="bg-white p-8">
        <div class="grid xl:grid-cols-2 xl:divide-x gap-4">
            <div v-if="auth.user.hasOwnProperty('dependencia')" class="grid gap-y-4 xl:pr-8">
                <h1 class="text-color-1 font-bold uppercase text-center text-2xl">Datos del usuario</h1>
                <Input v-model="auth.user.dependencia.nombre" option="label" title="Dependencia" readonly disabled />
                <Input v-model="auth.user.nombre" option="label" title="Usuario" readonly disabled />
                <Input v-model="auth.user.cui" option="label" title="cui" readonly disabled />
                <Input v-model="auth.user.perfil" option="label" title="perfil" readonly disabled />
            </div>
            
            <div class="grid gap-y-4 xl:pl-8">
                <h1 class="text-color-1 font-bold uppercase text-center text-2xl">Cambio de contraseña</h1>
                <div class="text-color-4">
                    <p>
                        Para efectuar con éxito eI cambio de contraseña, sirvase atender las siguientes recomendaciones:
                    </p>
                    <ul class="list-decimal list-inside">
                        <li>
                            Su nueva contraseña debe tener una longitud entre 8 y 15 caracteres
                        </li>
                        <li>
                            Por seguridad incluya mayúsculas, minúsculas, números y caracteres especiales.
                        </li>
                    </ul>
                </div>

                <Input v-model="store.user.old_password" option="label" title="Contraseña anterior" type="password" minlength="8" maxlength="15" :error="store.errors.hasOwnProperty('old_password')" />
                <Input v-model="store.user.new_password" option="label" title="Nueva contraseña" type="password" minlength="8" maxlength="15" :error="store.errors.hasOwnProperty('new_password')" />
                <Input v-model="store.user.new_password_confirmation" option="label" title="Confirme contraseña" type="password" minlength="8" maxlength="15" :error="store.errors.hasOwnProperty('new_password_confirmation')" />
                <Validate-Errors :errors="store.errors" v-if="store.errors != 0" />
                <div class="flex justify-end">
                    <Button @click="store.updatePassword" text="Actualizar contraseña" class="btn-danger w-56 text-nowrap" icon="fas fa-key" :loading="store.loading"/>
                </div>
            </div>
        </div>
    </Card>
</template>