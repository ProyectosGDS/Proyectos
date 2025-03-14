import { defineStore } from 'pinia'
import { useGlobalStore } from './global'
import { ref } from 'vue'
import axios from 'axios'

export const useCatalogosStore = defineStore('catalogos', () => {
    
    const global = useGlobalStore()

    const catalogos_evento = ref([])

    const getCatalogos = async () => {
        try {
            const response = await axios.get('catalogos-evento')
            catalogos_evento.value = response.data
        } catch (error) {
            global.manejarError(error)
        }
    }
    
    return {
        catalogos_evento,
        
        getCatalogos,
    }
})
