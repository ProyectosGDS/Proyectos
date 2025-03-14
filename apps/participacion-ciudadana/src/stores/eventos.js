import axios from 'axios'
import { defineStore } from 'pinia'
import { ref } from 'vue'

export const useEventosStore = defineStore('eventos', () => {
    
    const eventos = ref([])
    const loading = ref({
        fetch : false
    })
    const errors = ref([])

    async function fetch () {
        loading.value.fetch = true
        try {
            const response = await axios.get('participacion-ciudadana/eventos')
            eventos.value = response.data
        } catch (error) {
            if(error.response.data.hasOwnProperty('errors')){
                errors.value = error.response.data.errors
            }
            console.error(error)
        } finally {
            loading.value.fetch = false
        }
    }

    return {
       eventos,
       loading,
       
       fetch
    }
})
