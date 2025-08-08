<script setup>
    import { onBeforeMount } from 'vue'
    import { useCatalogosStore } from '@/stores/catalogos'

    const headers = [
        { title : 'id', key : 'id', type : 'numeric' },
        { title : 'programa', key : 'programa.nombre' },
        { title : 'modulo', key : 'nombre', class : 'uppercase text-xs font-semibold' },
        { title : 'seccion', key : 'seccion', width : '10px', align : 'center' },
        { title : 'sede', key : 'sede.nombre_completo' },
        { title : 'temporalidad', key : 'temporalidad.nombre', class: 'uppercase text-xs', width : '10px', align : 'center' },
        { title : 'modalidad', key : 'modalidad', width : '10px', align : 'center' },
        { title : 'inicia', key : 'fecha_inicial', type : 'date' },
        { title : 'termina', key : 'fecha_final', type : 'date' },
    ]

    const catalogos = useCatalogosStore()

    const selectModulo = (items) => {
        catalogos.modulos = items
    }

    onBeforeMount(() => {
        catalogos.getModulosPrograma()
    })
</script>

<template>
    <Data-Table 
        :headers="headers" 
        :data="catalogos.modulos_programa"
        :loading="catalogos.loading.modulos"
        :rowsPerPage="5"
        :multiSelect="true" 
        @selectdAllItems="selectModulo" 
        :itemsSelected="catalogos.modulos"
    />
</template>