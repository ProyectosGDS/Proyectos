import { defineStore } from 'pinia'
import { ref } from 'vue'
import { useGlobalStore } from '../global'
import { useBeneficiariosStore } from '../Inscripciones/beneficiarios'
import axios from 'axios'

export const useCatalogosStore = defineStore('catalogos', () => {
    
    const global = useGlobalStore()
    const beneficiarios = useBeneficiariosStore()

    const catalogo_beneficiario = ref([])
    const municipios = ref([])
    const grupos_zonas  = ref([])
    const loading = ref({
        fetch : false,
        municipios : false,
        grupos_zonas : false,
    })



    const errors = ref([])

    async function fetch() {
        loading.value.fetch = true
        try {
            const response = await axios.get('catalogos')
            catalogo_beneficiario.value = response.data
        } catch (error) {
            global.manejarError(error)
            if(error.status === 422) {
                errors.value = error.response.data.errors
            }
        } finally {
            loading.value.fetch = false
        }
    }

    const getGruposZonas = async () => {
        loading.value.grupos_zonas = true
        
        try {
            if(beneficiarios.beneficiario.domicilio.zona_id && beneficiarios.beneficiario.domicilio.grupo_habitacional_id ) {
                
                const zona_id = beneficiarios.beneficiario.domicilio.zona_id
                const grupo_habitacional_id = beneficiarios.beneficiario.domicilio.grupo_habitacional_id
                
                const response = await axios.get(`grupos-zonas/${zona_id}/${grupo_habitacional_id}`)
                grupos_zonas.value = response.data
            }
        } catch (error) {
            global.manejarError(error)
            if(error.status === 422) {
                errors.value = error.response.data.errors
            }
        } finally {
            loading.value.grupos_zonas = false
        }
    }

    const getMunicipiosDepartamento = async () => {
        loading.value.municipios = true
        try {
                        
            if(beneficiarios.beneficiario.domicilio.departamento_id) {
                const departamento_id = beneficiarios.beneficiario.domicilio.departamento_id
                
                const response = await axios.get(`municipios-departamento/${departamento_id}`)
                municipios.value = response.data
            }
        } catch (error) {
            global.manejarError(error)
            if(error.status === 422) {
                errors.value = error.response.data.errors
            }
        } finally {
            loading.value.municipios = false
        }
    }

    return {
        catalogo_beneficiario,
        municipios,
        grupos_zonas,
        loading,

        fetch,
        getGruposZonas,
        getMunicipiosDepartamento,
    }
})
