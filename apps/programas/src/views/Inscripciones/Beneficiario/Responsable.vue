<script setup>
    import { useBeneficiariosStore } from '@/stores/Inscripciones/beneficiarios'
    import { useCatalogosStore } from '@/stores/Catalogos/catalogos'

    const store = useBeneficiariosStore()
    const catalogos = useCatalogosStore()

</script>

<template>
    <details :open="true" class="border-t-2 py-4">
        <summary class="text-color-4 text-lg mb-3 cursor-pointer hover:bg-gray-100 rounded-lg font-medium">RESPONSABLE</summary>
        <div class="grid lg:grid-cols-2 gap-4">
            <Input v-model="store.beneficiario.responsable.cui" option="label" title="*cui" maxlength="13" :error="store.errors.hasOwnProperty('responsable.cui')" />
            <Input v-model="store.beneficiario.responsable.fecha_nacimiento" option="label" title="*fecha nacimiento" type="date" :error="store.errors.hasOwnProperty('responsable.fecha_nacimiento')"/>
            <Input v-model="store.beneficiario.responsable.nombres" option="label" title="*nombres" maxlength="150" :error="store.errors.hasOwnProperty('responsable.nombres')"/>
            <Input v-model="store.beneficiario.responsable.apellidos" option="label" title="*apellidos" maxlength="150" :error="store.errors.hasOwnProperty('responsable.apellidos')"/>
            <Input v-model="store.beneficiario.responsable.celular" option="label" title="*celular" maxlength="8" :error="store.errors.hasOwnProperty('responsable.celular')"/>
            <Input v-model="store.beneficiario.responsable.email" option="label" title="email" type="email" maxlength="60" :error="store.errors.hasOwnProperty('responsable.email')"/>
            <Input v-model="store.beneficiario.responsable.zona_id" option="select" title="seleccione zona" :error="store.errors.hasOwnProperty('responsable.zona_id')">
                <option value=""></option>
                <option v-for="zona in catalogos.catalogo_beneficiario.zonas" :value="zona.id">{{ zona.descripcion }}</option>
            </Input>
            <Input v-model="store.beneficiario.responsable.direccion" option="label" title="direccion" maxlength="200" :error="store.errors.hasOwnProperty('responsable.direccion')"/>
            <div class="grid justify-items-center">
                <h1 class="uppercase text-color-4 text-center">*sexo</h1>
                <div    
                    class="flex flex-col items-center gap-1 rounded-lg p-1" 
                    :class="{'border border-red-500' : store.errors.hasOwnProperty('responsable.sexo') }" >
                    <label class="flex gap-3 items-center">
                        <input 
                            v-model="store.beneficiario.responsable.sexo" 
                            type="radio" 
                            name="responsable-sexo" 
                            value="M"
                        >
                        <small>Masculino</small>
                    </label>
                    <label class="flex gap-3 items-center">
                        <input 
                            v-model="store.beneficiario.responsable.sexo" 
                            type="radio" 
                            name="responsable-sexo" 
                            value="F"
                        >
                        <small>Femenino</small>
                    </label>
                </div>
            </div>
            <Input v-model="store.beneficiario.responsable.parentesco_id" option="select" title="*seleccione parentesco" :error="store.errors.hasOwnProperty('responsable.parentesco_id')">
                <option value=""></option>
                <option v-for="parentesco in catalogos.catalogo_beneficiario.parentescos" :value="parentesco.id">{{ parentesco.nombre }}</option>
            </Input>
        </div>
    </details>
</template>