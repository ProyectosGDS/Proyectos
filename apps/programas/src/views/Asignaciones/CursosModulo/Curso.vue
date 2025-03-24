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
    <Input v-model="cursos.curso.descripcion" option="text-area" title="Descripción" placeholder="Describe el curso ..." rows="3" maxlength="500" :error="cursos.errors.hasOwnProperty('descripcion')" />
    <div class="flex justify-evenly uppercase text-color-4">
        <label class="uppercase">*Impulsatec</label>
        <div class="flex items-center gap-2">
            <span class="text-sm text-gray-500">Sí</span>
            <Switch v-model="store.curso.inpulsatec" class="h-auto w-14 bg-red-400 has-[:checked]:bg-green-500" :values="['S','N']" />
            <span class="text-sm text-gray-500">No</span>
        </div>
    </div>
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

