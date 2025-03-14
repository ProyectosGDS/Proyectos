<script setup>
    import { useInscripcionStore } from '@/stores/inscripcion'
    import { useCatalogosStore } from '@/stores/catalogos'

    const store = useInscripcionStore()
    const catalogos = useCatalogosStore()

</script>

<template>
    <details class="border p-4 rounded-lg border-color-4">
        <summary class="cursor-pointer">DATOS MEDICOS </summary>
        <br>
        <div class="grid xl:flex gap-4">
            <div class="grow">
                <span class="uppercase">tipo sangre</span>
                <select v-model="store.beneficiario.datos_medicos.id_tipo" class="input focus:outline-none uppercase" :class="{'border-red-400':store.errors.hasOwnProperty('datos_medicos.id_tipo')}">
                    <option value=""> -- SELECCIONE -- </option>
                    <option v-for="tipo in catalogos.catalogos.tipo_sangre" :value="tipo.id_tipo">{{ tipo.descripcion }}</option>
                </select>
            </div>
            <div class="grow">
                <span class="uppercase">¿ Padece alguna enfermedad cronica, cual ?</span>
                <select v-model="store.beneficiario.datos_medicos.id_enfermedad" class="input focus:outline-none uppercase" :class="{'border-red-400':store.errors.hasOwnProperty('datos_medicos.id_enfermedad')}">
                    <option value=""> -- SELECCIONE -- </option>
                    <option v-for="enfermedad in catalogos.catalogos.enfermedades" :value="enfermedad.id_enfermedad">{{ enfermedad.descripcion }}</option>
                </select>
            </div>
        </div>
        <div class="grid xl:flex gap-4">
            <div class="grow">
                <span class="uppercase">¿ Toma algun medicamento ?</span>
                <input v-model="store.beneficiario.datos_medicos.medicamentos" type="text" maxlength="100" class="input focus:outline-none" :class="{'border-red-400':store.errors.hasOwnProperty('datos_medicos.medicamentos')}" >
            </div>
            <div class="grow">
                <span class="uppercase">¿ Dosis de medicamento ?</span>
                <input v-model="store.beneficiario.datos_medicos.dosis" type="text" maxlength="100" class="input focus:outline-none" :class="{'border-red-400':store.errors.hasOwnProperty('datos_medicos.dosis')}" >
            </div>
        </div>
    </details>
</template>