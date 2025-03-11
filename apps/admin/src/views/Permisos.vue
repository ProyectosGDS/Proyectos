<script setup>
    import { onBeforeMount } from 'vue'
    import {usePermisosStore} from '@/stores/permisos'
    import { useAuthStore } from '@/stores/auth'

    const store = usePermisosStore()
    const auth = useAuthStore()
    

    onBeforeMount(() => {
        store.fetch()
    })

</script>

<template>
    <Card class="bg-white px-4 py-8">
        <div v-if="auth.checkPermission('crear permiso')" class="flex justify-center">
            <Tool-Tip message="Nuevo permiso" class="-mt-7 text-color-4">
                <Button @click="store.modal.new = true" icon="fas fa-plus" class="btn-primary" />
            </Tool-Tip>
        </div>
        <Data-Table v-if="auth.checkPermission('ver permisos')" :headers="store.headers" :data="store.permisos" :loading="store.loading.fetch" :excel="auth.checkPermission('exportar permisos')">
            <template #actions="{item}">
                <Drop-Down-Button icon="fas fa-ellipsis-v" >
                    <ul>
                        <li v-if="auth.checkPermission('editar permiso')" @click="store.edit(item)" class="text-color-4">Editar</li>
                        <li v-if="auth.checkPermission('eliminar permiso')" @click="store.remove(item)" class="text-red-400">Eliminar</li>
                    </ul>
                </Drop-Down-Button>
            </template>
        </Data-Table>
    </Card>
    <Modal :open="store.modal.new" title="Crear permiso" icon="fas fa-user-shield">
        <template #close>
            <Icon @click="store.resetData" icon="fas fa-xmark" class="cursor-pointer text-white" />
        </template>
        <div class="grid gap-4">
            <Input v-model="store.permiso.nombre" option="label" title="*Nombre del permiso" maxlength="80" :error="store.errors.hasOwnProperty('nombre')" />
            <Input v-model="store.permiso.app" option="label" title="*Aplicación a la que pertenece" maxlength="80" :error="store.errors.hasOwnProperty('app')" />
            <Input v-model="store.permiso.grupo" option="label" title="*Grupo" maxlength="80" :error="store.errors.hasOwnProperty('grupo')" />
            <Input v-model="store.permiso.descripcion" option="text-area" title="*Descripción" maxlength="255" rows="6" :error="store.errors.hasOwnProperty('descripcion')" />
        </div>
        <Validate-Errors :errors="store.errors" v-if="store.errors != 0" />
        <template #footer>
            <Button @click="store.resetData" text="Cancelar" icon="fas fa-xmark" class="btn-secondary" />
            <Button @click="store.store" text="Guardar" icon="fas fa-save" class="btn-primary" :loading="store.loading.store" />
        </template>
    </Modal>
    <Modal :open="store.modal.edit" title="Editar permiso" icon="fas fa-user-shield">
        <template #close>
            <Icon @click="store.resetData" icon="fas fa-xmark" class="cursor-pointer text-white" />
        </template>
        <div class="grid gap-4">
            <Input v-model="store.permiso.nombre" option="label" title="*Nombre del permiso" maxlength="80" :error="store.errors.hasOwnProperty('nombre')" />
            <Input v-model="store.permiso.app" option="label" title="*Aplicación a la que pertenece" maxlength="80" :error="store.errors.hasOwnProperty('app')" />
            <Input v-model="store.permiso.grupo" option="label" title="*Grupo" maxlength="80" :error="store.errors.hasOwnProperty('grupo')" />
            <Input v-model="store.permiso.descripcion" option="text-area" title="*Descripción" maxlength="255" rows="6" :error="store.errors.hasOwnProperty('descripcion')" />
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
                <p class="text-center text-lg">¿Estás seguro de eliminar el permiso?</p>
                <h1 class="text-center font-semibold">{{ store.permiso.nombre }}</h1>
            </div>
        </div>
        <template #footer>
            <Button @click="store.resetData" text="Cancelar" icon="fas fa-xmark" class="btn-secondary" />
            <Button @click="store.destroy" text="Sí, eliminar" icon="fas fa-trash" class="btn-danger" :loading="store.loading.destroy" />
        </template>
    </Modal>
</template>

<style scoped>
    li {
        @apply cursor-pointer text-xs text-nowrap hover:bg-slate-100 py-1 px-3 rounded-lg;
    }
</style>