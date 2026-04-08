import { defineStore } from 'pinia'
import { ref } from 'vue'
import axios from 'axios'
import { useGlobalStore } from '../global'

export const useCargosStore = defineStore('cargos', () => {

    const global = useGlobalStore()


    const programasEscuela = ref([])
    const anio = ref(null)
    const mes = ref(null)
    const escuela_id = ref(null)
    const programasGenerados = ref([])
    const loading = ref({
        programasEscuela : false,
        cargosPrograma : false,
    })


    const errors = ref([])

    const getProgramasEscuela = async () => {
        loading.value.programasEscuela = true
        programasEscuela.value = []
        try {
            const response = await axios.get('programas/programas_escuela/' + escuela_id.value + '/' + anio.value)
            programasEscuela.value = response.data.programas_escuela
        } catch (error) {
            global.manejarError(error)
        } finally {
            loading.value.programasEscuela = false
        }
    }

    const generarCargosPrograma = async (programa_id) => {
        loading.value.cargosPrograma = true
        programasGenerados.value.push(programa_id)
        try {
            const response = await axios.post('cargos/generar-partidas/'+programa_id,{
                anio : anio.value,
                mes : mes.value
            })
            
            programasEscuela.value = programasEscuela.value.map(programa => {
                if(programa.id === programa_id) {
                    return {
                        ...programa,
                        partidas_generadas : response.data.partidas_generadas
                    }
                }
                return programa
            })

            global.setAlert(response.data.message,'success')
        } catch (error) {
            if(error.response.status === 422) {
                errors.value = error.response.data.errors
            }
            global.manejarError(error)
        } finally {
            loading.value.cargosPrograma = false
        }
    }

    return {
        programasEscuela,
        anio,
        mes,
        escuela_id,
        programasGenerados,
        loading,
        errors,

        getProgramasEscuela,
        generarCargosPrograma,
    }
})
