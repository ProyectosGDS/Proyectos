<script setup>
    import { useModulosStore } from '@/stores/Catalogos/modulos'
    import { useProgramasStore } from '@/stores/Catalogos/programas'
    import { useAuthStore } from '@/stores/auth'
    import { onBeforeMount } from 'vue'

    const programas = useProgramasStore()
    const store = useModulosStore()
    const auth = useAuthStore()
    
    onBeforeMount(() => {
        programas.fetch()
        store.getRequirements()
    })

</script>

<template>
    <Card class="bg-white px-4 py-8">
        <div v-if="auth.checkPermission('crear modulo')" class="flex justify-center">
            <Tool-Tip message="Nuevo modulo" class="-mt-7 text-color-4">
                <Button @click="store.modal.new = true" icon="fas fa-plus" class="btn-primary" />
            </Tool-Tip>
        </div>
        <div class="flex items-center gap-4 p-4">
            <Input v-model="store.programa_id" @change="store.fetch(store.programa_id)" option="select" class="flex-1" title="*Seleccione programa para cargar módulos" :error="store.errors.hasOwnProperty('programa_id')">
                <option value=""></option>
                <template v-for="programa in programas.programas">
                    <option v-if="programa.estado == 'A'" :value="programa.id">{{ programa.nombre }}</option>
                </template>
            </Input>
            <Button @click="store.fetch(store.programa_id)" text="Cargar" icon="fas fa-share" class="btn-secondary flex-none" :loading="store.loading.fetch" />
        </div>
        <Data-Table v-if="auth.checkPermission('ver modulos')" :headers="store.headers" :data="store.modulos" :loading="store.loading.fetch" :excel="auth.checkPermission('exportar excel modulos')">
            <template #estado="{item}">
                <Badge :color="item.estado == 'A' ? 'green' : 'red'" :text="item.estado == 'A' ? 'Activo' : 'Inactivo'" />
            </template>
            <template #actions="{item}">
                <Drop-Down-Button icon="fas fa-ellipsis-v" >
                    <ul>
                        <li v-if="auth.checkPermission('editar modulo')" @click="store.edit(item)" class="text-color-4">Editar</li>
                        <li v-if="auth.checkPermission('asignar requisitos modulo')" @click="store.assignRequirements(item)" class="text-color-4">Asignar requisitos</li>
                        <template v-if="item.estado == 'A'">
                            <li v-if="auth.checkPermission('eliminar modulo')" @click="store.remove(item)" class="text-red-400">Desactivar</li>
                        </template>
                    </ul>
                </Drop-Down-Button>
            </template>
        </Data-Table>
    </Card>

    <Modal :open="store.modal.new" title="Crear módulo" icon="fas fa-folder-tree">
        <template #close>
            <Icon @click="store.resetData" icon="fas fa-xmark" class="cursor-pointer text-white" />
        </template>
        <div class="grid gap-4">
            <Input v-model="store.modulo.nombre" option="label" title="*Nombre" maxlength="80" :error="store.errors.hasOwnProperty('nombre')" />
            <Input v-model="store.modulo.programa_id" option="select" title="*Seleccione programa" :error="store.errors.hasOwnProperty('programa_id')">
                <option value=""></option>
                <template v-for="programa in programas.programas">
                    <option v-if="programa.estado == 'A'" :value="programa.id">{{ programa.nombre }}</option>
                </template>
            </Input>
            <Input v-model="store.modulo.descripcion" option="text-area" title="Descripción" placeholder="Describe el modulo ..." rows="7" maxlength="255" :error="store.errors.hasOwnProperty('descripcion')" />
            <Input v-model="store.modulo.fecha_inicial" option="label" title="inicia" type="date" :error="store.errors.hasOwnProperty('fecha_inicial')" />
            <Input v-model="store.modulo.fecha_final" option="label" title="termina" type="date" :error="store.errors.hasOwnProperty('fecha_final')" />
            <Input v-model="store.modulo.capacidad" option="label" title="Capacidad" type="number" min="1" :error="store.errors.hasOwnProperty('capacidad')" />
        </div>
        <Validate-Errors :errors="store.errors" v-if="store.errors != 0" />
        <template #footer>
            <Button @click="store.resetData" text="Cancelar" icon="fas fa-xmark" class="btn-secondary" />
            <Button @click="store.store" text="Crear" icon="fas fa-plus" class="btn-primary" :loading="store.loading.store" />
        </template>
    </Modal>

    <Modal :open="store.modal.edit" title="Editar modulo" icon="fas fa-folder-tree" class="w-1/2">
        <template #close>
            <Icon @click="store.resetData" icon="fas fa-xmark" class="cursor-pointer text-white" />
        </template>
        <div class="grid gap-4">
            <div v-if="store.modulo.estado == 'I'" class="flex justify-end">
                <div class="flex items-center gap-2">
                    <span class="text-sm text-gray-500">Activar</span>
                    <Switch v-model="store.modulo.estado" class="h-auto w-14 bg-red-400 has-[:checked]:bg-green-500" :values="['A','I']" />
                    <span class="text-sm text-gray-500">Inactivo</span>
                </div>
            </div>
            <Input v-model="store.modulo.nombre" option="label" title="*Nombre" maxlength="80" :error="store.errors.hasOwnProperty('nombre')" />
            <Input v-model="store.modulo.programa_id" option="select" title="*Seleccione programa" :error="store.errors.hasOwnProperty('programa_id')">
                <option value=""></option>
                <template v-for="programa in programas.programas">
                    <option v-if="programa.estado == 'A'" :value="programa.id">{{ programa.nombre }}</option>
                </template>
            </Input>
            <Input v-model="store.modulo.descripcion" option="text-area" title="Descripción" placeholder="Describe el modulo ..." rows="7" maxlength="255" :error="store.errors.hasOwnProperty('descripcion')" />
            <div class="grid grid-cols-2 gap-4">
                <Input v-model="store.modulo.fecha_inicial" option="label" title="inicia" type="date" :error="store.errors.hasOwnProperty('fecha_inicial')" />
                <Input v-model="store.modulo.fecha_final" option="label" title="termina" type="date" :error="store.errors.hasOwnProperty('fecha_final')" />
            </div>
            <Input v-model="store.modulo.capacidad" option="label" title="Capacidad" type="number" min="1" :error="store.errors.hasOwnProperty('capacidad')" />
            <div class="flex justify-evenly">
                <div class="flex items-center gap-2">
                    <span class="text-sm text-gray-500">PÚBLICO</span>
                    <Switch v-model="store.modulo.publico" class="h-auto w-14 bg-red-400 has-[:checked]:bg-green-500" :values="['S','N']" />
                    <span class="text-sm text-gray-500">PRIVADO</span>
                </div>
            </div>
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
                <p class="text-center text-lg">¿Estás seguro de desactivar el modulo?</p>
                <h1 class="text-center font-semibold">{{ store.modulo.nombre }}</h1>
            </div>
        </div>
        <template #footer>
            <Button @click="store.resetData" text="Cancelar" icon="fas fa-xmark" class="btn-secondary" />
            <Button @click="store.destroy" text="Sí, desactivar" icon="fas fa-trash" class="btn-danger" :loading="store.loading.destroy" />
        </template>
    </Modal>

    <Modal :open="store.modal.requisitos" title="Asignar requisitos modulo" icon="fas fa-folder-tree">
        <template #close>
            <Icon @click="store.resetData" icon="fas fa-xmark" class="cursor-pointer text-white" />
        </template>
        <div>
            <Input v-model="store.modulo.nombre" option="label" title="Módulo seleccionado" readonly disabled />
            <br>
            <details open class="border p-3 uppercase rounded-md border-color-4 text-color-1">
                <summary>Requisitos disponibles</summary>
                <Loading-Bar v-if="store.loading.requisitos" class="bg-color-4 h-1" />
                <div class="grid grid-cols-2 gap-4 py-2">
                    <label v-for="requisito in store.requisitos" class="flex gap-2 text-sm text-color-4">
                        <input type="checkbox" v-model="store.selected_requirements" :value="requisito.id">
                        <span>{{ requisito.nombre }}</span>
                    </label>
                </div>
            </details>
        </div>
        <Validate-Errors :errors="store.errors" v-if="store.errors != 0"/>
        <template #footer>
            <Button @click="store.resetData" text="Cancelar" icon="fas fa-xmark" class="btn-secondary" />
            <Button @click="store.assign" text="Asignar" icon="fas fa-check" class="btn-primary" :loading="store.loading.update" />
        </template>
    </Modal>

</template>

<style scoped>
    li {
        @apply cursor-pointer text-xs text-nowrap hover:bg-slate-100 py-1 px-3 rounded-lg;
    }
</style>
