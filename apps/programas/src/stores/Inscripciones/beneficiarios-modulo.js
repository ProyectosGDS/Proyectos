import { defineStore } from 'pinia'
import { ref } from 'vue'
import { useModulosStore } from '@/stores/Catalogos/modulos'
import { useBeneficiariosStore } from './beneficiarios'
import { useInscripcionesModuloStore } from './inscripciones-modulo'
import { useAuthStore } from '../auth'


export const useBeneficiariosModuloStore = defineStore('beneficiarios-modulo', () => {

    const beneficiariosStore = useBeneficiariosStore()
    const modulos = useModulosStore()
    const inscripcion = useInscripcionesModuloStore()
    const auth = useAuthStore()
    
    const curso = ref({})
    const detalles = ref([])
    const search = ref('')

    const errors = ref([])
    const errorsDetails = ref([])

    const openCursos = () => {
        detalles.value = Object.keys(curso.value).length ? [curso.value] : []
        modal.value.cursos = true
    }

    const selectedPrograma = () => {
        modulos.modulos = {}
        inscripcion.beneficiarios = []
        modulos.fetch(inscripcion.programa_id)
    }

    const selectedModulo = () => {
        inscripcion.beneficiarios = []
        inscripcion.fetch(inscripcion.modulo_id)
    }

    const removeInscripcion = (item,index) => {
        if(item.hasOwnProperty('id')) {
            if(auth.checkPermission('eliminar inscripcion modulo')) {
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
            inscripcion.programa_id &&
            inscripcion.modulo_id &&
            beneficiariosStore.beneficiario.hasOwnProperty('primer_nombre')
        ) {

            const new_beneficiario = inscripcion.beneficiarios.filter(item => item.beneficiario.cui === beneficiariosStore.beneficiario.cui )            

            if(!Object.keys(new_beneficiario).length > 0) {
                inscripcion.beneficiarios.unshift({
                    modulo_id : inscripcion.modulo_id,
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
            inscripcion.programa_id &&
            inscripcion.modulo_id &&
            beneficiariosStore.beneficiario.hasOwnProperty('primer_nombre')
        ) {
            
            const new_beneficiario = inscripcion.beneficiarios.filter(item => item.beneficiario.cui === beneficiariosStore.beneficiario.cui )            
            
            if(!Object.keys(new_beneficiario).length > 0) { 
                
                await beneficiariosStore.create()

                if(beneficiariosStore.errors == 0) {

                    beneficiariosStore.nuevo_registro = false

                    inscripcion.beneficiarios.unshift({
                        modulo_id : inscripcion.modulo_id,
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
        search,
        detalles,
        errors,
        errorsDetails,

        saveAddBeneficiario,
        openCursos,
        selectedPrograma,
        selectedModulo,
        removeInscripcion,
        changeEstadoInscripcion,
        addBeneficiario,
        resetData,
    }
})
