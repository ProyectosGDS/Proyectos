<script setup>
    import {useBeneficiariosStore} from '@/stores/Inscripciones/beneficiarios'
    import { useCatalogosStore } from '@/stores/Catalogos/catalogos'
    import { watchEffect } from 'vue'

    const store = useBeneficiariosStore()
    const catalogos = useCatalogosStore()

    function calcularEdad() {
        const fecha_nacimiento = store.beneficiario.fecha_nacimiento
        const regexFecha = /^\d{4}-\d{2}-\d{2}$/

        if(regexFecha.test(fecha_nacimiento)) { 

            const hoy = new Date()
            const fechaNac = new Date(fecha_nacimiento)

            if(fechaNac.getFullYear() > (hoy.getFullYear() - 100)) {

                let edad = hoy.getFullYear() - fechaNac.getFullYear()
                const mes = hoy.getMonth() - fechaNac.getMonth()
    
    
                if (mes < 0 || (mes === 0 && hoy.getDate() < fechaNac.getDate())) {
                    edad--;
                }
    
                store.beneficiario.edad = edad
            }

        }else {
            store.beneficiario.edad = 0
        }
    }

    watchEffect(() => {
        calcularEdad()
    })

</script>

<template>
    <details :open="true" class="pb-4">
        <summary class="text-color-4 text-lg mb-3 cursor-pointer hover:bg-gray-100 rounded-lg font-medium">DATOS PERSONALES</summary>
        <div class="grid lg:grid-cols-2 gap-4">
            <div class="col-span-2">
                <Input v-model="store.beneficiario.cui" option="label" title="*Cui" maxlength="13" type="search" :error="store.errors.hasOwnProperty('cui')" required readonly disabled />
            </div>
            <Input v-model="store.beneficiario.primer_nombre" option="label" title="*primer nombre" maxlength="45" :error="store.errors.hasOwnProperty('primer_nombre')" required />
            <Input v-model="store.beneficiario.segundo_nombre" option="label" title="segundo nombre" maxlength="45" :error="store.errors.hasOwnProperty('segundo_nombre')" />
            <Input v-model="store.beneficiario.primer_apellido" option="label" title="*primer apellido" maxlength="45" :error="store.errors.hasOwnProperty('primer_apellido')" required />
            <Input v-model="store.beneficiario.segundo_apellido" option="label" title="segundo apellido" maxlength="45" :error="store.errors.hasOwnProperty('segundo_apellido')" />
            <Input v-model="store.beneficiario.celular" type="tel" pattern="\d{8}" option="label" title="*celular"  maxlength="8" :error="store.errors.hasOwnProperty('celular')" required />
            <Input v-model="store.beneficiario.correo" type="email" option="label" title="correo" :error="store.errors.hasOwnProperty('correo')" />
            <Input v-model="store.beneficiario.fecha_nacimiento" type="date" option="label" title="*fecha nacimiento" :error="store.errors.hasOwnProperty('fecha_nacimiento')" />
            <div class="flex gap-3">
                <Input v-model="store.beneficiario.edad" type="number" min="0" option="label" title="edad" readonly :error="store.errors.hasOwnProperty('edad')"/>
                <div>
                    <h1 class="uppercase text-color-4 text-center">*sexo</h1>
                    <div class="flex items-center gap-1">
                        <Icon icon="fas fa-person-dress" class="text-fuchsia-500 text-2xl" />
                        <Switch class="w-auto h-6 bg-blue-500 has-[:checked]:bg-fuchsia-500" :values="['F','M']" v-model="store.beneficiario.sexo" :error="store.errors.hasOwnProperty('sexo')" />
                        <Icon icon="fas fa-person" class="text-blue-500 text-2xl" />
                    </div>
                </div>
            </div>
            <Input v-model="store.beneficiario.estado_civil_id" option="select" title="seleccione estado civil" :error="store.errors.hasOwnProperty('estado_civil_id')">
                <option value=""></option>
                <option v-for="estado_civil in catalogos.catalogo_beneficiario.estados_civiles" :value="estado_civil.id">{{ estado_civil.nombre }}</option>
            </Input>
            <Input v-model="store.beneficiario.etnia_id" option="select" title="seleccione etnia" :error="store.errors.hasOwnProperty('etnia_id')">
                <option value=""></option>
                <option v-for="etnia in catalogos.catalogo_beneficiario.etnias" :value="etnia.id">{{ etnia.nombre }}</option>
            </Input>
        </div>
    </details>
</template>

