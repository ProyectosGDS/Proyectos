<script setup>
    import { onBeforeMount } from 'vue'
    import { useCursosModuloStore } from '@/stores/Asignaciones/cursos-modulo'
    import { useInstructoresStore } from '@/stores/Catalogos/instructores'

    const store = useCursosModuloStore()
    const instructores = useInstructoresStore()

    function selectInstructor(item) {
        store.detalles.instructor = item
    }

    onBeforeMount(() => {
        instructores.fetch()
    })
</script>

<template>
    <Data-Table 
        :headers="instructores.headers" 
        :data="instructores.instructores"
        :loading="instructores.loading.fetch"
        :excel="false" 
        :rowsPerPage="5" 
        :multiSelect="true" 
        @selectdAllItems="selectInstructor" 
        :itemsSelected="store.detalles.instructor" 
    />
</template>
