import { defineStore } from 'pinia'
import { ref } from 'vue'
import { useProgramasStore } from '@/stores/Catalogos/programas'
import { useBeneficiariosStore } from './beneficiarios'
import { useInscripcionesCursoStore } from './inscripciones-curso'
import { useAuthStore } from '../auth'


export const useBeneficiariosCursoStore = defineStore('beneficiarios-curso', () => {

    const beneficiariosStore = useBeneficiariosStore()
    const programas = useProgramasStore()
    const inscripcion = useInscripcionesCursoStore()
    const auth = useAuthStore()
    
    const beneficiario_curso = ref({})
    const curso = ref({})
    const detalles = ref([])
    const search = ref('')

    const modal = ref({
        cursos: false,
    })


    const errors = ref([])
    const errorsDetails = ref([])

    const openCursos = () => {
        detalles.value = Object.keys(curso.value).length ? [curso.value] : []
        modal.value.cursos = true
    }

    const selectedCurso = () => {

        if (detalles.value.length != 1) {
            errors.value = { seleccion: ['Seleccione un solo curso'] }
            return
        }

        curso.value = detalles.value[0]
        inscripcion.fetch(curso.value.id)
        resetData()
        
    }

    const removeCurso = () => {
        curso.value = {}
        inscripcion.beneficiarios = []
    }

    const removeInscripcion = (item,index) => {
        if(item.hasOwnProperty('id')) {
            if(auth.checkPermission('eliminar inscripcion curso')) {
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
            Object.keys(curso.value).length &&
            beneficiariosStore.beneficiario.hasOwnProperty('primer_nombre')
        ) {

            const new_beneficiario = inscripcion.beneficiarios.filter(item => item.beneficiario.cui === beneficiariosStore.beneficiario.cui )            

            if(!Object.keys(new_beneficiario).length > 0) {
                inscripcion.beneficiarios.unshift({
                    programa_id : programas.programa,
                    detalle_curso_id : curso.value.id,
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
            Object.keys(curso.value).length &&
            beneficiariosStore.beneficiario.hasOwnProperty('primer_nombre')
        ) {
            
            const new_beneficiario = inscripcion.beneficiarios.filter(item => item.beneficiario.cui === beneficiariosStore.beneficiario.cui )            
            
            if(!Object.keys(new_beneficiario).length > 0) { 
                
                await beneficiariosStore.create()

                if(beneficiariosStore.errors == 0) {

                    beneficiariosStore.nuevo_registro = false

                    inscripcion.beneficiarios.unshift({
                        programa_id : programas.programa,
                        detalle_curso_id : curso.value.id,
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
            cursos: false,
        }
        errors.value = []
        detalles.value = []
    }

    return {
        beneficiario_curso,
        search,
        curso,
        detalles,
        modal,
        errors,
        errorsDetails,

        saveAddBeneficiario,
        openCursos,
        selectedCurso,
        removeCurso,
        removeInscripcion,
        changeEstadoInscripcion,
        addBeneficiario,
        resetData,
    }
})
