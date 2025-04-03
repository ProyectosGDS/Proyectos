import { defineStore } from 'pinia'
import { ref } from 'vue'
import axios from 'axios'
import { useBeneficiariosStore } from './beneficiarios'
import { useGlobalStore } from './global'

export const useCatalogosStore = defineStore('catalogos', () => {

    const beneficiarios = useBeneficiariosStore()
    const global = useGlobalStore()
    
    const catalogo = ref([])
    const municipios = ref([])
    const grupos_zonas = ref([])

    const programas = ref([])
    const programa_id = ref(null)
    const modulos_cursos = ref([])
    const tipo = ref(null)

    const loading = ref({
        catalogo : false,
        municipios : false,
        grupos_zonas : false,
        programas : false,
        modulos_cursos : false,
    })
    
    const errors = ref([])

    const fetch = async () => {
        loading.value.catalogo = true
        try {
            const response = await axios.get('catalogos')
            catalogo.value = response.data
        } catch (error) {
            global.manejarError(error)
            if(error.status === 422) {
                errors.value = error.response.data.errors
            }
        } finally {
            loading.value.catalogo = false
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

    const getProgramas = async () => {
        loading.value.programas = true
        
        try {
            const response = await axios.get('programas')
            programas.value = response.data
        } catch (error) {
            global.manejarError(error)
            if(error.status === 422) {
                errors.value = error.response.data.errors
            }
        } finally {
            loading.value.programas = false
        }
    }

    const getModulosCursos = async () => {
        loading.value.modulos_cursos = true
        
        try {
            const response = await axios.get('programas/modulos-cursos', {
                params : {
                    programa_id : programa_id.value,
                    tipo : tipo.value,
                }
            })
            modulos_cursos.value = response.data
        } catch (error) {
            global.manejarError(error)
            if(error.status === 422) {
                errors.value = error.response.data.errors
            }
        } finally {
            loading.value.modulos_cursos = false
        }
    }


    
    
    return {
        catalogo,
        programas,
        programa_id,
        modulos_cursos,
        tipo,
        municipios,
        grupos_zonas,
        loading,
        errors,

        fetch,
        getMunicipiosDepartamento,
        getGruposZonas,
        getProgramas,
        getModulosCursos,
    }
})
