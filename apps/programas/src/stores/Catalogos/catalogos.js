import axios from 'axios'
import { defineStore } from 'pinia'
import { ref } from 'vue'
import { useGlobalStore } from '@/stores/global'
import { useBeneficiariosStore } from '@/stores/Inscripciones/beneficiarios'

export const useCatalogosStore = defineStore('catalogos', () => {
    
    const beneficiarios = useBeneficiariosStore()
    const global = useGlobalStore()

    const dependencias = ref([])
    const departamento = ref({})
    const municipios = ref([])
    const catalogo_beneficiario = ref([])
    const catalogos_curso = ref([])
    const catalogos_actividad = ref([])
    const zonas = ref([])
    const grupos_zonas = ref([])
    const distritos = ref([])
    const temporalidades = ref([])
    const tipos_actividades = ref([])

    const loading = ref({
        dependencia : false,
        zona : false,
        distrito : false,
        temporalidad : false,
        catalogo_beneficiario : false,
        departamentos : false,
        grupos_zonas : false,
        tipos_actividades : false,
    })
    const errors = ref([])

    const getDependencias = async () => {
        loading.value.dependencia = true
        try {
            const response = await axios.get('dependencias')
            dependencias.value = response.data
        } catch (error) {
            global.manejarError(error)
            if(error.status === 422) {
                errors.value = error.response.data.errors
            }
        } finally {
            loading.value.dependencia = false
        }
    }

    const getZonas = async () => {
        loading.value.zona = true
        try {
            const response = await axios.get('zonas')
            zonas.value = response.data
        } catch (error) {
            global.manejarError(error)
            if(error.status === 422) {
                errors.value = error.response.data.errors
            }
        } finally {
            loading.value.zona = false
        }
    }

    const getDistritos = async () => {
        loading.value.distrito = true
        try {
            const response = await axios.get('distritos')
            distritos.value = response.data
        } catch (error) {
            global.manejarError(error)
            if(error.status === 422) {
                errors.value = error.response.data.errors
            }
        } finally {
            loading.value.distrito = false
        }
    }

    const getTemporalidades = async () => {
        loading.value.temporalidad = true
        try {
            const response = await axios.get('temporalidades')
            temporalidades.value = response.data
        } catch (error) {
            global.manejarError(error)
            if(error.status === 422) {
                errors.value = error.response.data.errors
            }
        } finally {
            loading.value.temporalidad = false
        }
    }

    const getCatalogosCurso = async () => {
        loading.value.dependencia = true
        try {
            const response = await axios.get('catalogos-curso')
            catalogos_curso.value = response.data
        } catch (error) {
            global.manejarError(error)
            if(error.status === 422) {
                errors.value = error.response.data.errors
            }
        } finally {
            loading.value.dependencia = false
        }
    }

    const getCatalogosActividad = async () => {
        loading.value.dependencia = true
        try {
            const response = await axios.get('catalogos-actividad')
            catalogos_actividad.value = response.data
        } catch (error) {
            global.manejarError(error)
            if(error.status === 422) {
                errors.value = error.response.data.errors
            }
        } finally {
            loading.value.dependencia = false
        }
    }

    const getCatalogoBeneficiario = async () => {
        loading.value.catalogo_beneficiario = true
        try {
            const response = await axios.get('catalogos')
            catalogo_beneficiario.value = response.data
        } catch (error) {
            global.manejarError(error)
            if(error.status === 422) {
                errors.value = error.response.data.errors
            }
        } finally {
            loading.value.catalogo_beneficiario = false
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

    const getTiposActividades = async () => {
        loading.value.tipos_actividades = true
        try {                
            const response = await axios.get('tipos-actividades')
            tipos_actividades.value = response.data
        } catch (error) {
            global.manejarError(error)
            if(error.status === 422) {
                errors.value = error.response.data.errors
            }
        } finally {
            loading.value.tipos_actividades = false
        }
    }


    return {
        catalogo_beneficiario,
        catalogos_actividad,
        tipos_actividades,
        catalogos_curso,
        dependencias,
        departamento,
        municipios,
        zonas,
        distritos,
        grupos_zonas,
        temporalidades,
        errors,
        loading,

        getTemporalidades,
        getDependencias,
        getZonas,
        getDistritos,
        getCatalogosCurso,
        getCatalogoBeneficiario,
        getCatalogosActividad,
        getMunicipiosDepartamento,
        getGruposZonas,
        getTiposActividades,
    }
})
