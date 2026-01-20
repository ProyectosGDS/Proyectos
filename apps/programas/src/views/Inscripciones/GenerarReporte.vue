<script setup>
    import { computed, onMounted } from 'vue'
    import { useGenerarReporteStore } from '@/stores/Inscripciones/generar-reporte'
    import { useCatalogosStore } from '@/stores/Catalogos/catalogos'
    import { useAuthStore } from '@/stores/auth'
    import { useProgramasStore } from '@/stores/Catalogos/programas'

    const store = useGenerarReporteStore()
    const catalogos = useCatalogosStore()
    const auth = useAuthStore()
    const programas = useProgramasStore()

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
        
        if(['5','8'].includes(auth.dependencia_id)) {
            catalogos.getEscuelas(auth.dependencia_id)
        }else {
            programas.fetch()
        }

        store.anio_inscripcion = JSON.parse(localStorage.getItem('anio_electivo')) ?? null
    })
</script>
<template>
    <Card v-if="auth.checkPermission('generar reporte completo excel')" class="bg-white px-4 py-8">
        <div class="flex justify-center items-center">
            <div class="grid gap-4">
                <Input 
                    v-model="store.anio_inscripcion" 
                    option="select" 
                    title="AÑO DE INSCRIPCION *" >

                    <option value=""> -- Seleccione año -- </option>
                    <option v-for="year in years" :value="year">{{ year }}</option>

                </Input>
                <Input 
                    v-if="['5','8'].includes(auth.user.dependencia_id)" 
                    @change="programas.getProgramasFromEscuelas(store.escuela_id)"
                    v-model="store.escuela_id"
                    option="select" 
                    title="ESCUELAS *" >

                    <option value=""> -- Seleccione escuela -- </option>
                    <template v-for="escuela in catalogos.escuelas">
                        <option :value="escuela.id">{{ escuela.nombre }}</option>
                    </template>

                </Input>
                <summary v-if="programas.programas.length">
                    <legend>SELECCIONE PROGRAMAS</legend>
                    <br>
                    <div class="grid xl:grid-cols-4 gap-3">
                        <template v-for="programa in programas.programas">
                            <label v-if="programa.estado == 'A'" class="flex gap-2 items-center">
                                <input v-model="store.programas" type="checkbox" :value="programa.id" >
                                {{ programa.id+' - '+programa.nombre }}
                            </label>
                        </template>
                    </div>
                </summary>
                <Button 
                    @click="store.reporte_excel"
                    text="Descargar reporte en excel" 
                    icon="file-excel" 
                    class="btn-primary" 
                    :loading="store.loading" 
                />
            </div>
        </div>
    </Card>
</template>