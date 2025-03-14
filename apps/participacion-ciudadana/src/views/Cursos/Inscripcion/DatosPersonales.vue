<script setup>
    import { useInscripcionStore } from '@/stores/inscripcion'
    import { useCatalogosStore } from '@/stores/catalogos'
    import { watchEffect } from 'vue';

    const store = useInscripcionStore()
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
    <div>
        <details :open="true" class="border p-4 rounded-lg border-color-4">
            <summary class="cursor-pointer">DATOS GENERALES</summary>
            <br>
            <div class="flex flex-wrap gap-4">
                <div class="grow">
                    <span class="text-red-500">*</span>
                    <span class="uppercase">cui</span>
                    <input v-model="store.beneficiario.cui" type="text" maxlength="13" minlength="13" class="input focus:outline-none" :class="{'border-red-400':store.errors.hasOwnProperty('cui')}">
                </div>
                <div class="grow">
                    <span class="uppercase">nit</span>
                    <input v-model="store.beneficiario.nit" type="text" maxlength="13" minlength="13" class="input focus:outline-none">
                </div>
                <div class="grow">
                    <span class="uppercase">pasaporte</span>
                    <input v-model="store.beneficiario.pasaporte" type="text" maxlength="20" class="input focus:outline-none" >
                </div>
            </div>
    
            <div class="flex flex-wrap gap-4">
                <div class="grow">
                    <span class="text-red-500">*</span>
                    <span class="uppercase">primer nombre</span>
                    <input v-model="store.beneficiario.primer_nombre" type="text" maxlength="50" class="input focus:outline-none" :class="{'border-red-400':store.errors.hasOwnProperty('primer_nombre')}" >
                </div>
                <div class="grow">
                    <span class="uppercase">segundo nombre</span>
                    <input v-model="store.beneficiario.segundo_nombre" type="text" maxlength="50" class="input focus:outline-none" >
                </div>
                <div class="grow">
                    <span class="uppercase">tercer nombre</span>
                    <input v-model="store.beneficiario.tercer_nombre" type="text" maxlength="50" class="input focus:outline-none" >
                </div>
            </div>
    
            <div class="flex flex-wrap gap-4">
                <div class="grow">
                    <span class="text-red-500">*</span>
                    <span class="uppercase">primer apellido</span>
                    <input v-model="store.beneficiario.primer_apellido" type="text" maxlength="50" class="input focus:outline-none" :class="{'border-red-400':store.errors.hasOwnProperty('primer_apellido')}" >
                </div>
                <div class="grow">
                    <span class="uppercase">segundo apellido</span>
                    <input v-model="store.beneficiario.segundo_apellido" type="text" maxlength="50" class="input focus:outline-none" >
                </div>
                <div class="grow">
                    <span class="uppercase">apellido de casada</span>
                    <input v-model="store.beneficiario.apellido_casada" type="text" maxlength="50" class="input focus:outline-none" >
                </div>
            </div>
    
            <div class="flex flex-wrap gap-4">
                <div class="grow">
                    <span class="uppercase">sexo</span>
                    <div class="flex items-center gap-3">
                        <Icon icon="fas fa-person-dress" class="text-fuchsia-500 text-5xl" />
                        <Switch class="w-14 h-7 bg-blue-500 has-[:checked]:bg-fuchsia-500" :values="['F','M']" v-model="store.beneficiario.sexo" :error="store.errors.hasOwnProperty('sexo')" />
                        <Icon icon="fas fa-person" class="text-blue-500 text-5xl" />
                    </div>
                </div>
                <div class="grow">
                    <span class="text-red-500">*</span>
                    <span class="uppercase">fecha nacimiento</span>
                    <input v-model="store.beneficiario.fecha_nacimiento" type="date" class="input focus:outline-none" :class="{'border-red-400':store.errors.hasOwnProperty('fecha_nacimiento')}" >
                </div>
                <div class="grow">
                    <span class="uppercase">edad</span>
                    <input v-model="store.beneficiario.edad" type="number" class="input focus:outline-none" readonly>
                </div>
                <div class="grow">
                    <span class="uppercase">etnia</span>
                    <select v-model="store.beneficiario.id_etnia" class="input focus:outline-none uppercase" :class="{'border-red-400':store.errors.hasOwnProperty('id_etnia')}">
                        <option value=""> -- SELECCIONE -- </option>
                        <option v-for="etnia in catalogos.catalogos.etnias" :value="etnia.id_etnia">{{ etnia.descripcion }}</option>
                    </select>
                </div> 
            </div>
            <div class="flex flex-wrap gap-4">
                <div class="grow">
                    <span class="uppercase">estado civil</span>
                    <select v-model="store.beneficiario.id_estado" class="input focus:outline-none uppercase" :class="{'border-red-400':store.errors.hasOwnProperty('id_estado')}">
                        <option value=""> -- SELECCIONE -- </option>
                        <option v-for="estado in catalogos.catalogos.estado_civil" :value="estado.id_estado">{{ estado.descripcion }}</option>
                    </select>
                </div> 
                <div class="grow">
                    <span class="uppercase">lugar de nacimiento</span>
                    <input v-model="store.beneficiario.lugar_nacimiento" type="text" maxlength="80" class="input focus:outline-none" >
                </div>
            </div>
            <div class="flex flex-wrap gap-4">
                <div class="grow">
                    <span class="text-red-500">*</span>
                    <span class="uppercase">celular</span>
                    <input v-model="store.beneficiario.celular" type="number" maxlength="8" minlength="8" class="input focus:outline-none" :class="{'border-red-400':store.errors.hasOwnProperty('celular')}">
                </div>
                <div class="grow">
                    <span class="uppercase">telefono</span>
                    <input v-model="store.beneficiario.telefono" type="number" maxlength="8" minlength="8" class="input focus:outline-none" :class="{'border-red-400':store.errors.hasOwnProperty('telefono')}">
                </div>
                <div class="grow">
                    <span class="text-red-500">*</span>
                    <span class="uppercase">correo</span>
                    <input v-model="store.beneficiario.email" type="email" class="input focus:outline-none" :class="{'border-red-400':store.errors.hasOwnProperty('email')}">
                </div>
            </div>
            <div class="flex flex-wrap gap-4">
                <div class="grow">
                    <span class="uppercase">facebook</span>
                    <input v-model="store.beneficiario.facebook" type="text" maxlength="100" class="input focus:outline-none">
                </div>
                <div class="grow">
                    <span class="uppercase">tiktok</span>
                    <input v-model="store.beneficiario.tiktok" type="text" maxlength="100" class="input focus:outline-none">
                </div>
                <div class="grow">
                    <span class="uppercase">instagram</span>
                    <input v-model="store.beneficiario.instagram" type="text" maxlength="100" class="input focus:outline-none" >
                </div>
            </div>
        </details>

    </div>
</template>