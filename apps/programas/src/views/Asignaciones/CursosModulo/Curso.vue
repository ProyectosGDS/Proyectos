<script setup>
    import { onBeforeMount } from 'vue'
    import { useCursosModuloStore } from '@/stores/Asignaciones/cursos-modulo'
    import { useCursosStore } from '@/stores/Catalogos/cursos'

    const store = useCursosModuloStore()
    const cursos = useCursosStore()

    function selectCurso(item) {
        store.detalles.curso = item
    }

    onBeforeMount(() => {
        cursos.fetch()
    })
</script>

<template>
    <Input v-model="cursos.curso.nombre" option="label" title="*Nombre" maxlength="80" :error="cursos.errors.hasOwnProperty('nombre')" />
    <Input v-model="cursos.curso.descripcion" option="text-area" title="Descripción" placeholder="Describe el curso ..." rows="3" maxlength="255" :error="cursos.errors.hasOwnProperty('descripcion')" />
    <Validate-Errors :errors="cursos.errors" v-if="cursos.errors != 0" />
    <div class="flex justify-center">
        <Button @click="cursos.store" text="Crear curso" icon="fas fa-book" class="btn-primary" :loading="cursos.loading.store"/>
    </div>
    <hr class="my-4">
    <Data-Table 
        :headers="cursos.headers" 
        :data="cursos.cursos"
        :loading="cursos.loading.fetch"
        :excel="false" 
        :rowsPerPage="5" 
        :multiSelect="true" 
        @selectdAllItems="selectCurso" 
        :itemsSelected="store.detalles.curso" 
    />
</template>

