<script setup>
    import { onBeforeMount } from 'vue'
    import { useCursosProgramaStore } from '@/stores/Asignaciones/cursos-programa'
    import { useInstructoresStore } from '@/stores/Catalogos/instructores'

    const store = useCursosProgramaStore()
    const instructores = useInstructoresStore()

    function selectInstructor(item) {
        store.detalles.instructor = item
    }

    onBeforeMount(() => {
        instructores.fetch()
    })
</script>

<template>
    <Input v-model="instructores.instructor.nombre" option="label" title="*Nombre" maxlength="100" :error="instructores.errors.hasOwnProperty('nombre')" />
    <Validate-Errors :errors="instructores.errors" v-if="instructores.errors != 0" />
    <div class="flex justify-center">
        <Button @click="instructores.store" text="Crear instructor" icon="fas fa-chalkboard-user" class="btn-primary" :loading="instructores.loading.store"/>
    </div>
    <hr class="my-4">
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
