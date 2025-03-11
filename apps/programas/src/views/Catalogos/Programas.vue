<script setup>
    import { useProgramasStore } from '@/stores/Catalogos/programas'
    import { useAuthStore } from '@/stores/auth'
    import { useCatalogosStore } from '@/stores/Catalogos/catalogos'
    import { onBeforeMount } from 'vue'

    const store = useProgramasStore()
    const auth = useAuthStore()
    const catalogos = useCatalogosStore()

    onBeforeMount(() => {
        store.fetch()
        catalogos.getDependencias()
    })

</script>

<template>
    <Card class="bg-white px-4 py-8">
        <div v-if="auth.checkPermission('crear programa')" class="flex justify-center">
            <Tool-Tip message="Nuevo programa" class="-mt-7 text-color-4">
                <Button @click="store.modal.new = true" icon="fas fa-plus" class="btn-primary" />
            </Tool-Tip>
        </div>
        <Data-Table v-if="auth.checkPermission('ver programas')" :headers="store.headers" :data="store.programas" :loading="store.loading.fetch" :excel="auth.checkPermission('exportar excel programas')">
            <template #estado="{item}">
                <Badge :color="item.estado == 'A' ? 'green' : 'red'" :text="item.estado == 'A' ? 'Activo' : 'Inactivo'" />
            </template>
            <template #actions="{item}">
                <Drop-Down-Button icon="fas fa-ellipsis-v" >
                    <ul>
                        <li v-if="auth.checkPermission('editar programa')" @click="store.edit(item)" class="text-color-4">Editar</li>
                        <template v-if="item.estado == 'A'">
                            <li v-if="auth.checkPermission('eliminar programa')" @click="store.remove(item)" class="text-red-400">Desactivar</li>
                        </template>
                    </ul>
                </Drop-Down-Button>
            </template>
        </Data-Table>
    </Card>

    <Modal :open="store.modal.new" title="Crear programa" icon="fas fa-book-open-reader">
        <template #close>
            <Icon @click="store.resetData" icon="fas fa-xmark" class="cursor-pointer text-white" />
        </template>
        <div class="grid gap-4">
            <Input v-model="store.programa.nombre" option="label" title="*Nombre" maxlength="80" :error="store.errors.hasOwnProperty('nombre')" />
            <Input v-if="auth.user.perfil.toLowerCase() == 'sysadmin'" v-model="store.programa.dependencia_id" option="select" title="*Seleccione dependencia" :error="store.errors.hasOwnProperty('dependencia_id')">
                <option value=""></option>
                <option v-for="dependencia in catalogos.dependencias" :value="dependencia.id">{{ dependencia.nombre }}</option>
            </Input>
            <Input v-model="store.programa.descripcion" option="text-area" title="Descripción" placeholder="Describe el programa ..." rows="7" maxlength="255" :error="store.errors.hasOwnProperty('descripcion')" />
        </div>
        <Validate-Errors :errors="store.errors" v-if="store.errors != 0" />
        <template #footer>
            <Button @click="store.resetData" text="Cancelar" icon="fas fa-xmark" class="btn-secondary" />
            <Button @click="store.store" text="Crear" icon="fas fa-plus" class="btn-primary" :loading="store.loading.store" />
        </template>
    </Modal>

    <Modal :open="store.modal.edit" title="Editar programa" icon="fas fa-book-open-reader">
        <template #close>
            <Icon @click="store.resetData" icon="fas fa-xmark" class="cursor-pointer text-white" />
        </template>
        <div class="grid gap-4">
            <div v-if="store.programa.estado == 'I'" class="flex justify-end">
                <div class="flex items-center gap-2">
                    <span class="text-sm text-gray-500">Activar</span>
                    <Switch v-model="store.programa.estado" class="h-auto w-14 bg-red-400 has-[:checked]:bg-green-500" :values="['A','I']" />
                    <span class="text-sm text-gray-500">Inactivo</span>
                </div>
            </div>
            <Input v-model="store.programa.nombre" option="label" title="*Nombre" maxlength="80" :error="store.errors.hasOwnProperty('nombre')" />
            <Input v-if="auth.user.perfil.toLowerCase() == 'sysadmin'" v-model="store.programa.dependencia_id" option="select" title="*Seleccione dependencia" :error="store.errors.hasOwnProperty('dependencia_id')">
                <option value=""></option>
                <option v-for="dependencia in catalogos.dependencias" :value="dependencia.id">{{ dependencia.nombre }}</option>
            </Input>
            <Input v-model="store.programa.descripcion" option="text-area" title="Descripción" placeholder="Describe el programa ..." rows="7" maxlength="255" :error="store.errors.hasOwnProperty('descripcion')" />
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
                <p class="text-center text-lg">¿Estás seguro de desactivar el programa?</p>
                <h1 class="text-center font-semibold">{{ store.programa.nombre }}</h1>
            </div>
        </div>
        <template #footer>
            <Button @click="store.resetData" text="Cancelar" icon="fas fa-xmark" class="btn-secondary" />
            <Button @click="store.destroy" text="Sí, desactivar" icon="fas fa-trash" class="btn-danger" :loading="store.loading.destroy" />
        </template>
    </Modal>

</template>

<style scoped>
    li {
        @apply cursor-pointer text-xs text-nowrap hover:bg-slate-100 py-1 px-3 rounded-lg;
    }
</style>
