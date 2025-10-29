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
    const escuela = ref(null)
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

    const selectedModulo = async () => {
        inscripcion.beneficiarios = []
        await inscripcion.fetch()
        
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
            !inscripcion.programa_id ||
            !typeof(inscripcion.modulo) == 'string' ||
            !beneficiariosStore.beneficiario.hasOwnProperty('primer_nombre')||
            !beneficiariosStore.beneficiario.hasOwnProperty('primer_apellido')
        ) {
            errorsDetails.value = { detalles: ['Hay datos que no se seleccionaron'] }
            return
        }
            if(beneficiariosStore.beneficiario.edad < 18) {

                if(
                    !beneficiariosStore.beneficiario?.responsable ||
                    !beneficiariosStore.beneficiario?.responsable?.cui ||
                    !beneficiariosStore.beneficiario?.responsable?.nombres ||
                    !beneficiariosStore.beneficiario?.responsable?.apellidos ||
                    !beneficiariosStore.beneficiario?.responsable?.fecha_nacimiento
                ) {
                    errorsDetails.value = { detalles: ['No hay datos del responsable'] }
                    return
                }

                const new_beneficiario = inscripcion.beneficiarios.filter(item => item.beneficiario.cui === beneficiariosStore.beneficiario.cui )            
                
                const modulo = JSON.parse(inscripcion.modulo)
    
                if(!Object.keys(new_beneficiario).length > 0) {
                    inscripcion.beneficiarios.unshift({
                        modulo_id : modulo.id,
                        beneficiario_id : beneficiariosStore.beneficiario.id,
                        beneficiario : beneficiariosStore.beneficiario,
                        paga : modulo.paga,
                        tarifas : modulo.tarifas,
                        edad : beneficiariosStore.beneficiario.edad,
                        dependencia : auth.dependencia_id,
                        cui : beneficiariosStore.beneficiario.cui,
                        responsable : beneficiariosStore.beneficiario.responsable,
                        nombre_modulo : modulo.nombre,
                        objeto_contrato : escuela.value ? JSON.parse(escuela.value).objeto_contrato : null,
                        sede_oc : modulo.sede.objeto_contrato ?? null,
                        op_principal : escuela.value ? JSON.parse(escuela.value).op_principal : null,
                        op_parcial : escuela.value ? JSON.parse(escuela.value).op_parcial : null,
                        sede_op_principal : modulo.sede.op_principal ?? null,
                        sede_op_parcial : modulo.sede.op_parcial ?? null,
                    })
        
                    beneficiariosStore.resetData()
                    errorsDetails.value = []
                    beneficiariosStore.nuevo_registro = false
                    return
                } else {
                    errorsDetails.value = { detalles: ['Ya existe el beneficiario en el listado'] }
                    beneficiariosStore.resetData()
                    return
                }
                
            } else {

                const new_beneficiario = inscripcion.beneficiarios.filter(item => item.beneficiario.cui === beneficiariosStore.beneficiario.cui )            
                
                const modulo = JSON.parse(inscripcion.modulo)
    
                if(!Object.keys(new_beneficiario).length > 0) {
                    inscripcion.beneficiarios.unshift({
                        modulo_id : modulo.id,
                        beneficiario_id : beneficiariosStore.beneficiario.id,
                        beneficiario : beneficiariosStore.beneficiario,
                        paga : modulo.paga,
                        tarifas : modulo.tarifas,
                        edad : beneficiariosStore.beneficiario.edad,
                        dependencia : auth.dependencia_id,
                        cui : beneficiariosStore.beneficiario.cui,
                        nombre_modulo : modulo.nombre,
                        objeto_contrato : escuela.value ? JSON.parse(escuela.value).objeto_contrato : null,
                        sede_oc : modulo.sede.objeto_contrato ?? null,
                        op_principal : escuela.value ? JSON.parse(escuela.value).op_principal : null,
                        op_parcial : escuela.value ? JSON.parse(escuela.value).op_parcial : null,
                        sede_op_principal : modulo.sede.op_principal ?? null,
                        sede_op_parcial : modulo.sede.op_parcial ?? null,
                    })
        
                    beneficiariosStore.resetData()
                    errorsDetails.value = []
                    beneficiariosStore.nuevo_registro = false
                    return
                } else {
                    errorsDetails.value = { detalles: ['Ya existe el beneficiario en el listado'] }
                    beneficiariosStore.resetData()
                    return
                }

            }
    }

    const saveAddBeneficiario = async () => {

        if (
            !inscripcion.programa_id ||
            !typeof(inscripcion.modulo) == 'string' ||
            !beneficiariosStore.beneficiario.hasOwnProperty('primer_nombre') ||
            !beneficiariosStore.beneficiario.hasOwnProperty('primer_apellido')
        ) {
            errorsDetails.value = { detalles: ['Hay datos que no se seleccionaron'] }
            return
        }
            
            if(beneficiariosStore.beneficiario.edad < 18) {

                if(
                    !beneficiariosStore.beneficiario?.responsable ||
                    !beneficiariosStore.beneficiario?.responsable?.cui ||
                    !beneficiariosStore.beneficiario?.responsable?.nombres ||
                    !beneficiariosStore.beneficiario?.responsable?.apellidos ||
                    !beneficiariosStore.beneficiario?.responsable?.fecha_nacimiento
                ) {
                    errorsDetails.value = { detalles: ['No hay datos del responsable'] }
                    return
                }

                const new_beneficiario = inscripcion.beneficiarios.filter(item => item.beneficiario.cui === beneficiariosStore.beneficiario.cui )            
                
                if(!Object.keys(new_beneficiario).length > 0) { 
                    
                    await beneficiariosStore.create()
    
                    if(beneficiariosStore.errors == 0) {
    
                        beneficiariosStore.nuevo_registro = false
                        const modulo = JSON.parse(inscripcion.modulo)
                        inscripcion.beneficiarios.unshift({
                            modulo_id : modulo.id,
                            beneficiario_id : beneficiariosStore.beneficiario.id,
                            beneficiario : beneficiariosStore.beneficiario,
                            paga : modulo.paga,
                            tarifas : modulo.tarifas,
                            edad : beneficiariosStore.beneficiario.edad,
                            dependencia : auth.dependencia_id,
                            cui : beneficiariosStore.beneficiario.cui,
                            responsable : beneficiariosStore.beneficiario.responsable,
                            nombre_modulo : modulo.nombre,
                            objeto_contrato : escuela.value ? JSON.parse(escuela.value).objeto_contrato : null,
                            sede_oc : modulo.sede.objeto_contrato ?? null,
                            op_principal : escuela.value ? JSON.parse(escuela.value).op_principal : null,
                            op_parcial : escuela.value ? JSON.parse(escuela.value).op_parcial : null,
                            sede_op_principal : modulo.sede.op_principal ?? null,
                            sede_op_parcial : modulo.sede.op_parcial ?? null,
                        })
    
                        beneficiariosStore.resetData()
                        errorsDetails.value = []
                        return
                    }
    
                    return
                } else {
                    errorsDetails.value = { detalles: ['Ya existe el beneficiario en el listado'] }
                    beneficiariosStore.resetData()
                    return
                }
                
            } else {
                const new_beneficiario = inscripcion.beneficiarios.filter(item => item.beneficiario.cui === beneficiariosStore.beneficiario.cui )            
                
                if(!Object.keys(new_beneficiario).length > 0) { 
                    
                    await beneficiariosStore.create()
    
                    if(beneficiariosStore.errors == 0) {
    
                        beneficiariosStore.nuevo_registro = false
                        const modulo = JSON.parse(inscripcion.modulo)
                        inscripcion.beneficiarios.unshift({
                            modulo_id : modulo.id,
                            beneficiario_id : beneficiariosStore.beneficiario.id,
                            beneficiario : beneficiariosStore.beneficiario,
                            paga : modulo.paga,
                            tarifas : modulo.tarifas,
                            edad : beneficiariosStore.beneficiario.edad,
                            dependencia : auth.dependencia_id,
                            cui : beneficiariosStore.beneficiario.cui,
                            nombre_modulo : modulo.nombre,
                            objeto_contrato : escuela.value ? JSON.parse(escuela.value).objeto_contrato : null,
                            sede_oc : modulo.sede.objeto_contrato ?? null,
                            op_principal : escuela.value ? JSON.parse(escuela.value).op_principal : null,
                            op_parcial : escuela.value ? JSON.parse(escuela.value).op_parcial : null,
                            sede_op_principal : modulo.sede.op_principal ?? null,
                            sede_op_parcial : modulo.sede.op_parcial ?? null,
                        })
    
                        beneficiariosStore.resetData()
                        errorsDetails.value = []
                        return
                    }
    
                    return
                } else {
                    errorsDetails.value = { detalles: ['Ya existe el beneficiario en el listado'] }
                    beneficiariosStore.resetData()
                    return
                }
            }
    }

    const resetData = () => {
        modal.value = {
            cursos: false,
        }
        errors.value = []
        detalles.value = []
        
    }

    return {
        escuela,
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
