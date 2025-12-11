<script setup>
    import { useExtranjerosStore } from '@/stores/Extranjeros/extranjeros'
    import { useCatalogosStore } from '@/stores/Catalogos/catalogos'
    import { watchEffect } from 'vue'

    const store = useExtranjerosStore()
    const catalogos = useCatalogosStore()

    function calcularEdad() {
        const fecha_nacimiento = store.extranjero.fecha_nacimiento
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
    
                store.extranjero.edad = edad
            }

        }else {
            store.extranjero.edad = 0
        }
    }

    function formatCui() {
        let cadena = store.extranjero.pasaporte
        if(cadena) {
            let numeros = cadena.replace(/\D+/g, "");
            let formatCui = numeros.padEnd(13, "0");
            store.extranjero.cui = formatCui
        }
    }

    watchEffect(() => {
        calcularEdad()
        formatCui()
    })

</script>

<template>
    <details :open="true" class="pb-4">
        <summary class="text-color-4 text-lg mb-3 cursor-pointer hover:bg-gray-100 rounded-lg font-medium">DATOS PERSONALES</summary>
        <div class="grid lg:grid-cols-2 gap-4">
            <Input v-model="store.extranjero.cui" option="label" title="*cui" maxlength="15" type="number" :error="store.errors.hasOwnProperty('cui')" required/>
            <Input v-model="store.extranjero.pasaporte" option="label" title="*Pasaporte" maxlength="15" :error="store.errors.hasOwnProperty('pasaporte')" required readonly disabled />        
            <Input v-model="store.extranjero.primer_nombre" option="label" title="*primer nombre" maxlength="45" :error="store.errors.hasOwnProperty('primer_nombre')" required />
            <Input v-model="store.extranjero.segundo_nombre" option="label" title="segundo nombre" maxlength="45" :error="store.errors.hasOwnProperty('segundo_nombre')" />
            <Input v-model="store.extranjero.primer_apellido" option="label" title="*primer apellido" maxlength="45" :error="store.errors.hasOwnProperty('primer_apellido')" required />
            <Input v-model="store.extranjero.segundo_apellido" option="label" title="segundo apellido" maxlength="45" :error="store.errors.hasOwnProperty('segundo_apellido')" />
            <Input v-model="store.extranjero.celular" type="tel" pattern="\d{8}" option="label" title="*celular"  maxlength="8" :error="store.errors.hasOwnProperty('celular')" required />
            <Input v-model="store.extranjero.correo" type="email" option="label" title="correo" :error="store.errors.hasOwnProperty('correo')" />
            <Input v-model="store.extranjero.fecha_nacimiento" type="date" option="label" title="*fecha nacimiento" :error="store.errors.hasOwnProperty('fecha_nacimiento')" />
            <div class="flex gap-3">
                <Input v-model="store.extranjero.edad" type="number" min="0" option="label" title="edad" readonly :error="store.errors.hasOwnProperty('edad')"/>
                <div>
                    <h1 class="uppercase text-color-4 text-center">*sexo</h1>
                    <div class="flex items-center gap-1">
                        <Icon icon="fas fa-person-dress" class="text-fuchsia-500 text-2xl" />
                        <Switch class="w-auto h-6 bg-blue-500 has-[:checked]:bg-fuchsia-500" :values="['F','M']" v-model="store.extranjero.sexo" :error="store.errors.hasOwnProperty('sexo')" />
                        <Icon icon="fas fa-person" class="text-blue-500 text-2xl" />
                    </div>
                </div>
            </div>
            <Input v-model="store.extranjero.estado_civil_id" option="select" title="seleccione estado civil" :error="store.errors.hasOwnProperty('estado_civil_id')">
                <option value=""></option>
                <option v-for="estado_civil in catalogos.catalogo_beneficiario.estados_civiles" :value="estado_civil.id">{{ estado_civil.nombre }}</option>
            </Input>
        </div>
    </details>
</template>

