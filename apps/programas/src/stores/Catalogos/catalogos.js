import axios from 'axios'
import { defineStore } from 'pinia'
import { ref } from 'vue'
import { useGlobalStore } from '@/stores/global'
import { useBeneficiariosStore } from '@/stores/Inscripciones/beneficiarios'
import { useExtranjerosStore } from '../Extranjeros/extranjeros'

export const useCatalogosStore = defineStore('catalogos', () => {
    
    const beneficiarios = useBeneficiariosStore()
    const extranjeros = useExtranjerosStore()
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
    const sedes = ref([])
    const escuelas = ref([])
    const tempo_tarifas = ref([])

    const loading = ref({
        dependencia : false,
        zona : false,
        distrito : false,
        temporalidad : false,
        catalogo_beneficiario : false,
        departamentos : false,
        grupos_zonas : false,
        tipos_actividades : false,
        sedes : false,
        escuelas : false,
        tempo_tarifas : false,
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

    const getSedes = async () => {
        loading.value.sedes = true
        try {
            const response = await axios.get('sedes')
            sedes.value = response.data
        } catch (error) {
            global.manejarError(error)
            if(error.status === 422) {
                errors.value = error.response.data.errors
            }
        } finally {
            loading.value.sedes = false
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

    const getMunicipiosDepartamentoExtrajeros = async () => {
        loading.value.municipios = true
        try {
            if(extranjeros.extranjero.domicilio.departamento_id) {
                const departamento_id = extranjeros.extranjero.domicilio.departamento_id
                
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

    const getGruposZonasExtranjeros = async () => {
        loading.value.grupos_zonas = true
        
        try {
            if(extranjeros.extranjero.domicilio.zona_id && extranjeros.extranjero.domicilio.grupo_habitacional_id ) {
                
                const zona_id = extranjeros.extranjero.domicilio.zona_id
                const grupo_habitacional_id = extranjeros.extranjero.domicilio.grupo_habitacional_id
                
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

    const getEscuelas = async (dependencia_id) => {
        loading.value.escuelas = true
        try {                
            const response = await axios.get('escuelas',{
                params : {
                    dependencia_id : dependencia_id
                }
            })
            escuelas.value = response.data
        } catch (error) {
            global.manejarError(error)
            if(error.status === 422) {
                errors.value = error.response.data.errors
            }
        } finally {
            loading.value.escuelas = false
        }
    }

    const getTemporalidadesTarifas = async () => {
        loading.value.tempo_tarifas = true
        try {                
            const response = await axios.get('temporalidades-tarifa')
            tempo_tarifas.value = response.data
        } catch (error) {
            global.manejarError(error)
            if(error.status === 422) {
                errors.value = error.response.data.errors
            }
        } finally {
            loading.value.tempo_tarifas = false
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
        sedes,
        escuelas,
        tempo_tarifas,
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
        getMunicipiosDepartamentoExtrajeros,
        getGruposZonas,
        getGruposZonasExtranjeros,
        getTiposActividades,
        getSedes,
        getEscuelas,
        getTemporalidadesTarifas,
    }
})
