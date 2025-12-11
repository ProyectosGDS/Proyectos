import { defineStore } from 'pinia'
import { ref } from 'vue'
import axios from 'axios'
import { useExtranjerosStore } from './extranjeros'
import { useGlobalStore } from '../global'

export const useInscripcionesStore = defineStore('inscripciones', () => {

    const global = useGlobalStore()
    const extranjeros = useExtranjerosStore()

    const inscripcion = ref({
        year : null,
        tipo : null,
        codigo : null,
        extranjero_id : null
    })

    const loading = ref({
        inscripcion : false,
    })

    const errors = ref([])

    const inscripcionExtranjero = async () => {
        loading.value.inscripcion = true
        try {

            if([2,3].includes(extranjeros.codeResponse)) {
                await extranjeros.store()
            }

            inscripcion.value.extranjero_id = extranjeros.extranjero.id
            
            const response = await axios.post('inscripciones-extranjeros', inscripcion.value)

            global.setAlert(response.data.message,'success')
            extranjeros.resetData()

        } catch (error) {
            
            if(error.response.code == 422) {
                errors.value = error.response.data.errors
            }

            global.manejarError(error)
        } finally {
            loading.value.inscripcion = false
        }
    }

    const resetData = () => {
        inscripcion.value = {
            codigo : null,
            extranjero_id : null
        }
    }


    return {
        inscripcion,
        loading,
        errors,

        inscripcionExtranjero,
        resetData
    }
})
