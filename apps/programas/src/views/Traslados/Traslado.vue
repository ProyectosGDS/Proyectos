<script setup>
import { computed, onMounted } from 'vue'
import { useTrasladoStore } from '@/stores/Traslados/traslado'

    const store = useTrasladoStore()

    const currentYear = new Date().getFullYear();

    const years = computed(() => {
        const yearsList = []
        for (let i = 0; i <= 1; i++) {
        yearsList.unshift(currentYear - i)
        }
        yearsList.push(currentYear + 1)
        return yearsList
    })

    const tipos_recursos = [
        'curso',
        'modulo',
        'actividad'
    ]

    onMounted(() => {
        store.year = JSON.parse(localStorage.getItem('anio_electivo')) ?? null
    })

</script>

<template>
    <Card class="bg-white p-4 xl:p-8">
        <div class="flex justify-center">
            
            <div class="w-full max-w-4xl space-y-4 border-2 p-4 xl:p-8 rounded-lg">
                <p class="text-sm text-center text-gray-500">
                    Módulo para traslados de beneficiarios entre recursos que pueden ser: cursos, modulos o actividades.
                    <br>
                    Al realizar el traslado el sistema verificará que el beneficiario este inscrito en el recurso actual y que el recurso nuevo tenga cupo disponible.
                    <br>
                    Elije el año de inscripción e ingresa el cui del beneficiario y presiona tecla ENTER.
                </p>

                <Input 
                    v-model="store.year" 
                    option="select" 
                    title="*año inscripción" 
                    :error="store.errors.hasOwnProperty('anio_inscripcion')" >

                    <option v-for="year in years" :value="year">{{ year }}</option>
                </Input>
                <div class="relative">
                    <Input
                        @keypress.enter="store.searchBeneficiario()"
                        v-model="store.cui"
                        option="label" 
                        title="*Cui" 
                        maxlength="13" 
                        type="search"  
                        placeholder="Ingrese CUI y presione ENTER" 
                        required
                        autofocus
                        :error="store.errors.hasOwnProperty('cui')"
                    />
                    <Icon v-if="store.loading.beneficiario" 
                        icon="fas fa-spinner" 
                        class="animate-spin absolute top-3 right-3 text-gray-500" 
                    />
                </div>
                <Loading-Bar v-if="store.loading.beneficiario" class="bg-color-4 h-1" />
                <span v-if="store.success.beneficiario.message"
                    class="text-sm"
                    :class="{
                        'text-green-500': store.success.beneficiario.code == '1', 
                        'text-red-500' : store.success.beneficiario.code == '2'
                    }" >
                    {{ store.success.beneficiario.message }}
                </span>
                <fieldset v-if="store.success.beneficiario.code == '1'" 
                    class="p-4 border rounded-lg">
                    <legend class="uppercase px-4 text-color-4">Recurso actual</legend>
                    <p>
                        <span class="text-sm text-gray-500">
                            Ingrese el id de la asignación donde esta inscrito el beneficiario y elija el tipo de recurso actual.
                        </span>
                    </p>
                    <br>
                    <div class="grid xl:grid-cols-2 gap-4">
                        <Input
                            @keypress.enter="store.validarRecursoActual()"
                            v-model.lazy="store.recurso_id_actual"
                            option="label"
                            title="*Id de asignación actual"
                            type="number"
                            min="0"
                            required
                            :error="store.errors.hasOwnProperty('recurso_id_actual')"
                        />

                        <Input
                            @change="store.validarRecursoActual()"
                            v-model="store.tipo_recurso_actual" 
                            option="select" 
                            title="*Tipo de recurso actual" 
                            :error="store.errors.hasOwnProperty('tipo_recurso_actual')" 
                            required>

                            <option v-for="tipo in tipos_recursos" :value="tipo">{{ tipo }}</option>
                        </Input>

                    </div>
                    <Loading-Bar v-if="store.loading.verificarRecursoActual" class="bg-color-4 h-1" />
                    <span v-if="store.success.recursoActual.message"
                        class="text-sm"
                        :class="{
                            'text-green-500': store.success.recursoActual.code == '1', 
                            'text-red-500' : store.success.recursoActual.code == '2'
                        }" >
                        {{ store.success.recursoActual.message }}
                    </span>
                </fieldset>

                <fieldset v-if="store.success.recursoActual.code == '1'" 
                    class="p-4 border rounded-lg">
                    <legend class="uppercase px-4 text-color-4">Recurso nuevo</legend>
                    <p>
                        <span class="text-sm text-gray-500">
                            Ingrese el id de la asignación nueva y elija tipo de recurso nuevo a donde desea trasladar al beneficiario.
                        </span>
                    </p>
                    <br>
                    <div class="grid xl:grid-cols-2 gap-4">
                        <Input 
                            @keypress.enter="store.validarRecursoNuevo()"
                            v-model="store.recurso_id_nuevo"
                            option="label"
                            title="*Id de asignación nuevo"
                            type="number"
                            min="0"
                            required
                            :error="store.errors.hasOwnProperty('recurso_id_nuevo')"
                        />

                        <Input
                            @change="store.validarRecursoNuevo()"
                            v-model="store.tipo_recurso_nuevo" 
                            option="select" 
                            title="*Tipo de recurso nuevo" 
                            :error="store.errors.hasOwnProperty('tipo_recurso_nuevo')" 
                            required>

                            <option v-for="tipo in tipos_recursos" :value="tipo">{{ tipo }}</option>
                        </Input>

                    </div>
                    <Loading-Bar v-if="store.loading.verificarRecursoNuevo" class="bg-color-4 h-1" />
                    <span v-if="store.success.recursoNuevo.message"
                        class="text-sm"
                        :class="{
                            'text-green-500': store.success.recursoNuevo.code == '1', 
                            'text-red-500' : store.success.recursoNuevo.code == '2'
                        }" >
                        {{ store.success.recursoNuevo.message }} <br>
                        {{ 'CUPOS DISPONIBLES: ' + store.success.recursoNuevo.disponiblidad ?? null }}
                    </span>
                </fieldset>
                <div v-if="store.success.recursoNuevo.code == '1'" 
                    class="flex justify-center">
                    <Button
                        @click="store.realizarTraslado()"
                        text="Realizar traslado"
                        icon="arrows-rotate"
                        class="btn-primary"
                        :loading="store.loading.traslado"
                    />
                </div>
            </div>
        </div>
    </Card>
</template>