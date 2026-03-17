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

    function verifyCui () {
        const cui = store.cui;
        clearCui()
        if(!cui){
            store.messageCui = 'Ingrese cui'
            store.success = false
            return false 
        }

        if (cui.length !== 13 || !/^[0-9]{4}\s?[0-9]{5}\s?[0-9]{4}$/.test(cui)) {
            store.messageCui = 'Cui invalido'
            store.success = false
            return false
        }

        const cleanCui = cui.replace(/\s/g, '');
        const depto = parseInt(cleanCui.substring(9, 11), 10);
        const muni = parseInt(cleanCui.substring(11, 13), 10);
        const numero = cleanCui.substring(0, 8);
        const verificador = parseInt(cleanCui.substring(8, 9), 10);

        const munisPorDepto = [
            { id: 1, cantidad: 17 }, { id: 2, cantidad: 8 }, { id: 3, cantidad: 16 },
            { id: 4, cantidad: 16 }, { id: 5, cantidad: 13 }, { id: 6, cantidad: 14 },
            { id: 7, cantidad: 19 }, { id: 8, cantidad: 8 }, { id: 9, cantidad: 24 },
            { id: 10, cantidad: 21 }, { id: 11, cantidad: 9 }, { id: 12, cantidad: 30 },
            { id: 13, cantidad: 32 }, { id: 14, cantidad: 21 }, { id: 15, cantidad: 8 },
            { id: 16, cantidad: 17 }, { id: 17, cantidad: 14 }, { id: 18, cantidad: 5 },
            { id: 19, cantidad: 11 }, { id: 20, cantidad: 11 }, { id: 21, cantidad: 7 },
            { id: 22, cantidad: 17 }
        ];

        if (depto === 0 || muni === 0 || depto > munisPorDepto.length || muni > munisPorDepto[depto - 1].cantidad) {
            store.messageCui = 'Cui invalido'
            store.success = false
            return false
        }

        const total = numero.split('').reduce((acc, digit, index) => acc + digit * (index + 2), 0)


        if (total % 11 === verificador) {
            store.beneficiario = {
                sexo : 'M',
                domicilio : {
                    departamento_id : 7,
                    grupo_zona : {},
                },
                datos_academicos : {},
                datos_medicos : {},
                responsable : {},
                emergencia : {},
                estado : 'V',
            }
            store.getBeneficiarioUnico(cleanCui)
            store.beneficiario.cui = cleanCui    
            return true
        }

        store.messageCui = 'Cui invalido'
        store.success = false
        return false
    }

    function clearCui() {
        if(store.cui == '') {
            store.nuevo_registro = false
            store.errors = []
            store.beneficiario = {
                sexo : 'M',
                domicilio : {
                    departamento_id : 7,
                    grupo_zona : {},
                },
                datos_academicos : {},
                datos_medicos : {},
                responsable : {},
                emergencia : {},
                estado : 'V',
            }
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

