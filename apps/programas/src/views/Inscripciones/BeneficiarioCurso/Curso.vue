<script setup>
    import { onBeforeMount } from 'vue'
    import { useBeneficiariosCursoStore } from '@/stores/Inscripciones/beneficiarios-curso'
    import { useAsignacionesCursosProgramaStore } from '@/stores/Asignaciones/asignaciones-cursos-programa'
    import { useInscripcionesCursoStore } from '@/stores/Inscripciones/inscripciones-curso'

    const store = useBeneficiariosCursoStore()
    const cursos = useAsignacionesCursosProgramaStore()
    const beneficiarios_curso_store = useInscripcionesCursoStore()

    function selectCurso(item) {
        store.detalles = item
    }

    onBeforeMount(() => {
        cursos.fetch(beneficiarios_curso_store.programa_id)
    })

</script>

<template>
    <Data-Table 
        :headers="cursos.headers" 
        :data="cursos.cursos" 
        :loading="cursos.loading.fetch"
        :rowsPerPage="5" 
        :multiSelect="true" 
        @selectdAllItems="selectCurso" 
        :itemsSelected="store.detalles"
    />
</template>

