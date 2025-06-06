<script setup>
    import { onBeforeMount} from 'vue'
    import {useMenusStore} from '@/stores/menus'
    import { useAuthStore } from '@/stores/auth'
    import { usePaginasStore } from '@/stores/paginas'

    const store = useMenusStore()
    const paginas = usePaginasStore()
    const auth = useAuthStore()


    function selected(item) {
        store.menu.paginas = item
    }

    onBeforeMount(() => {
        store.fetch()
        paginas.fetch()
    })

</script>

<template>
    <Card class="bg-white px-4 py-8">
        <div v-if="auth.checkPermission('crear menu')" class="flex justify-center">
            <Tool-Tip message="Nuevo menu" class="-mt-7 text-color-4">
                <Button @click="store.modal.new = true" icon="fas fa-plus" class="btn-primary" />
            </Tool-Tip>
        </div>
        <Data-Table v-if="auth.checkPermission('ver menus')" :headers="store.headers" :data="store.menus" :loading="store.loading.fetch" :excel="auth.checkPermission('exportar excel menus')">
            <template #actions="{item}">
                <Drop-Down-Button icon="fas fa-ellipsis-v" >
                    <ul>
                        <li v-if="auth.checkPermission('editar menu')" @click="store.edit(item)" class="text-color-4">Editar</li>
                        <li v-if="auth.checkPermission('eliminar menu')" @click="store.remove(item)" class="text-red-400">Eliminar</li>
                    </ul>
                </Drop-Down-Button>
            </template>
        </Data-Table>
    </Card>
    <Modal :open="store.modal.new" title="Crear menu" icon="fas fa-bars">
        <template #close>
            <Icon @click="store.resetData" icon="fas fa-xmark" class="cursor-pointer text-white" />
        </template>
        <div class="grid gap-4">
            <Input v-model="store.menu.nombre" option="label" title="*Nombre del menu" maxlength="80" :error="store.errors.hasOwnProperty('nombre')" />
            <hr>
            <h1 class="font-medium text-color-4 text-2xl">Asignación de páginas al menú</h1>
            <Data-Table :headers="paginas.headers" :data="paginas.paginas" :excel="false" :rowsPerPage="5" :multiSelect="true" @selectdAllItems="selected" />
        </div>
        <Validate-Errors :errors="store.errors" v-if="store.errors != 0" />
        <template #footer>
            <Button @click="store.resetData" text="Cancelar" icon="fas fa-xmark" class="btn-secondary" />
            <Button @click="store.store" text="Guardar" icon="fas fa-save" class="btn-primary" :loading="store.loading.store" />
        </template>
    </Modal>
    <Modal :open="store.modal.edit" title="Editar menu" icon="fas fa-bars">
        <template #close>
            <Icon @click="store.resetData" icon="fas fa-xmark" class="cursor-pointer text-white" />
        </template>
        <div class="grid gap-4">
            <Input v-model="store.menu.nombre" option="label" title="*Nombre del menu" maxlength="80" :error="store.errors.hasOwnProperty('nombre')" />
            <Data-Table :headers="paginas.headers" :data="paginas.paginas" :rowsPerPage="5" :excel="false" :multiSelect="true" @selectdAllItems="selected" :itemsSelected="store.menu.paginas" />
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
                <p class="text-center text-lg">¿Estás seguro de eliminar el menu?</p>
                <h1 class="text-center font-semibold">{{ store.menu.nombre }}</h1>
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