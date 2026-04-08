<script setup>
    import { useAuthStore } from '@/stores/auth'
    import { useCatalogosStore } from '@/stores/Catalogos/catalogos'
    import { useCargosStore } from '@/stores/Cargos/cargos'
    import { computed, onMounted } from 'vue'

    const store = useCargosStore()
    const auth = useAuthStore()
    const catalogos = useCatalogosStore()
    const dependencia_id = JSON.parse(localStorage.getItem('dependencia_id'))


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
        store.anio = JSON.parse(localStorage.getItem('anio_electivo')) ?? null
        catalogos.getEscuelas(dependencia_id)
    })

</script>
<template>
    <Card class="bg-white p-4 xl:p-8">
        <div class="flex justify-center">
            <div class="space-y-4 ">
                <Input v-model="store.anio" option="select" title="*seleccione año" :error="store.errors.hasOwnProperty('anio')">
                    <option v-for="year in years" :value="year">{{ year }}</option>
                </Input>
                <Input v-model="store.mes" option="select" title="*seleccione mes" :error="store.errors.hasOwnProperty('mes')">
                    <option value="01">ENERO</option>
                    <option value="02">FEBRERO</option>
                    <option value="03">MARZO</option>
                    <option value="04">ABRIL</option>
                    <option value="05">MAYO</option>
                    <option value="06">JUNIO</option>
                    <option value="07">JULIO</option>
                    <option value="08">AGOSTO</option>
                    <option value="09">SEPTIEMBRE</option>
                    <option value="10">OCTUBRE</option>
                    <option value="11">NOVIEMBRE</option>
                    <option value="12">DICIEMBRE</option>
                </Input>
                <Input @change="store.getProgramasEscuela" v-model="store.escuela_id" option="select" title="*Seleccione escuela">
                    <option value=""></option>
                    <template v-for="escuela in catalogos.escuelas">
                        <option v-if="escuela.objeto_contrato" :value="escuela.id">{{ escuela.nombre }}</option>
                    </template>
                </Input>
                <table>
                    <thead>
                        <tr>
                            <th>PROGRAMA</th>
                            <th>BENEFICIARIOS VALIDOS INSCRITOS</th>
                            <th></th>
                            <th>PARTIDAS GENERADAS</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="programa in store.programasEscuela" :key="programa.id">
                            <td>{{ programa.nombre }}</td>
                            <td align="center">{{ programa.count_beneficiarios }}</td>
                            <td>
                                <Button 
                                    @click="store.generarCargosPrograma(programa.id)" 
                                    text="Generar partidas" 
                                    class="btn-primary" 
                                    :disabled="store.programasGenerados.includes(programa.id)" 
                                    :loading="store.programasGenerados.includes(programa.id) ? store.loading.cargosPrograma : false"
                                />
                            </td>
                            <td align="center">
                                {{ programa.partidas_generadas ?? null }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </Card>
</template>