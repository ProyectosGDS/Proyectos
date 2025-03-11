<script setup>
    import { useSedesStore } from '@/stores/Catalogos/sedes'
    import { useAuthStore } from '@/stores/auth'
    import { useCatalogosStore } from '@/stores/Catalogos/catalogos'
    import { onBeforeMount } from 'vue'

    const store = useSedesStore()
    const auth = useAuthStore()
    const catalogos = useCatalogosStore()

    onBeforeMount(() => {
        store.fetch()
        catalogos.getZonas()
        catalogos.getDistritos()
    })

</script>

<template>
    <Card class="bg-white px-4 py-8">
        <div v-if="auth.checkPermission('crear sede')" class="flex justify-center">
            <Tool-Tip message="Nuevo sede" class="-mt-7 text-color-4">
                <Button @click="store.modal.new = true" icon="fas fa-plus" class="btn-primary" />
            </Tool-Tip>
        </div>
        <Data-Table v-if="auth.checkPermission('ver sedes')" :headers="store.headers" :data="store.sedes" :loading="store.loading.fetch" :excel="auth.checkPermission('exportar excel sedes')">
            <template #estado="{item}">
                <Badge :color="item.estado == 'A' ? 'green' : 'red'" :text="item.estado == 'A' ? 'Activo' : 'Inactivo'" />
            </template>
            <template #actions="{item}">
                <Drop-Down-Button icon="fas fa-ellipsis-v" >
                    <ul>
                        <li v-if="auth.checkPermission('editar sede')" @click="store.edit(item)" class="text-color-4">Editar</li>
                        <template v-if="item.estado == 'A'">
                            <li v-if="auth.checkPermission('eliminar sede')" @click="store.remove(item)" class="text-red-400">Desactivar</li>
                        </template>
                    </ul>
                </Drop-Down-Button>
            </template>
        </Data-Table>
    </Card>

    <Modal :open="store.modal.new" title="Crear sede" icon="fas fa-school" class="w-1/3">
        <template #close>
            <Icon @click="store.resetData" icon="fas fa-xmark" class="seder-pointer text-white" />
        </template>
        <div class="grid xl:grid-cols-3 gap-4">
            <div class="col-span-2">
                <Input v-model="store.sede.nombre" option="label" title="*Nombre" maxlength="100" :error="store.errors.hasOwnProperty('nombre')" />
            </div>
            <Input v-model="store.sede.zona_id" option="select" title="*zonas" :error="store.errors.hasOwnProperty('zona_id')">
                <option value=""></option>
                <option v-for="zona in catalogos.zonas" :value="zona.id">{{ zona.descripcion }}</option>
            </Input>
            <div class="col-span-2">
                <Input v-model="store.sede.direccion" option="label" title="*dirección" maxlength="100" :error="store.errors.hasOwnProperty('direccion')" />
            </div>
            <Input v-model="store.sede.distrito_id" option="select" title="distritos" :error="store.errors.hasOwnProperty('distrito_id')">
                <option value=""></option>
                <option v-for="distrito in catalogos.distritos" :value="distrito.id">{{ distrito.nombre }}</option>
            </Input>
        </div>
        <Validate-Errors :errors="store.errors" v-if="store.errors != 0" />
        <template #footer>
            <Button @click="store.resetData" text="Cancelar" icon="fas fa-xmark" class="btn-secondary" />
            <Button @click="store.store" text="Crear" icon="fas fa-plus" class="btn-primary" :loading="store.loading.store" />
        </template>
    </Modal>

    <Modal :open="store.modal.edit" title="Editar sede" icon="fas fa-school">
        <template #close>
            <Icon @click="store.resetData" icon="fas fa-xmark" class="seder-pointer text-white" />
        </template>
        <div v-if="store.sede.estado == 'I'" class="flex justify-end">
            <div class="flex items-center gap-2">
                <span class="text-sm text-gray-500">Activar</span>
                <Switch v-model="store.sede.estado" class="h-auto w-14 bg-red-400 has-[:checked]:bg-green-500" :values="['A','I']" />
                <span class="text-sm text-gray-500">Inactivo</span>
            </div>
        </div>
        <br>
        <div class="grid xl:grid-cols-3 gap-4">
            <div class="col-span-2">
                <Input v-model="store.sede.nombre" option="label" title="*Nombre" maxlength="100" :error="store.errors.hasOwnProperty('nombre')" />
            </div>
            <Input v-model="store.sede.zona_id" option="select" title="*zonas" :error="store.errors.hasOwnProperty('zona_id')">
                <option value=""></option>
                <option v-for="zona in catalogos.zonas" :value="zona.id">{{ zona.descripcion }}</option>
            </Input>
            <div class="col-span-2">
                <Input v-model="store.sede.direccion" option="label" title="*dirección" maxlength="100" :error="store.errors.hasOwnProperty('direccion')" />
            </div>
            <Input v-model="store.sede.distrito_id" option="select" title="distritos" :error="store.errors.hasOwnProperty('distrito_id')">
                <option value=""></option>
                <option v-for="distrito in catalogos.distritos" :value="distrito.id">{{ distrito.nombre }}</option>
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
                <p class="text-center text-lg">¿Estás seguro de desactivar el sede?</p>
                <h1 class="text-center font-semibold">{{ store.sede.nombre }}</h1>
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
