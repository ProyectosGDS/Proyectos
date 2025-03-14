<script setup>
    import { useInscripcionStore } from '@/stores/inscripcion'
    import { useCatalogosStore } from '@/stores/catalogos'

    const store = useInscripcionStore()
    const catalogos = useCatalogosStore()

</script>

<template>
    <details class="border p-4 rounded-lg border-color-4">
        <summary class="cursor-pointer">INFORMACIÓN ACADEMICA </summary>
        <br>

        <div class="grid xl:flex gap-4">
            <div class="grow">
                <span class="uppercase">establecimiento educativo</span>
                <input v-model="store.beneficiario.datos_academicos.establecimiento" type="text" maxlength="80" class="input focus:outline-none" :class="{'border-red-400':store.errors.hasOwnProperty('datos_academicos.establecimiento')}" >
            </div>
            <div class="grow">
                <span class="text-red-500">*</span>
                <span class="uppercase">escolaridad</span>
                <select v-model="store.beneficiario.datos_academicos.id_escolaridad" class="input focus:outline-none uppercase" :class="{'border-red-400':store.errors.hasOwnProperty('datos_academicos.id_escolaridad')}">
                    <option value=""> -- SELECCIONE -- </option>
                    <option v-for="escolaridad in catalogos.catalogos.escolaridades" :value="escolaridad.id_escolaridad">{{ escolaridad.descripcion }}</option>
                </select>
            </div>
        </div>
        <div class="grid xl:flex gap-4 items-center">
            <div class="grow">
                <span class="uppercase">titulo o carrera</span>
                <input v-model="store.beneficiario.datos_academicos.titulo" type="text" maxlength="80" class="input focus:outline-none" :class="{'border-red-400':store.errors.hasOwnProperty('datos_academicos.titulo')}" >
            </div>
            <div class="grow">
                <span class="text-red-500">*</span>
                <span class="uppercase">tipo de establecimiento</span>
                <div class="flex items-center gap-3" :class="{'text-red-400' : store.errors.hasOwnProperty('datos_academicos.tipo_establecimiento')}">
                    <span>PUBLICO</span>
                    <Switch class="w-14 h-7 bg-blue-500" :values="['PU','PR']" v-model="store.beneficiario.datos_academicos.tipo_establecimiento" :error="store.errors.hasOwnProperty('datos_academicos.tipo_establecimiento')" />
                    <span>PRIVADO</span>
                </div>
            </div> 
        </div>
    </details>
</template>