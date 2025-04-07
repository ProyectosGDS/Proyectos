<script setup>
    import { onBeforeMount } from 'vue'
    import {useUsuariosStore} from '@/stores/usuarios'
    import { useCatalogosStore } from '@/stores/catalogos'
    import { useAuthStore } from '@/stores/auth'

    const store = useUsuariosStore()
    const catalogos = useCatalogosStore()
    const auth = useAuthStore()

    const date = new Date()
    const now = date.getFullYear() + '-' + (date.getMonth() + 1) + '-' + date.getDate()
    
    onBeforeMount(() => {
        store.fetch()
        catalogos.fetchDependencias()
        catalogos.fetchPerfiles()
    })

</script>

<template>
    <Card class="bg-white px-4 py-8">
        <div v-if="auth.checkPermission('crear usuario')" class="flex justify-center">
            <Tool-Tip message="Nuevo usuario" class="-mt-7 text-color-4">
                <Button @click="store.modal.new = true" icon="fas fa-plus" class="btn-primary" />
            </Tool-Tip>
        </div>
        <Data-Table v-if="auth.checkPermission('ver usuarios')" :headers="store.headers" :data="store.usuarios" :loading="store.loading.fetch" :excel="auth.checkPermission('exportar usuarios')">
            <template #deleted_at="{item}">
                <Icon :icon="item.deleted_at ? 'fas fa-xmark' : 'fas fa-check'" :class="item.deleted_at ? 'text-red-500' : 'text-green-500'" />
            </template>
            <template #actions="{item}">
                <Drop-Down-Button icon="fas fa-ellipsis-v" >
                    <ul>
                        <li v-if="auth.checkPermission('editar usuario')" @click="store.edit(item)" class="text-color-4">Editar</li>
                        <li v-if="auth.checkPermission('reiniciar contraseña')" @click="store.resetPass(item)" class="text-color-4">Reiniciar contraseña</li>
                        <li v-if="auth.checkPermission('eliminar usuario')" @click="store.remove(item)" class="text-red-400">Desactivar</li>
                    </ul>
                </Drop-Down-Button>
            </template>
        </Data-Table>
    </Card>

    <!-- MODALES -->

    <Modal :open="store.modal.new" title="Crear usuario" icon="fas fa-user-plus">
        <template #close>
            <Icon @click="store.resetData" icon="fas fa-xmark" class="cursor-pointer text-white" />
        </template>
        <div class="grid gap-4">
            <Input v-model="store.usuario.cui" option="label" title="*cui" maxlength="13" :error="store.errors.hasOwnProperty('cui')" />
            <Input v-model="store.usuario.nombre" option="label" title="*Nombre del usuario completo" maxlength="100" :error="store.errors.hasOwnProperty('nombre')" />
            <Input v-model="store.usuario.dependencia_id" option="select" title="*Dependencias" :error="store.errors.hasOwnProperty('dependencia_id')">
                <option value=""></option>
                <option v-for="dependencia in catalogos.dependencias" :value="dependencia.id">{{ dependencia.nombre }}</option>
            </Input>
            <Input v-model="store.usuario.perfil_id" option="select" title="perfiles" :error="store.errors.hasOwnProperty('perfil_id')">
                <option value=""></option>
                <option v-for="perfil in catalogos.perfiles" :value="perfil.id">{{ perfil.nombre }}</option>
            </Input>
        </div>
        <Validate-Errors :errors="store.errors" v-if="store.errors != 0" />
        <template #footer>
            <Button @click="store.resetData" text="Cancelar" icon="fas fa-xmark" class="btn-secondary" />
            <Button @click="store.store" text="Guardar" icon="fas fa-save" class="btn-primary" :loading="store.loading.store" />
        </template>
    </Modal>

    <Modal :open="store.modal.edit" title="Editar usuario" icon="fas fa-user-edit">
        <template #close>
            <Icon @click="store.resetData" icon="fas fa-xmark" class="cursor-pointer text-white" />
        </template>
        <div class="grid gap-4">
            <div v-if="store.usuario.deleted_at != null" class="flex justify-end">
                <div class="flex items-center gap-2">
                    <span class="text-sm text-gray-500">Activar</span>
                    <Switch v-model="store.usuario.deleted_at" class="h-auto w-14 bg-red-400 has-[:checked]:bg-green-500" :values="[null,now]" />
                    <span class="text-sm text-gray-500">Inactivo</span>
                </div>
            </div>
            <Input v-model="store.usuario.cui" option="label" title="cui" maxlength="13" :error="store.errors.hasOwnProperty('cui')" />
            <Input v-model="store.usuario.nombre" option="label" title="Nombre del usuario completo" maxlength="100" :error="store.errors.hasOwnProperty('nombre')" />
            <Input v-model="store.usuario.dependencia_id" option="select" title="Dependencias" :error="store.errors.hasOwnProperty('dependencia_id')">
                <option value=""></option>
                <option v-for="dependencia in catalogos.dependencias" :value="dependencia.id">{{ dependencia.nombre }}</option>
            </Input>
            <Input v-model="store.usuario.perfil_id" option="select" title="perfiles" :error="store.errors.hasOwnProperty('perfil_id')">
                <option value=""></option>
                <option v-for="perfil in catalogos.perfiles" :value="perfil.id">{{ perfil.nombre }}</option>
            </Input>
        </div>
        <Validate-Errors :errors="store.errors" v-if="store.errors != 0" />
        <template #footer>
            <Button @click="store.resetData" text="Cancelar" icon="fas fa-xmark" class="btn-secondary" />
            <Button @click="store.update" text="Actualizar" icon="fas fa-arrows-rotate" class="btn-primary" :loading="store.loading.update" />
        </template>
    </Modal>

    <Modal :open="store.modal.delete">
        <div class="flex items-center justify-center gap-4">
            <Icon icon="fas fa-exclamation-triangle" class="text-orange-500 text-5xl" />
            <div>
                <p class="text-center text-lg">¿Estás seguro de desactivar el usuario?</p>
                <h1 class="text-center font-semibold">{{ store.usuario.nombre }}</h1>
            </div>
        </div>
        <template #footer>
            <Button @click="store.resetData" text="Cancelar" icon="fas fa-xmark" class="btn-secondary" />
            <Button @click="store.destroy" text="Sí, desactivar" icon="fas fa-trash" class="btn-danger" :loading="store.loading.destroy" />
        </template>
    </Modal>

    <Modal :open="store.modal.resetPassword">
        <div class="flex items-center justify-center gap-4">
            <Icon icon="fas fa-exclamation-triangle" class="text-orange-500 text-5xl" />
            <div>
                <p class="text-center text-lg">¿Estás seguro de reinciar la contraseña del usuario?</p>
                <h1 class="text-center font-semibold">{{ store.usuario.nombre }}</h1>
            </div>
        </div>
        <template #footer>
            <Button @click="store.resetData" text="Cancelar" icon="fas fa-xmark" class="btn-secondary" />
            <Button @click="store.resetPassword" text="Sí, reiniciar" icon="fas fa-trash" class="btn-danger" :loading="store.loading.destroy" />
        </template>
    </Modal>
</template>

<style scoped>
    li {
        @apply cursor-pointer text-xs text-nowrap hover:bg-slate-100 py-1 px-3 rounded-lg;
    }
</style>