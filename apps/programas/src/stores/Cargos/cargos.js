import { defineStore } from 'pinia'
import { ref } from 'vue'
import axios from 'axios'
import { useGlobalStore } from '../global'

export const useCargosStore = defineStore('cargos', () => {

    const global = useGlobalStore()


    const programasEscuela = ref([])
    const loading = ref({
        programasEscuela : false
    })

    const getProgramasEscuela = async (id) => {
        loading.value.programasEscuela = true
        programasEscuela.value = []
        try {
            const response = await axios.get('programas/programas_escuela/' + id)
            programasEscuela.value = response.data.programas_escuela
        } catch (error) {
            global.manejarError(error)
        } finally {
            loading.value.programasEscuela = false
        }
    }

    return {
        programasEscuela,
        loading,

        getProgramasEscuela,
    }
})
