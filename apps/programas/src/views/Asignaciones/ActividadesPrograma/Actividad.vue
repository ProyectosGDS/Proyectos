<script setup>
    import { onBeforeMount } from 'vue'
    import { useActividadesStore } from '@/stores/Catalogos/actividades'
    import { useActividadesProgramaStore } from '@/stores/Asignaciones/actividades-programa'

    const store = useActividadesProgramaStore()
    const actividades = useActividadesStore()

    function selectCurso(item) {
        store.detalles.actividad = item
    }

    onBeforeMount(() => {
        actividades.fetch()
    })
</script>

<template>
    <Input v-model="actividades.actividad.nombre" option="label" title="*Nombre" maxlength="80" :error="actividades.errors.hasOwnProperty('nombre')" />
    <Input v-model="actividades.actividad.descripcion" option="text-area" title="Descripción" placeholder="Describe el actividad ..." rows="3" maxlength="255" :error="actividades.errors.hasOwnProperty('descripcion')" />
    <Validate-Errors :errors="actividades.errors" v-if="actividades.errors != 0" />
    <div class="flex justify-center">
        <Button @click="actividades.store" text="Crear actividad" icon="fas fa-bicycle" class="btn-primary" :loading="actividades.loading.store"/>
    </div>
    <hr class="my-4">
    <Data-Table 
        :headers="actividades.headers" 
        :data="actividades.actividades"
        :loading="actividades.loading.fetch"
        :excel="false" 
        :rowsPerPage="5" 
        :multiSelect="true" 
        @selectdAllItems="selectCurso" 
        :itemsSelected="store.detalles.actividad" 
    />
</template>

