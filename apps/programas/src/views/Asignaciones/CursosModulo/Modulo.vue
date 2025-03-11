<script setup>
    import { onBeforeMount } from 'vue'
    import { useCursosModuloStore } from '@/stores/Asignaciones/cursos-modulo'
    import { useModulosStore } from '@/stores/Catalogos/modulos'
    import { useProgramasStore } from '@/stores/Catalogos/programas'

    const store = useCursosModuloStore()
    const modulos = useModulosStore()
    const programas = useProgramasStore()

    function selectModulo(item) {
        store.detalles.modulo = item
    }

    onBeforeMount(() => {
        modulos.fetch(store.programa_id)
        modulos.modulo.programa_id = store.programa_id
        modulos.programa_id = store.programa_id
    })
</script>

<template>
    <Input v-model="modulos.modulo.nombre" option="label" title="*Nombre" maxlength="80" :error="modulos.errors.hasOwnProperty('nombre')" />
    <Input v-model="modulos.modulo.programa_id" option="select" title="*Seleccione programa" :error="modulos.errors.hasOwnProperty('programa_id')" readonly disabled>
        <option value=""></option>
        <template v-for="programa in programas.programas">
            <option v-if="programa.estado == 'A'" :value="programa.id">{{ programa.nombre }}</option>
        </template>
    </Input>
    <Input v-model="modulos.modulo.descripcion" option="text-area" title="Descripción" placeholder="Describe el modulo ..." rows="3" maxlength="255" :error="modulos.errors.hasOwnProperty('descripcion')" />
    <div class="grid xl:flex gap-4">
        <Input v-model="modulos.modulo.fecha_inicial" option="label" title="inicia" type="date" :error="modulos.errors.hasOwnProperty('fecha_inicial')" />
        <Input v-model="modulos.modulo.fecha_final" option="label" title="termina" type="date" :error="modulos.errors.hasOwnProperty('fecha_final')" />
    </div>
    <Validate-Errors :errors="modulos.errors" v-if="modulos.errors != 0" />
    <div class="flex justify-center">
        <Button @click="modulos.store" text="Crear módulo" icon="fas fa-folder-tree" class="btn-primary" :loading="modulos.loading.store"/>
    </div>
    <hr class="my-4">
    <Data-Table 
        :headers="modulos.headers" 
        :data="modulos.modulos"
        :loading="modulos.loading.fetch" 
        :excel="false" 
        :rowsPerPage="5" 
        :multiSelect="true" 
        @selectdAllItems="selectModulo" 
        :itemsSelected="store.detalles.modulo" 
    />
</template>

