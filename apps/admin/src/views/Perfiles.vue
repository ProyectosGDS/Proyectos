<script setup>
    import { onBeforeMount } from 'vue'
    import {usePerfilesStore} from '@/stores/perfiles'
    import { useAuthStore } from '@/stores/auth'
    import { useRolesStore } from '@/stores/roles'
    import { useMenusStore } from '@/stores/menus'


    const store = usePerfilesStore()
    const roles = useRolesStore()
    const menus = useMenusStore()
    const auth = useAuthStore()
    

    onBeforeMount(() => {
        store.fetch()
        roles.fetch()
        menus.fetch()
    })

</script>

<template>
    <Card class="bg-white px-4 py-8">
        <div v-if="auth.checkPermission('crear perfil')" class="flex justify-center">
            <Tool-Tip message="Nuevo perfil" class="-mt-7 text-color-4">
                <Button @click="store.modal.new = true" icon="fas fa-plus" class="btn-primary" />
            </Tool-Tip>
        </div>
        <Data-Table v-if="auth.checkPermission('ver perfiles')" :headers="store.headers" :data="store.perfiles" :loading="store.loading.fetch" :excel="auth.checkPermission('exportar perfiles')">
            <template #actions="{item}">
                <Drop-Down-Button icon="fas fa-ellipsis-v" >
                    <ul>
                        <li v-if="auth.checkPermission('editar perfil')" @click="store.edit(item)" class="text-color-4">Editar</li>
                        <li v-if="auth.checkPermission('eliminar perfil')" @click="store.remove(item)" class="text-red-400">Eliminar</li>
                    </ul>
                </Drop-Down-Button>
            </template>
        </Data-Table>
    </Card>
    <Modal :open="store.modal.new" title="Crear perfil" icon="fas fa-clipboard-user">
        <template #close>
            <Icon @click="store.resetData" icon="fas fa-xmark" class="cursor-pointer text-white" />
        </template>
        <div class="grid gap-4">
            <Input v-model="store.perfil.nombre" option="label" title="*Nombre del perfil" maxlength="80" :error="store.errors.hasOwnProperty('nombre')" />
            <Input v-model="store.perfil.rol_id" option="select" title="*Roles" :error="store.errors.hasOwnProperty('rol_id')">
                <option value=""></option>
                <option v-for="rol in roles.roles" :value="rol.id">{{ rol.nombre }}</option>
            </Input>
            <Input v-model="store.perfil.menu_id" option="select" title="*Menus" :error="store.errors.hasOwnProperty('menu_id')">
                <option value=""></option>
                <option v-for="menu in menus.menus" :value="menu.id">{{ menu.nombre }}</option>
            </Input>
            <Input v-model="store.perfil.descripcion" option="text-area" title="*Descripción" maxlength="255" rows="6" :error="store.errors.hasOwnProperty('descripcion')" />
        </div>
        <Validate-Errors :errors="store.errors" v-if="store.errors != 0" />
        <template #footer>
            <Button @click="store.resetData" text="Cancelar" icon="fas fa-xmark" class="btn-secondary" />
            <Button @click="store.store" text="Guardar" icon="fas fa-save" class="btn-primary" :loading="store.loading.store" />
        </template>
    </Modal>
    <Modal :open="store.modal.edit" title="Editar perfil" icon="fas fa-clipboard-user">
        <template #close>
            <Icon @click="store.resetData" icon="fas fa-xmark" class="cursor-pointer text-white" />
        </template>
        <div class="grid gap-4">
            <Input v-model="store.perfil.nombre" option="label" title="*Nombre del perfil" maxlength="80" :error="store.errors.hasOwnProperty('nombre')" />
            <Input v-model="store.perfil.rol_id" option="select" title="*Roles" :error="store.errors.hasOwnProperty('rol_id')">
                <option value=""></option>
                <option v-for="rol in roles.roles" :value="rol.id">{{ rol.nombre }}</option>
            </Input>
            <Input v-model="store.perfil.menu_id" option="select" title="*Menus" :error="store.errors.hasOwnProperty('menu_id')">
                <option value=""></option>
                <option v-for="menu in menus.menus" :value="menu.id">{{ menu.nombre }}</option>
            </Input>
            <Input v-model="store.perfil.descripcion" option="text-area" title="*Descripción" maxlength="255" rows="6" :error="store.errors.hasOwnProperty('descripcion')" />
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
                <p class="text-center text-lg">¿Estás seguro de eliminar el perfil?</p>
                <h1 class="text-center font-semibold">{{ store.perfil.nombre }}</h1>
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