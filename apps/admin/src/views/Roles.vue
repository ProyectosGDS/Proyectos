<script setup>
    import { onBeforeMount, computed, ref } from 'vue'
    import {useRolesStore} from '@/stores/roles'
    import { useAuthStore } from '@/stores/auth'
    import { usePermisosStore } from '@/stores/permisos'

    const store = useRolesStore()
    const permisos = usePermisosStore()
    const auth = useAuthStore()
    
    const grupoPermisos = computed(() => {
        return permisos.permisos.reduce((acc, permiso) => {
            if (!acc[permiso.grupo]) {
                acc[permiso.grupo] = []
            }
            acc[permiso.grupo].push(permiso)
            return acc
        }, {})
    })

    const permisosSeleccionados = ref([])

    function selected(item) {
        store.rol.permisos = item
    }

    onBeforeMount(() => {
        store.fetch()
        permisos.fetch()
    })

</script>

<template>
    <Card class="bg-white px-4 py-8">
        <div v-if="auth.checkPermission('crear rol')" class="flex justify-center">
            <Tool-Tip message="Nuevo rol" class="-mt-7 text-color-4">
                <Button @click="store.modal.new = true" icon="fas fa-plus" class="btn-primary" />
            </Tool-Tip>
        </div>
        <Data-Table v-if="auth.checkPermission('ver roles')" :headers="store.headers" :data="store.roles" :loading="store.loading.fetch" :excel="auth.checkPermission('exportar roles')">
            <template #actions="{item}">
                <Drop-Down-Button icon="fas fa-ellipsis-v" >
                    <ul>
                        <li v-if="auth.checkPermission('editar rol')" @click="store.edit(item)" class="text-color-4">Editar</li>
                        <li v-if="auth.checkPermission('eliminar rol')" @click="store.remove(item)" class="text-red-400">Eliminar</li>
                    </ul>
                </Drop-Down-Button>
            </template>
        </Data-Table>
    </Card>
    <Modal :open="store.modal.new" title="Crear rol" icon="fas fa-tag">
        <template #close>
            <Icon @click="store.resetData" icon="fas fa-xmark" class="cursor-pointer text-white" />
        </template>
        <div class="grid gap-4">
            <Input v-model="store.rol.nombre" option="label" title="*Nombre del rol" maxlength="80" :error="store.errors.hasOwnProperty('nombre')" />
            <Input v-model="store.rol.descripcion" option="text-area" title="*Descripción" maxlength="255" rows="6" :error="store.errors.hasOwnProperty('descripcion')" />
            <Data-Table :headers="permisos.headers" :data="permisos.permisos" :excel="false" :rowsPerPage="5" :multiSelect="true" @selectdAllItems="selected" />
        </div>
        <Validate-Errors :errors="store.errors" v-if="store.errors != 0" />
        <template #footer>
            <Button @click="store.resetData" text="Cancelar" icon="fas fa-xmark" class="btn-secondary" />
            <Button @click="store.store" text="Guardar" icon="fas fa-save" class="btn-primary" :loading="store.loading.store" />
        </template>
    </Modal>
    <Modal :open="store.modal.edit" title="Editar rol" icon="fas fa-tag">
        <template #close>
            <Icon @click="store.resetData" icon="fas fa-xmark" class="cursor-pointer text-white" />
        </template>
        <div class="grid gap-4">
            <Input v-model="store.rol.nombre" option="label" title="*Nombre del rol" maxlength="80" :error="store.errors.hasOwnProperty('nombre')" />
            <Input v-model="store.rol.descripcion" option="text-area" title="*Descripción" maxlength="255" rows="6" :error="store.errors.hasOwnProperty('descripcion')" />
            <Data-Table :headers="permisos.headers" :data="permisos.permisos" :rowsPerPage="5" :excel="false" :multiSelect="true" @selectdAllItems="selected" :itemsSelected="store.rol.permisos" />
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
                <p class="text-center text-lg">¿Estás seguro de eliminar el rol?</p>
                <h1 class="text-center font-semibold">{{ store.rol.nombre }}</h1>
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