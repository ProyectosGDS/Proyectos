<script setup>
    import { useBeneficiariosStore } from '@/stores/Inscripciones/beneficiarios'
    import { useCatalogosStore } from '@/stores/Catalogos/catalogos'

    const store = useBeneficiariosStore()
    const catalogos = useCatalogosStore()

</script>

<template>
    <details class="border-t-2 py-4">
        <summary class="text-color-4 text-lg mb-3 cursor-pointer hover:bg-gray-100 rounded-lg font-medium">INFORMACIÓN ACADÉMICA</summary>
        <div class="grid lg:grid-cols-2 gap-4">
            <Input v-model="store.beneficiario.datos_academicos.establecimiento" option="label" title="establecimiento" maxlength="100" :error="store.errors.hasOwnProperty('datos_academicos.establecimiento')"/>
            <Input v-model="store.beneficiario.datos_academicos.escolaridad_id" option="select" title="seleccione escolaridad" :error="store.errors.hasOwnProperty('datos_academicos.escolaridad_id')">
                <option value=""></option>
                <option v-for="escolaridad in catalogos.catalogo_beneficiario.escolaridades" :value="escolaridad.id">{{ escolaridad.nombre }}</option>
            </Input>
            <Input v-model="store.beneficiario.datos_academicos.titulo_carrera" option="label" title="titulo ó carrera" maxlength="100" :error="store.errors.hasOwnProperty('datos_academicos.titulo_carrera')"/>
            <div class="grid justify-items-center">
                <h1 class="uppercase text-color-4 text-center">TIPO ESTABLECIMIENTO</h1>
                <div class="flex items-center gap-1 text-gray-500">
                    <span>PÚBLICO</span>
                    <Switch class="w-auto h-6 bg-blue-500 has-[:checked]:bg-green-500" :values="['PU','PR']" v-model="store.beneficiario.datos_academicos.tipo" :error="store.errors.hasOwnProperty('datos_academicos.tipo')" />
                    <span>PRIVADO</span>
                </div>
            </div>
        </div>
    </details>
</template>