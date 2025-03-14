import { defineStore } from 'pinia'
import { ref } from 'vue'
import { useGlobalStore } from './global'
import { useInscripcionStore } from './inscripcion'
import axios from 'axios'

export const useCatalogosStore = defineStore('catalogos', () => {
    
    const global = useGlobalStore()
    const store = useInscripcionStore()

    const catalogos = ref([])
    const loading = ref({
        fetch : false,
    })



    const errors = ref([])

    async function fetch() {
        loading.value.fetch = true
        try {
            const response = await axios.get('participacion-ciudadana/catalogos')
            catalogos.value = response.data
        } catch (error) {
            if(error.response.data.hasOwnProperty('message') && error.response.data.hasOwnProperty('line')) {
                global.setAlert(error.response.data.message,'danger')
            }else if(error.response.data.hasOwnProperty('errors') && error.response.data.hasOwnProperty('message')){
                errors.value = error.response.data.errors
            }else{
                console.error(error.response.data)
            }
        } finally {
            loading.value.fetch = false
        }
    }

    async function fetchGrupoZona () {

        try {

            if(store.beneficiario.domicilios.hasOwnProperty('zona') && store.beneficiario.domicilios.grupo_x_zona.hasOwnProperty('id_grupo') ) {

                const id_zona = store.beneficiario.domicilios.zona
                const id_grupo = store.beneficiario.domicilios.grupo_x_zona.id_grupo

                const response = await axios.get('grupo-zona/'+ id_zona +'/'+ id_grupo)
                catalogos.value.grupos_x_zona = response.data
            }
            
        } catch (error) {
            console.error(error)
        }
    }

    async function fetchMunicipiosDepartamento() {
        try {
            if(store.beneficiario.domicilios.hasOwnProperty('departamento_id')) {
                const departamento_id = store.beneficiario.domicilios.departamento_id
                const response = await axios.get('municipios-por-departamento/'+ departamento_id)
                catalogos.value.municipios = response.data.municipios
            }
            
        } catch (error) {
            console.error(error)
        }
    }

    return {
        catalogos,

        fetch,
        fetchGrupoZona,
        fetchMunicipiosDepartamento,
    }
})
