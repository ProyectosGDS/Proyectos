<script setup>
    import { onBeforeMount } from 'vue'
    import { useSedesStore } from '@/stores/Catalogos/sedes'
    import { useCursosProgramaStore } from '@/stores/Asignaciones/cursos-programa'
    import { useCatalogosStore } from '@/stores/Catalogos/catalogos'
    
    const store = useCursosProgramaStore()
    const sedes = useSedesStore()
    const catalogos = useCatalogosStore()

    function selectSede(item) {
        store.detalles.sede = item
    }

    onBeforeMount(() => {
        sedes.fetch()
        catalogos.getZonas()
        catalogos.getDistritos()
    })
</script>

<template>
    <div class="grid xl:grid-cols-3 gap-4">
        <div class="col-span-2">
            <Input v-model="sedes.sede.nombre" option="label" title="*Nombre" maxlength="100" :error="sedes.errors.hasOwnProperty('nombre')" />
        </div>
        <Input v-model="sedes.sede.zona_id" option="select" title="*zonas" :error="sedes.errors.hasOwnProperty('zona_id')">
            <option value=""></option>
            <option v-for="zona in catalogos.zonas" :value="zona.id">{{ zona.descripcion }}</option>
        </Input>
        <div class="col-span-2">
            <Input v-model="sedes.sede.direccion" option="label" title="*dirección" maxlength="100" :error="sedes.errors.hasOwnProperty('direccion')" />
        </div>
        <Input v-model="sedes.sede.distrito_id" option="select" title="distritos" :error="sedes.errors.hasOwnProperty('distrito_id')">
            <option value=""></option>
            <option v-for="distrito in catalogos.distritos" :value="distrito.id">{{ distrito.nombre }}</option>
        </Input>
    </div>
    <Validate-Errors :errors="sedes.errors" v-if="sedes.errors != 0" />
    <div class="flex justify-center">
        <Button @click="sedes.store" text="Crear sede" icon="fas fa-school" class="btn-primary" :loading="sedes.loading.store"/>
    </div>
    <hr class="my-4">
    <Data-Table 
        :headers="sedes.headers" 
        :data="sedes.sedes"
        :loading="sedes.loading.fetch" 
        :excel="false" 
        :rowsPerPage="5" 
        :multiSelect="true" 
        @selectdAllItems="selectSede" 
        :itemsSelected="store.detalles.sede" 
    />
</template>
