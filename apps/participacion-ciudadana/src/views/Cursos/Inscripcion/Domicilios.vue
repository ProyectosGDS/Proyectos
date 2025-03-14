<script setup>
    import { useInscripcionStore } from '@/stores/inscripcion'
    import { useCatalogosStore } from '@/stores/catalogos'

    const store = useInscripcionStore()
    const catalogos = useCatalogosStore()

</script>

<template>
    <details class="border p-4 rounded-lg border-color-4">
        <summary class="cursor-pointer">DIRECCIÓN DOMICILIAR </summary>
        <br>
        <div class="flex flex-wrap gap-4">
            <div class="grow">
                <span class="uppercase">departamento</span>
                <select v-model="store.beneficiario.domicilios.departamento_id" class="input focus:outline-none uppercase">
                    <option value=""> -- SELECCIONE -- </option>
                    <option v-for="departamento in catalogos.catalogos.departamentos" :value="departamento.id">{{ departamento.nombre }}</option>
                </select>
            </div>
            <div class="grow">
                <span class="uppercase">municipio</span>
                <select v-model="store.beneficiario.domicilios.municipio_id" class="input focus:outline-none uppercase" :class="{'border-red-400':store.errors.hasOwnProperty('domicilios.municipio_id')}">
                    <option value=""> -- SELECCIONE -- </option>
                    <option v-for="municipio in catalogos.catalogos.municipios" :value="municipio.id">{{ municipio.nombre }}</option>
                </select>
            </div>
        </div>     
        <!-- <div class="grid xl:flex gap-4">
            <div class="grow">
                <span class="uppercase">numero vía</span>
                <input v-model="store.beneficiario.domicilios.numero_via" type="number" class="input focus:outline-none" :class="{'border-red-400':store.errors.hasOwnProperty('domicilios.numero_via')}" >
            </div>
            <div class="grow">
                <span class="uppercase">via</span>
                <select v-model="store.beneficiario.domicilios.id_via" class="input focus:outline-none uppercase" :class="{'border-red-400':store.errors.hasOwnProperty('domicilios.via')}">
                    <option value=""> -- SELECCIONE -- </option>
                    <option v-for="via in catalogos.catalogos.vias" :value="via.id_via">{{ via.descripcion }}</option>
                </select>
            </div>
            <div class="grow">
                <span class="uppercase">nomenclatura</span>
                <input v-model="store.beneficiario.domicilios.nomenclatura" type="text" maxlength="10" class="input focus:outline-none" :class="{'border-red-400':store.errors.hasOwnProperty('domicilios.nomenclatura')}" >
            </div>
        </div> -->
        <!-- <div class="flex flex-wrap gap-4">
            <div class="grow">
                <span class="uppercase">complemento</span>
                <input v-model="store.beneficiario.domicilios.complemento" type="text" maxlength="150" class="input focus:outline-none" :class="{'border-red-400':store.errors.hasOwnProperty('domicilios.complemento')}" >
            </div>
        </div> -->
        <div class="flex flex-wrap gap-4">
            <div class="grow">
                <span class="uppercase">zona</span>
                <select v-model="store.beneficiario.domicilios.zona" class="input focus:outline-none uppercase" :class="{'border-red-400':store.errors.hasOwnProperty('domicilios.zona')}">
                    <option value=""> -- SELECCIONE -- </option>
                    <option v-for="zona in catalogos.catalogos.zonas" :value="zona.id_zona">{{ zona.descripcion }}</option>
                </select>
            </div>
            <div class="grow">
                <span class="uppercase">tipo grupo habitacional</span>
                <select v-model="store.beneficiario.domicilios.grupo_x_zona.id_grupo" class="input focus:outline-none uppercase">
                    <option value=""> -- SELECCIONE -- </option>
                    <option v-for="grupo_habitacional in catalogos.catalogos.grupo_habitacional" :value="grupo_habitacional.id_grupo">{{ grupo_habitacional.descripcion }}</option>
                </select>
            </div>
            <div class="grow">
                <span class="uppercase">grupo habitacional</span>
                <select v-model="store.beneficiario.domicilios.id_grupo_zona" class="input focus:outline-none uppercase">
                    <option value=""> -- SELECCIONE -- </option>
                    <option v-for="grupo_zona in catalogos.catalogos.grupos_x_zona" :value="grupo_zona.id_grupo_zona">{{ grupo_zona.descripcion }}</option>
                </select>
            </div>
        </div>
        <div class="flex flex-wrap gap-4">
            <div class="grow">
                <span class="uppercase">dirección</span>
                <input v-model="store.beneficiario.domicilios.direccion" type="text" maxlength="150" class="input focus:outline-none" :class="{'border-red-400':store.errors.hasOwnProperty('domicilios.direccion')}" >
            </div>
        </div>
    </details>
</template>