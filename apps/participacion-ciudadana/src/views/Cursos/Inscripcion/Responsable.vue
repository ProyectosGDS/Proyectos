<script setup>
    import { useBeneficiariosStore } from '@/stores/Inscripciones/beneficiarios'
    import { useCatalogosStore } from '@/stores/Catalogos/catalogos'

    const store = useBeneficiariosStore()
    const catalogos = useCatalogosStore()

</script>

<template>
    <details class="border-t-2 py-4">
        <summary class="text-color-4 text-lg mb-3 cursor-pointer hover:bg-gray-100 rounded-lg font-medium">RESPONSABLE</summary>
        <div class="grid lg:grid-cols-2 gap-4">
            <Input v-model="store.beneficiario.responsable.cui" option="label" title="cui" maxlength="13" :error="store.errors.hasOwnProperty('responsable.cui')"/>
            <Input v-model="store.beneficiario.responsable.nombre" option="label" title="*nombre" maxlength="150" :error="store.errors.hasOwnProperty('responsable.nombre')"/>
            <Input v-model="store.beneficiario.responsable.celular" option="label" title="*celular" maxlength="12" :error="store.errors.hasOwnProperty('responsable.celular')"/>
            <Input v-model="store.beneficiario.responsable.email" option="label" title="email" type="email" maxlength="60" :error="store.errors.hasOwnProperty('responsable.email')"/>
            <Input v-model="store.beneficiario.responsable.zona_id" option="select" title="seleccione zona" :error="store.errors.hasOwnProperty('responsable.zona_id')">
                <option value=""></option>
                <option v-for="zona in catalogos.catalogo_beneficiario.zonas" :value="zona.id">{{ zona.descripcion }}</option>
            </Input>
            <Input v-model="store.beneficiario.responsable.direccion" option="label" title="direccion" maxlength="200" :error="store.errors.hasOwnProperty('responsable.direccion')"/>
            <div class="grid justify-items-center">
                <h1 class="uppercase text-color-4 text-center">*sexo</h1>
                <div class="flex items-center gap-1">
                    <Icon icon="fas fa-person-dress" class="text-fuchsia-500 text-2xl" />
                    <Switch class="w-auto h-6 bg-blue-500 has-[:checked]:bg-fuchsia-500" :values="['F','M']" v-model="store.beneficiario.responsable.sexo" :error="store.errors.hasOwnProperty('responsable.sexo')" />
                    <Icon icon="fas fa-person" class="text-blue-500 text-2xl" />
                </div>
            </div>
            <Input v-model="store.beneficiario.responsable.parentesco_id" option="select" title="*seleccione parentesco" :error="store.errors.hasOwnProperty('responsable.parentesco_id')">
                <option value=""></option>
                <option v-for="parentesco in catalogos.catalogo_beneficiario.parentescos" :value="parentesco.id">{{ parentesco.nombre }}</option>
            </Input>
        </div>
    </details>
</template>