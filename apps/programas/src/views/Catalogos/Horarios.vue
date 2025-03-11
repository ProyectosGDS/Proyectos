<script setup>
    import { useHorariosStore } from '@/stores/Catalogos/horarios'
    import { useAuthStore } from '@/stores/auth'
    import { onBeforeMount } from 'vue'

    const store = useHorariosStore()
    const auth = useAuthStore()

    onBeforeMount(() => {
        store.fetch()
    })

</script>

<template>
    <Card class="bg-white px-4 py-8">
        <div v-if="auth.checkPermission('crear horario')" class="flex justify-center">
            <Tool-Tip message="Nuevo horario" class="-mt-7 text-color-4">
                <Button @click="store.modal.new = true" icon="fas fa-plus" class="btn-primary" />
            </Tool-Tip>
        </div>
        <Data-Table v-if="auth.checkPermission('ver horarios')" :headers="store.headers" :data="store.horarios" :loading="store.loading.fetch" :excel="auth.checkPermission('exportar excel horarios')">
            <template #estado="{item}">
                <Badge :color="item.estado == 'A' ? 'green' : 'red'" :text="item.estado == 'A' ? 'Activo' : 'Inactivo'" />
            </template>
            <template #actions="{item}">
                <Drop-Down-Button icon="fas fa-ellipsis-v" >
                    <ul>
                        <li v-if="auth.checkPermission('editar horario')" @click="store.edit(item)" class="text-color-4">Editar</li>
                        <template v-if="item.estado == 'A'">
                            <li v-if="auth.checkPermission('eliminar horario')" @click="store.remove(item)" class="text-red-400">Desactivar</li>
                        </template>
                    </ul>
                </Drop-Down-Button>
            </template>
        </Data-Table>
    </Card>

    <Modal :open="store.modal.new" title="Crear horario" icon="fas fa-clock">
        <template #close>
            <Icon @click="store.resetData" icon="fas fa-xmark" class="horarior-pointer text-white" />
        </template>
        <div class="grid gap-4">
            <Input v-model="store.horario.hora_inicial" option="label" title="hora inicio" type="time" :error="store.errors.hasOwnProperty('hora_inicio')" />
            <Input v-model="store.horario.hora_final" option="label" title="hora final" type="time" :error="store.errors.hasOwnProperty('hora_final')" />
            <hr class="my-4">
            <h1 class="text-gray-500 font-medium text-lg">Seleccione los días</h1>
            <div class="flex gap-4">
                <label class="flex gap-1 cursor-pointer">
                    <input v-model="store.dias" type="checkbox" value="lun">
                    <span>LUN</span>
                </label>
                <label class="flex gap-1 cursor-pointer">
                    <input v-model="store.dias" type="checkbox" value="mar">
                    <span>MAR</span>
                </label>
                <label class="flex gap-1 cursor-pointer">
                    <input v-model="store.dias" type="checkbox" value="mie">
                    <span>MIE</span>
                </label>
                <label class="flex gap-1 cursor-pointer">
                    <input v-model="store.dias" type="checkbox" value="jue">
                    <span>JUE</span>
                </label>
                <label class="flex gap-1 cursor-pointer">
                    <input v-model="store.dias" type="checkbox" value="vie">
                    <span>VIE</span>
                </label>
                <label class="flex gap-1 cursor-pointer">
                    <input v-model="store.dias" type="checkbox" value="sab">
                    <span>SAB</span>
                </label>
                <label class="flex gap-1 cursor-pointer">
                    <input v-model="store.dias" type="checkbox" value="dom">
                    <span>DOM</span>
                </label>
            </div>
        </div>
        <Validate-Errors :errors="store.errors" v-if="store.errors != 0" />
        <template #footer>
            <Button @click="store.resetData" text="Cancelar" icon="fas fa-xmark" class="btn-secondary" />
            <Button @click="store.store" text="Crear" icon="fas fa-plus" class="btn-primary" :loading="store.loading.store" />
        </template>
    </Modal>

    <Modal :open="store.modal.edit" title="Editar horario" icon="fas fa-clock">
        <template #close>
            <Icon @click="store.resetData" icon="fas fa-xmark" class="horarior-pointer text-white" />
        </template>
        <div class="grid gap-4">
            <div v-if="store.horario.estado == 'I'" class="flex justify-end">
                <div class="flex items-center gap-2">
                    <span class="text-sm text-gray-500">Activar</span>
                    <Switch v-model="store.horario.estado" class="h-auto w-14 bg-red-400 has-[:checked]:bg-green-500" :values="['A','I']" />
                    <span class="text-sm text-gray-500">Inactivo</span>
                </div>
            </div>
            <Input v-model="store.horario.hora_inicial" option="label" title="hora inicio" type="time" :error="store.errors.hasOwnProperty('hora_inicio')" />
            <Input v-model="store.horario.hora_final" option="label" title="hora final" type="time" :error="store.errors.hasOwnProperty('hora_final')" />
            <hr class="my-4">
            <h1 class="text-gray-500 font-medium text-lg">Seleccione los días</h1>
            <div class="flex gap-4">
                <label class="flex gap-1 cursor-pointer">
                    <input v-model="store.dias" type="checkbox" value="lun">
                    <span>LUN</span>
                </label>
                <label class="flex gap-1 cursor-pointer">
                    <input v-model="store.dias" type="checkbox" value="mar">
                    <span>MAR</span>
                </label>
                <label class="flex gap-1 cursor-pointer">
                    <input v-model="store.dias" type="checkbox" value="mie">
                    <span>MIE</span>
                </label>
                <label class="flex gap-1 cursor-pointer">
                    <input v-model="store.dias" type="checkbox" value="jue">
                    <span>JUE</span>
                </label>
                <label class="flex gap-1 cursor-pointer">
                    <input v-model="store.dias" type="checkbox" value="vie">
                    <span>VIE</span>
                </label>
                <label class="flex gap-1 cursor-pointer">
                    <input v-model="store.dias" type="checkbox" value="sab">
                    <span>SAB</span>
                </label>
                <label class="flex gap-1 cursor-pointer">
                    <input v-model="store.dias" type="checkbox" value="dom">
                    <span>DOM</span>
                </label>
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
                <p class="text-center text-lg">¿Estás seguro de desactivar el horario?</p>
                <h1 class="text-center font-semibold uppercase">{{ store.horario.hora + ' ' + store.horario.dias }}</h1>
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
