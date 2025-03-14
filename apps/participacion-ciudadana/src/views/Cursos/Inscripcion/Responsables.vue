<script setup>
    import { useInscripcionStore } from '@/stores/inscripcion'
    import { useCatalogosStore } from '@/stores/catalogos'

    const store = useInscripcionStore()
    const catalogos = useCatalogosStore()

</script>

<template>
    <details class="border p-4 rounded-lg border-color-4">
        <summary class="cursor-pointer">DATOS RESPONSABLE</summary>
        <br>
        <div class="flex flex-wrap gap-4">
            <div class="grow">
                <span class="text-red-500">*</span>
                <span class="uppercase">cui</span>
                <input v-model="store.beneficiario.responsables.cui" type="text" maxlength="13" minlength="13" class="input focus:outline-none" :class="{'border-red-400':store.errors.hasOwnProperty('responsables.cui')}">
            </div>
            <div class="grow">
                <span class="text-red-500">*</span>
                <span class="uppercase">nombre</span>
                <input v-model="store.beneficiario.responsables.nombre" type="text" maxlength="150" class="input focus:outline-none" :class="{'border-red-400':store.errors.hasOwnProperty('responsables.nombre')}" >
            </div>
            <div class="grow">
                <span class="uppercase">sexo</span>
                <div class="flex items-center gap-3">
                    <Icon icon="fas fa-person-dress" class="text-fuchsia-500 text-5xl" />
                    <Switch class="w-14 h-7 bg-blue-500 has-[:checked]:bg-fuchsia-500" :values="['F','M']" v-model="store.beneficiario.responsables.sexo" :error="store.errors.hasOwnProperty('responsables.sexo')" />
                    <Icon icon="fas fa-person" class="text-blue-500 text-5xl" />
                </div>
            </div>
        </div>

        <div class="flex flex-wrap gap-4">
            <div class="grow">
                <span class="uppercase">zona</span>
                <select v-model="store.beneficiario.responsables.zona" class="input focus:outline-none uppercase" :class="{'border-red-400':store.errors.hasOwnProperty('responsables.zona')}">
                    <option value=""> --seleccione-- </option>
                    <option v-for="zona in catalogos.catalogos.zonas" :value="zona.id_zona">{{ zona.descripcion }}</option>
                </select>
            </div>
            <div class="grow">
                <span class="uppercase">dirección domiciliar</span>
                <input v-model="store.beneficiario.responsables.domicilio" type="text" maxlength="150" class="input focus:outline-none" :class="{'border-red-400':store.errors.hasOwnProperty('responsables.domicilio')}" >
            </div>
        </div>

        <div class="flex flex-wrap gap-4">
            <div class="grow">
                <span class="text-red-500">*</span>
                <span class="uppercase">celular</span>
                <input v-model="store.beneficiario.responsables.celular" type="number" class="input focus:outline-none" :class="{'border-red-400':store.errors.hasOwnProperty('responsables.celular')}" >
            </div>
            <div class="grow">
                <span class="uppercase">correo</span>
                <input v-model="store.beneficiario.responsables.email" type="email" class="input focus:outline-none" :class="{'border-red-400':store.errors.hasOwnProperty('responsables.email')}" >
            </div>
            <div class="grow">
                <span class="text-red-500">*</span>
                <span class="uppercase">Parentesco</span>
                <select v-model="store.beneficiario.responsables.id_parentesco" class="input focus:outline-none uppercase" :class="{'border-red-400':store.errors.hasOwnProperty('responsables.id_parentesco')}">
                    <option value=""> -- SELECCIONE -- </option>
                    <option v-for="parentesco in catalogos.catalogos.parentescos" :value="parentesco.id_parentesco">{{ parentesco.descripcion }}</option>
                </select>
            </div>
        </div>
    </details>
</template>