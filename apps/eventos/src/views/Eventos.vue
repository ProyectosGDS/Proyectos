<script setup>
    import { useEventosStore } from '@/stores/eventos'
    import { useAuthStore } from '@/stores/auth'
    import { useCatalogosStore } from '@/stores/catalogos'
    import { computed, onBeforeMount } from 'vue'

    const store = useEventosStore()
    const catalogos = useCatalogosStore()
    const auth = useAuthStore()

    const currentYear = new Date().getFullYear();

    const years = computed(() => {
      const yearsList = []
      for (let i = 0; i <= 3; i++) {
        yearsList.unshift(currentYear - i)
      }
      return yearsList
    })

    const estado = (state) => {
        if(state == 'ACEPTADO') {
            return 'green'
        }

        if(state == 'PENDIENTE') {
            return 'orange'
        }

        if(state == 'RECHAZADO') {
            return 'red'
        }
    }

    onBeforeMount(() => {
        catalogos.getCatalogos()
        const year = new Date()
        store.year = year.getFullYear()
        store.fetch(store.year)
    })

</script>

<template>
    <Card class="bg-white px-4 py-8">
        <div v-if="auth.checkPermission('crear evento')" class="flex justify-center">
            <Tool-Tip message="Nuevo evento" class="-mt-7 text-color-4">
                <Button @click="store.modal.new = true" icon="fas fa-plus" class="btn-primary" />
            </Tool-Tip>
        </div>
        <div class="flex justify-center items-center gap-4 py-4">
            <div class="w-96">
                <Input @change="store.fetch(store.year)" v-model="store.year" option="select" title="*seleccione año" :error="store.errors.hasOwnProperty('year')">
                    <option v-for="year in years" :value="year">{{ year }}</option>
                </Input>
            </div>
            <Button @click="store.fetch(store.year)" text="Consultar" icon="search" class="btn-primary" :loading="store.loading.fetch" />
        </div>
        <Data-Table v-if="auth.checkPermission('ver eventos')" :headers="store.headers" :data="store.eventos" :loading="store.loading.fetch" :excel="auth.checkPermission('exportar excel eventos')">
            <template #estado="{item}">
                <Badge :color="estado(item.estado)" :text="item.estado"/>
            </template>
            <template #actions="{item}">
                <Drop-Down-Button icon="fas fa-ellipsis-v" >
                    <ul>
                        <li v-if="auth.checkPermission('editar evento')" @click="store.show(item.id)" class="text-color-4">Editar</li>
                        <li v-if="auth.checkPermission('eliminar evento')" @click="store.remove(item)" class="text-red-400">Eliminar</li>
                    </ul>
                </Drop-Down-Button>
            </template>
        </Data-Table>
    </Card>

    <Modal :open="store.modal.new" title="Crear evento" icon="fas fa-bicycle" class="w-1/2">
        <template #close>
            <Icon @click="store.resetData" icon="fas fa-xmark" class="cursor-pointer text-white" />
        </template>
        <div class="grid gap-4">
            <Input v-model="store.evento.nombre" option="label" title="*Nombre" maxlength="100" :error="store.errors.hasOwnProperty('nombre')" />
            <Input v-model="store.evento.descripcion" option="text-area" title="Descripción" placeholder="Describe el evento ..." rows="7" maxlength="500" :error="store.errors.hasOwnProperty('descripcion')" />
            <Input v-model="store.evento.ubicacion" option="label" title="*ubicación" maxlength="500" :error="store.errors.hasOwnProperty('ubicacion')" />
            <Input v-model="store.evento.responsable" option="label" title="*responsable" maxlength="100" :error="store.errors.hasOwnProperty('responsable')" />
            <div class="grid xl:flex gap-4">
                <Input v-model="store.evento.fecha_inicial" option="label" title="*fecha inicial" type="date" :error="store.errors.hasOwnProperty('fecha_inicial')" />
                <Input v-model="store.evento.fecha_final" option="label" title="*fecha final" type="date" :error="store.errors.hasOwnProperty('fecha_final')" />
            </div>
            <div class="grid xl:flex gap-4">
                <Input v-model="store.evento.hora_inicial" option="label" title="*hora inicial" type="time" :error="store.errors.hasOwnProperty('hora_inicial')" />
                <Input v-model="store.evento.hora_final" option="label" title="*hora final" type="time" :error="store.errors.hasOwnProperty('hora_final')" />
            </div>
            <Input v-model="store.evento.tipo_evento_id" option="select" title="*Seleccione tipo evento" :error="store.errors.hasOwnProperty('tipo_evento_id')">
                <option value=""></option>
                <option v-for="tipo in catalogos.catalogos_evento.tipos_eventos" :value="tipo.id">{{ tipo.nombre }}</option>
            </Input>
        </div>
        <Validate-Errors :errors="store.errors" v-if="store.errors != 0" />
        <template #footer>
            <Button @click="store.resetData" text="Cancelar" icon="fas fa-xmark" class="btn-secondary" />
            <Button @click="store.store" text="Crear" icon="fas fa-plus" class="btn-primary" :loading="store.loading.store" />
        </template>
    </Modal>

    <Modal :open="store.modal.edit" title="Editar evento" icon="fas fa-bicycle" class="w-1/2">
        <template #close>
            <Icon @click="store.resetData" icon="fas fa-xmark" class="cursor-pointer text-white" />
        </template>
        <div class="grid gap-4">
            <Input v-model="store.evento.nombre" option="label" title="*Nombre" maxlength="100" :error="store.errors.hasOwnProperty('nombre')" />
            <Input v-model="store.evento.descripcion" option="text-area" title="Descripción" placeholder="Describe el evento ..." rows="7" maxlength="500" :error="store.errors.hasOwnProperty('descripcion')" />
            <Input v-model="store.evento.ubicacion" option="label" title="*ubicación" maxlength="500" :error="store.errors.hasOwnProperty('ubicacion')" />
            <Input v-model="store.evento.responsable" option="label" title="*responsable" maxlength="100" :error="store.errors.hasOwnProperty('responsable')" />
            <div class="grid xl:flex gap-4">
                <Input v-model="store.evento.fecha_inicial" option="label" title="*fecha inicial" type="date" :error="store.errors.hasOwnProperty('fecha_inicial')" />
                <Input v-model="store.evento.fecha_final" option="label" title="*fecha final" type="date" :error="store.errors.hasOwnProperty('fecha_final')" />
            </div>
            <div class="grid xl:flex gap-4">
                <Input v-model="store.evento.hora_inicial" option="label" title="*hora inicial" type="time" :error="store.errors.hasOwnProperty('hora_inicial')" />
                <Input v-model="store.evento.hora_final" option="label" title="*hora final" type="time" :error="store.errors.hasOwnProperty('hora_final')" />
            </div>
            <div class="grid xl:flex gap-4">
                <Input v-model="store.evento.tipo_evento_id" option="select" title="*Seleccione tipo evento" :error="store.errors.hasOwnProperty('tipo_evento_id')">
                    <option value=""></option>
                    <option v-for="tipo in catalogos.catalogos_evento.tipos_eventos" :value="tipo.id">{{ tipo.nombre }}</option>
                </Input>
                <Input v-model="store.evento.estado_evento_id" option="select" title="*Seleccione estado evento" :error="store.errors.hasOwnProperty('estado_evento_id')">
                    <option value=""></option>
                    <option v-for="estado in catalogos.catalogos_evento.estados_eventos" :value="estado.id">{{ estado.nombre }}</option>
                </Input>
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
                <p class="text-center text-lg">¿Estás seguro de eliminar el evento?</p>
                <h1 class="text-center font-semibold">{{ store.evento.nombre }}</h1>
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
