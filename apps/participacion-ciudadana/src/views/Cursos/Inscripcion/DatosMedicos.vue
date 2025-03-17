<script setup>
    import { useBeneficiariosStore } from '@/stores/Inscripciones/beneficiarios'
    import { useCatalogosStore } from '@/stores/Catalogos/catalogos'

    const store = useBeneficiariosStore()
    const catalogos = useCatalogosStore()

</script>

<template>
    <details class="border-t-2 py-4">
        <summary class="text-color-4 text-lg mb-3 cursor-pointer hover:bg-gray-100 rounded-lg font-medium">DATOS MÉDICOS</summary>
        <div class="grid lg:grid-cols-2 gap-4">
            <Input v-model="store.beneficiario.datos_medicos.enfermedades_alergias" option="label" title="enfermedades o alergias" maxlength="500" :error="store.errors.hasOwnProperty('datos_medicos.enfermedades_alergias')"/>
            <Input v-model="store.beneficiario.datos_medicos.medicamentos" option="label" title="medicamentos" maxlength="500" :error="store.errors.hasOwnProperty('datos_medicos.medicamentos')"/>
            <Input v-model="store.beneficiario.datos_medicos.dosis" option="label" title="dosis de medicamentos" maxlength="150" :error="store.errors.hasOwnProperty('datos_medicos.dosis')"/>
            <Input v-model="store.beneficiario.datos_medicos.tipo_sangre_id" option="select" title="seleccione tipo sangre" :error="store.errors.hasOwnProperty('datos_medicos.tipo_sangre_id')">
                <option value=""></option>
                <option v-for="tipo_sangre in catalogos.catalogo_beneficiario.tipos_sangre" :value="tipo_sangre.id">{{ tipo_sangre.nombre }}</option>
            </Input>
        </div>
    </details>
</template>