import axios from 'axios'
import { defineStore } from 'pinia'
import { ref } from 'vue'
import { useGlobalStore } from '../global'

export const useTrasladoStore = defineStore('traslado', () => {
    
    const global = useGlobalStore()

    const cui = ref(null)
    const beneficiario = ref({})
    const recurso_id_actual = ref(null)
    const tipo_recurso_actual = ref(null)
    const recurso_id_nuevo = ref(null)
    const tipo_recurso_nuevo = ref(null)


    const year = ref(0);

    const loading = ref({
        beneficiario : false,
        verificarRecursoActual : false,
        verificarRecursoNuevo : false,
        traslado : false,
    })

    const errors = ref([]);
    const success = ref({
        beneficiario : {
            code : null,
            message : null,
        },
        recursoActual : {
            code : null,
            message : null,
        },
        recursoNuevo : {
            code : null,
            message : null,
            disponiblidad : null,
        }
    })
    
    const searchBeneficiario = async () => {
        loading.value.beneficiario = true
        success.value.beneficiario.code = null
        success.value.beneficiario.message = null
        errors.value = []
        try {
            const response = await axios.post('traslados-beneficiarios/busqueda-beneficiarios', {
                cui: cui.value,
                anio_inscripcion: year.value
            })

            beneficiario.value = response.data.beneficiario ?? {}
            success.value.beneficiario.code = response.data.code
            success.value.beneficiario.message = (response.data.code == '1') ? 'BENEFICIARIO ENCONTRADO : ' + response.data.beneficiario.nombre_completo : response.data.message 

        } catch (error) {
            if(error.status === 422) {
                errors.value = error.response.data.errors
            }
        } finally {
            loading.value.beneficiario = false
        }
    }

    const validarRecursoActual = async () => {
        loading.value.verificarRecursoActual = true
        success.value.recursoActual.code = null
        success.value.recursoActual.message = null
        errors.value = []
        if(!recurso_id_actual.value || !tipo_recurso_actual.value) return

        try {

            const response = await axios.post('traslados-beneficiarios/validar-recurso-actual', {
                cui: cui.value,
                beneficiario_id : beneficiario.value.id,
                anio_inscripcion: year.value,
                tipo_recurso_actual : tipo_recurso_actual.value,
                recurso_id_actual : recurso_id_actual.value
            })
                 
            success.value.recursoActual.code = response.data.code
            success.value.recursoActual.message = (response.data.code == '1') ? 'RECURSO VALIDO : ' + (tipo_recurso_actual.value == 'curso' ? response.data.recurso?.curso.nombre : (tipo_recurso_actual.value == 'modulo' ? response.data.recurso?.nombre : response.data.recurso?.actividad.nombre  )) : response.data.message 

        } catch (error) {
            if(error.status === 422) {
                errors.value = error.response.data.errors
            }
        } finally {
            loading.value.verificarRecursoActual = false

        }
    }

    const validarRecursoNuevo = async () => {
        loading.value.verificarRecursoNuevo = true
        success.value.recursoNuevo.code = null
        success.value.recursoNuevo.message = null
        errors.value = []
        if(!recurso_id_nuevo.value || !tipo_recurso_nuevo.value) return

        try {

            const response = await axios.post('traslados-beneficiarios/validar-recurso-nuevo', {
                cui: cui.value,
                beneficiario_id : beneficiario.value.id,
                anio_inscripcion: year.value,
                tipo_recurso_actual : tipo_recurso_actual.value,
                recurso_id_actual : recurso_id_actual.value,
                tipo_recurso_nuevo : tipo_recurso_nuevo.value,
                recurso_id_nuevo : recurso_id_nuevo.value
            })
                 
            success.value.recursoNuevo.code = response.data.code
            success.value.recursoNuevo.message = (response.data.code == '1') ? 'RECURSO VALIDO : ' + (tipo_recurso_nuevo.value == 'curso' ? response.data.recurso?.curso.nombre : (tipo_recurso_nuevo.value == 'modulo' ? response.data.recurso?.nombre : response.data.recurso?.actividad.nombre  )) : response.data.message 
            success.value.recursoNuevo.disponiblidad = (response.data.code == '1') ? response.data.recurso?.cupos_disponibles : null

        } catch (error) {
            if(error.status === 422) {
                errors.value = error.response.data.errors
            }
        } finally {
            loading.value.verificarRecursoNuevo = false

        }
    }

    const realizarTraslado = async () => {
        errors.value = []
        if(!recurso_id_nuevo.value || !tipo_recurso_nuevo.value) return

        try {

            const response = await axios.post('traslados-beneficiarios/realizar-traslado', {
                cui: cui.value,
                beneficiario_id : beneficiario.value.id,
                anio_inscripcion: year.value,
                tipo_recurso_actual : tipo_recurso_actual.value,
                recurso_id_actual : recurso_id_actual.value,
                tipo_recurso_nuevo : tipo_recurso_nuevo.value,
                recurso_id_nuevo : recurso_id_nuevo.value
            })
                 
            global.setAlert(response.data.message,'success');
            resetData()

        } catch (error) {
            if(error.status === 422) {
                errors.value = error.response.data.errors
            }
        } finally {
            loading.value.traslado = false

        }
    }

    const resetData = () => {

        cui.value = null
        beneficiario.value = null
        recurso_id_actual.value = null
        tipo_recurso_actual.value = null
        recurso_id_nuevo.value = null
        tipo_recurso_nuevo.value = null

        errors.value = []
        success.value = {
            beneficiario : {
                code : null,
                message : null,
            },
            recursoActual : {
                code : null,
                message : null,
            },
            recursoNuevo : {
                code : null,
                message : null,
                disponiblidad : null,
            }
        }
    }
    
    return {

        cui,
        recurso_id_actual,
        tipo_recurso_actual,
        recurso_id_nuevo,
        tipo_recurso_nuevo,
        year,

        success,
        errors,
        loading, 

        searchBeneficiario,
        validarRecursoActual,
        validarRecursoNuevo,
        realizarTraslado,
    }
});
