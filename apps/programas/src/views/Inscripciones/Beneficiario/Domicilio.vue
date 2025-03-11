<script setup>
    import { useBeneficiariosStore } from '@/stores/Inscripciones/beneficiarios'
    import { useCatalogosStore } from '@/stores/Catalogos/catalogos'
    import { watchEffect } from 'vue';

    const store = useBeneficiariosStore()
    const catalogos = useCatalogosStore()

    watchEffect(() => {
        catalogos.getMunicipiosDepartamento()
        catalogos.getGruposZonas()
    })

</script>

<template>
    <details :open="true" class="border-t-2 py-4">
        <summary class="text-color-4 text-lg mb-3 cursor-pointer hover:bg-gray-100 rounded-lg font-medium">DIRECCIÓN DOMICILIAR</summary>
        <div class="grid lg:grid-cols-2 gap-4">
            <Input v-model="store.beneficiario.domicilio.departamento_id" option="select" title="*seleccione departamento" :error="store.errors.hasOwnProperty('domicilio.departamento_id')" required>
                <option value=""></option>
                <option v-for="departamento in catalogos.catalogo_beneficiario.departamentos" :value="departamento.id">{{ departamento.nombre }}</option>
            </Input>
            <div class="relative">
                <Input v-model="store.beneficiario.domicilio.municipio_id" option="select" title="*seleccione municipio" :error="store.errors.hasOwnProperty('domicilio.municipio_id')" required>
                    <option value=""></option>
                    <option v-for="municipio in catalogos.municipios" :value="municipio.id">{{ municipio.nombre }}</option>
                </Input>
                <Icon v-if="catalogos.loading.municipios" icon="fas fa-spinner" spin class="text-gray-400 absolute top-6 right-6" />
            </div>
            <Input v-model="store.beneficiario.domicilio.zona_id" option="select" title="zonas" :error="store.errors.hasOwnProperty('domicilio.zona_id')">
                <option value=""></option>
                <option v-for="zona in catalogos.catalogo_beneficiario.zonas" :value="zona.id">{{ zona.descripcion }}</option>
            </Input>
            <Input @change="catalogos.getGruposZonas()" v-model="store.beneficiario.domicilio.grupo_habitacional_id" option="select" title="seleccione grupo" :error="store.errors.hasOwnProperty('domicilio.grupo_habitacional_id')">
                <option value=""></option>
                <option v-for="grupo_habitacional in catalogos.catalogo_beneficiario.grupos_habitacionales" :value="grupo_habitacional.id">{{ grupo_habitacional.nombre }}</option>
            </Input>
            <div class="relative">
                <Input v-model="store.beneficiario.domicilio.grupo_zona_id" option="select" title="selecicone grupo habitacional" :error="store.errors.hasOwnProperty('domicilio.grupo_zona_id')">
                    <option value=""></option>
                    <option v-for="grupo_zona in catalogos.grupos_zonas" :value="grupo_zona.id">{{ grupo_zona.descripcion }}</option>
                </Input>
                <Icon v-if="catalogos.loading.grupos_zonas" icon="fas fa-spinner" spin class="text-gray-400 absolute top-6 right-6" />
            </div>
            <Input v-model="store.beneficiario.domicilio.direccion" option="label" title="*dirección" maxlength="100" :error="store.errors.hasOwnProperty('domicilio.direccion')" required/>
        </div>
    </details>
</template>