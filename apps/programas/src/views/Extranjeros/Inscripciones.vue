<script setup>
import { useInscripcionesStore } from '@/stores/Extranjeros/inscripciones'
import { useCatalogosStore } from '@/stores/Catalogos/catalogos'
import { useExtranjerosStore } from '@/stores/Extranjeros/extranjeros'

import { computed, onMounted, watchEffect } from 'vue'

import DatosAcademicos from './Beneficiario/DatosAcademicos.vue'
import DatosMedicos from './Beneficiario/DatosMedicos.vue'
import DatosPersonales from './Beneficiario/DatosPersonales.vue'
import Domicilio from './Beneficiario/Domicilio.vue'
import Emergencia from './Beneficiario/Emergencia.vue'
import Responsable from './Beneficiario/Responsable.vue'

const store = useInscripcionesStore()
const catalogos = useCatalogosStore()
const extranjeros = useExtranjerosStore()

const currentYear = new Date().getFullYear();

const years = computed(() => {
    const yearsList = []
    for (let i = 0; i <= 1; i++) {
        yearsList.unshift(currentYear - i)
    }
    yearsList.push(currentYear + 1)
    return yearsList
})

onMounted(() => {

    catalogos.getCatalogoBeneficiario()
    const year = new Date()
    store.inscripcion.year = year.getFullYear() + 1
})

watchEffect(() => {
    if(extranjeros.pasaporte == '') {
        extranjeros.resetData()
        extranjeros.codeResponse = null
        extranjeros.messageResponse = null
    }
})

</script>

<template>
    <Card class="bg-white p-4 xl:p-8">
        <div class="flex justify-center">
            <div class="w-full max-w-4xl space-y-4 border-2 p-4 xl:p-8 rounded-lg">
                <div class="grid grid-cols-2 gap-4">
                    <Input v-model="store.inscripcion.year" option="select" title="*seleccione año inscripción" :error="store.errors.hasOwnProperty('year')" required>
                        <option v-for="year in years" :value="year">{{ year }}</option>
                    </Input>
                    <Input v-model="store.inscripcion.tipo" option="select" title="*Seleccione tipo de asignación" :error="store.errors.hasOwnProperty('tipo')" required>
                        <option value="modulo">módulo</option>
                        <option value="curso">curso</option>
                        <option value="actividad">actividad</option>
                    </Input>
                    <Input v-model="store.inscripcion.codigo" option="label" title="*Código de asignación" type="number" :error="store.errors.hasOwnProperty('codigo')" required />
                    <div class="relative">
                        <Input 
                            @keypress.enter="extranjeros.searchExtranjero" 
                            v-model="extranjeros.pasaporte" 
                            option="label" 
                            title="*Pasaporte"  
                            required 
                            type="search"
                            :error="extranjeros.errors.hasOwnProperty('pasaporte')"
                        />
                        <Icon v-if="extranjeros.loading.searchExtranjero" icon="fas fa-spinner" class="animate-spin absolute top-3 right-3 text-gray-400" />
                    </div>
                </div>
                <div v-if="extranjeros.messageResponse" class="col-span-2 flex justify-center items-center">
                    <span class="text-center text-sm font-medium" :class="{'text-red-400' : extranjeros.codeResponse == 3, 'text-green-400' : [1,2].includes(extranjeros.codeResponse)}">
                        {{ extranjeros.messageResponse }}
                    </span>
                </div>
                <Input v-if="[null,1,'error'].includes(extranjeros.codeResponse)" v-model="extranjeros.extranjero.nombre_completo" option="label" title="Beneficiario extrangero" readonly />
                <div v-else >
                    <DatosPersonales />
                    <Domicilio />
                    <DatosAcademicos />
                    <DatosMedicos />
                    <Responsable v-if="extranjeros.extranjero.edad < 18" />
                    <Emergencia />
                </div>
                <div class="fixed top-14 left-[50%] -translate-x-1/2 -translate-y-1/2">
                    <Button v-if="extranjeros.codeResponse == 1" @click="store.inscripcionExtranjero()" text="Asignar extrangero" class="btn-primary" icon="fas fa-save" :loading="store.loading.inscripcion" />
                    <Button v-else @click="store.inscripcionExtranjero()" text="Guardar y asignar extrangero" class="btn-primary" icon="fas fa-save" :loading="store.loading.inscripcion" />
                </div>
            </div>
        </div>
    </Card>
</template>