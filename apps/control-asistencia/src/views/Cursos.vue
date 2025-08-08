<script setup>
    import { ref, onBeforeMount } from 'vue'
    import { useCatalogosStore } from '@/stores/catalogos'

    const headers = [
        { title : 'id', key : 'id', type : 'numeric' },
        { title : 'programa', key : 'programa.nombre' },
        { title : 'curso', key : 'curso.nombre' },
        { title : 'seccion', key : 'seccion', width : '10px', align : 'center' },
        { title : 'instructor', key : 'instructor.nombre', class: 'uppercase text-xs font-semibold' },
        { title : 'sede', key : 'sede.nombre_completo' },
        { title : 'temporalidad', key : 'temporalidad.nombre', class: 'uppercase text-xs', width : '10px', align : 'center' },
        { title : 'modalidad', key : 'modalidad', width : '10px', align : 'center' },
        { title : 'inicia', key : 'fecha_inicial', type : 'date' },
        { title : 'termina', key : 'fecha_final', type : 'date' },
    ]

    const catalogos = useCatalogosStore()

    const selectCurso = (items) => {
        catalogos.cursos = items
    }

    onBeforeMount(() => {
        catalogos.getCursosPrograma()
    })
</script>

<template>
    <Data-Table 
        :headers="headers" 
        :data="catalogos.cursos_programa"
        :loading="catalogos.loading.cursos_programa"
        :rowsPerPage="5"
        :multiSelect="true" 
        @selectdAllItems="selectCurso" 
        :itemsSelected="catalogos.cursos"
    />
</template>