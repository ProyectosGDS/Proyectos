<script setup>
    import { computed, onBeforeMount } from 'vue'
    import { useBeneficiariosActividadStore } from '@/stores/Inscripciones/beneficiarios-actividad'
    import { useAsignacionesActividadesProgramaStore } from '@/stores/Asignaciones/asignaciones-actividades-programa'
    import { useInscripcionesActividadStore } from '@/stores/Inscripciones/inscripciones-actividad'

    const store = useBeneficiariosActividadStore()
    const actividades = useAsignacionesActividadesProgramaStore()
    const beneficiarios_actividad_store = useInscripcionesActividadStore()

    const data = computed(() => {
        return actividades.actividades.filter(actividad => actividad.estado == 'ACEPTADO')
    })

    function selectActividad(item) {
        store.detalles = item
    }

    onBeforeMount(() => {
        actividades.year = beneficiarios_actividad_store.year
        actividades.fetch(beneficiarios_actividad_store.programa_id)
    })

</script>

<template>
    <Data-Table 
        :headers="actividades.headers" 
        :data="data" 
        :loading="actividades.loading.fetch"
        :rowsPerPage="5" 
        :multiSelect="true" 
        @selectdAllItems="selectActividad" 
        :itemsSelected="store.detalles"
    />
</template>

