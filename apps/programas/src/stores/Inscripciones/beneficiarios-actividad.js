import { defineStore } from 'pinia'
import { ref } from 'vue'
import { useProgramasStore } from '@/stores/Catalogos/programas'
import { useBeneficiariosStore } from './beneficiarios'
import { useInscripcionesActividadStore } from './inscripciones-actividad'
import { useAuthStore } from '../auth'


export const useBeneficiariosActividadStore = defineStore('beneficiarios-actividad', () => {

    const beneficiariosStore = useBeneficiariosStore()
    const programas = useProgramasStore()
    const inscripcion = useInscripcionesActividadStore()
    const auth = useAuthStore()
    
    const beneficiario_actividad = ref({})
    const actividad = ref({})
    const detalles = ref([])
    const search = ref('')

    const modal = ref({
        actividades: false,
    })


    const errors = ref([])
    const errorsDetails = ref([])

    const openActividades = () => {
        detalles.value = Object.keys(actividad.value).length ? [actividad.value] : []
        modal.value.actividades = true
    }

    const selectedCurso = () => {

        if (detalles.value.length != 1) {
            errors.value = { seleccion: ['Seleccione una sola actividad'] }
            return
        }

        actividad.value = detalles.value[0]
        inscripcion.fetch(actividad.value.id)
        resetData()
        
    }

    const removeCurso = () => {
        actividad.value = {}
        inscripcion.beneficiarios = []
    }

    const removeInscripcion = (item,index) => {
        if(item.hasOwnProperty('id')) {
            if(auth.checkPermission('eliminar inscripcion actividad')) {
                inscripcion.inscripcion = item
                inscripcion.modal.delete = true
            }
            return 
        } else {
            inscripcion.beneficiarios.splice(index, 1)
        }
    }

    const changeEstadoInscripcion = (item) => {
        inscripcion.inscripcion = item
        inscripcion.inscripcion.estado = item.estado == 'A' ? 'I' : 'A'
        inscripcion.modal.disabled = true
    }

    const addBeneficiario = () => {

        if (
            programas.programa &&
            Object.keys(actividad.value).length &&
            beneficiariosStore.beneficiario.hasOwnProperty('primer_nombre')
        ) {

            const new_beneficiario = inscripcion.beneficiarios.filter(item => item.beneficiario.cui === beneficiariosStore.beneficiario.cui )            

            if(!Object.keys(new_beneficiario).length > 0) {
                inscripcion.beneficiarios.unshift({
                    programa_id : programas.programa,
                    detalle_actividad_id : actividad.value.id,
                    beneficiario_id : beneficiariosStore.beneficiario.id,
                    beneficiario : beneficiariosStore.beneficiario,
                })
    
                beneficiariosStore.resetData()
                errorsDetails.value = []
                beneficiariosStore.nuevo_registro = false
                return
            }
            
            errorsDetails.value = { detalles: ['Ya existe el beneficiario en el listado'] }
            beneficiariosStore.resetData()
            return

        }

        errorsDetails.value = { detalles: ['Hay datos que no se seleccionaron'] }
    }

    const saveAddBeneficiario = async () => {
        if (
            programas.programa &&
            Object.keys(actividad.value).length &&
            beneficiariosStore.beneficiario.hasOwnProperty('primer_nombre')
        ) {
            
            const new_beneficiario = inscripcion.beneficiarios.filter(item => item.beneficiario.cui === beneficiariosStore.beneficiario.cui )            
            
            if(!Object.keys(new_beneficiario).length > 0) { 
                
                await beneficiariosStore.create()

                if(beneficiariosStore.errors == 0) {

                    beneficiariosStore.nuevo_registro = false

                    inscripcion.beneficiarios.unshift({
                        programa_id : programas.programa,
                        detalle_actividad_id : actividad.value.id,
                        beneficiario_id : beneficiariosStore.beneficiario.id,
                        beneficiario : beneficiariosStore.beneficiario,
                    })

                    beneficiariosStore.resetData()
                    errorsDetails.value = []
                    return
                }

                return
            }
            
            errorsDetails.value = { detalles: ['Ya existe el beneficiario en el listado'] }
            beneficiariosStore.resetData()
            return
        }

        errorsDetails.value = { detalles: ['Hay datos que no se seleccionaron'] }
    }

    const resetData = () => {
        modal.value = {
            actividades: false,
        }
        errors.value = []
        detalles.value = []
    }

    return {
        beneficiario_actividad,
        search,
        actividad,
        detalles,
        modal,
        errors,
        errorsDetails,

        saveAddBeneficiario,
        openActividades,
        selectedCurso,
        removeCurso,
        removeInscripcion,
        changeEstadoInscripcion,
        addBeneficiario,
        resetData,
    }
})
