import axios from 'axios'
import { defineStore } from 'pinia'
import { ref } from 'vue'
import { useRouter } from 'vue-router'

export const useCarrerasStore = defineStore('carreras', () => {

    const router = useRouter()
    
    const carreras = ref([])
    const carrera = ref({})
    const errors = ref([])
    const loading = ref({
        fetch : false,
        details : false,
    })
    const modal = ref({
        inscripcion : false,
    })


    async function fetch() {
        loading.value.fetch = true
        try {
            const response = await axios.get('carreras')
            carreras.value = response.data
        } catch (error) {
            if(error.response.data.hasOwnProperty('errors')){
                errors.value = error.response.data.errors
            }
            console.error(error)
        } finally {
            loading.value.fetch = false
        }
    }

    async function show(programa_id) {
        loading.value.details = true
        try {
            const response = await axios.get('detalle-carreras/'+programa_id)
            carrera.value = response.data
        } catch (error) {
            if(error.response.data.hasOwnProperty('errors')){
                errors.value = error.response.data.errors
            }
            console.error(error)
        } finally {
            loading.value.details = false
        }
    }

    function detalleCarrera (item) {
        router.push({name:'Detalle carrera',params:{ programa_id : item.id }})
    }


    
    return {
        router,
        carreras,
        carrera,
        errors,
        loading,
        modal,

        fetch,
        show,
        detalleCarrera,
    }
})
