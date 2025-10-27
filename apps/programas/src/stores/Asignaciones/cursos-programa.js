import { defineStore } from 'pinia'
import { useCursosStore } from '@/stores/Catalogos/cursos'
import { useInstructoresStore } from '@/stores/Catalogos/instructores'
import { useSedesStore } from '@/stores/Catalogos/sedes'
import { useHorariosStore } from '@/stores/Catalogos/horarios'
import { useAsignacionesCursosProgramaStore } from './asignaciones-cursos-programa'
import { ref } from 'vue'

export const useCursosProgramaStore = defineStore('cursos-programa', () => {

    const asignaciones = useAsignacionesCursosProgramaStore()
    const cursosStore = useCursosStore()
    const instructoresStore = useInstructoresStore()
    const sedeStore = useSedesStore()
    const horarioStore = useHorariosStore()

    const editDetails = ref(false)

    const search = ref('')

    const curso = ref({
        curso: {},
        instructores: [],
        sede: {},
        horarios: [],
        publico: 'S',
        seccion : null,
        paga : 'N',
        tarifas : {}
    })

    const labels = ref({
        horarios : '',
        instructores : ''
    })

    const detalles = ref({
        curso: [],
        instructores: [],
        sede: [],
        horarios: [],
    })

    const errors = ref([])
    const errorsDetails = ref([])
    const modal = ref({
        curso: false,
        instructor: false,
        sede: false,
        horarios: false,
    })



    const addCurso = () => {

        if (
            asignaciones.programa_id &&
            Object.keys(curso.value.curso).length &&
            Object.keys(curso.value.sede).length &&
            curso.value.capacidad &&
            curso.value.modalidad &&
            curso.value.fecha_inicial &&
            curso.value.fecha_final
        ) {

            const temporalidad = JSON.parse(curso.value.temporalidad)

            const new_curso = asignaciones.cursos.filter( item => {
                return (
                    item.curso_id == curso.value.curso.id &&
                    item.seccion == curso.value.seccion &&
                    item.sede_id == curso.value.sede.id
                )
            })

            if(!Object.keys(new_curso).length > 0) {
                asignaciones.cursos.unshift({
                    programa_id: asignaciones.programa_id,
                    curso_id: curso.value.curso.id,
                    curso: curso.value.curso.nombre,
                    seccion: curso.value.seccion,
                    instructores: curso.value.instructores.map(item => item.id),
                    sede_id: curso.value.sede.id,
                    sede: curso.value.sede.nombre_completo,
                    horarios: curso.value.horarios.map(item => item.id),
                    temporalidad_id: temporalidad.id,
                    temporalidad: temporalidad.nombre,
                    modalidad: curso.value.modalidad,
                    capacidad: curso.value.capacidad,
                    fecha_inicial: curso.value.fecha_inicial,
                    fecha_final: curso.value.fecha_final,
                    publico: 'S',
                    paga : curso.value.paga,
                    inscripcion : curso.value.inscripcion,
                    tarifa_menor : curso.value.tarifa_menor,
                    tarifa_mayor : curso.value.tarifa_mayor,
                    temporalidad_tarifa : curso.value.temporalidad_tarifa,
                    no_cuotas : curso.value.no_cuotas,
                    mes_inicial : curso.value.mes_inicial,
                    mes_final : curso.value.mes_final,
                })
                curso.value = {
                    curso: {},
                    instructores: [],
                    sede: {},
                    horarios: [],
                    publico: 'S',
                    paga : 'N',
                    tarifas : {},
                }
                errors.value = []
                labels.value = {
                    horarios : '',
                    instructores : ''
                }
                return
            }
            
            errorsDetails.value = { detalles: ['Ya existe el curso en el listado'] }
            return 

        }

        errorsDetails.value = { detalles: ['Hay datos que no se seleccionaron'] }
    }

    const removeCurso = (index) => {
        asignaciones.cursos.splice(index, 1)
    }

    const removeItem = (objeto) => {
        if(['horarios','instructores'].includes(objeto)) {
            labels.value[objeto] = ''
            curso.value[objeto] = []
            return
        }

        curso.value[objeto] = {}
    }

    const editCurso = (item) => {
        curso.value = item
        editDetails.value = true
    }

    const selectedItem = (objeto) => {

        if (detalles.value[objeto].length != 1 && !['horarios','instructores'].includes(objeto)) {
            errors.value = { seleccion: ['Seleccione un solo registro'] }
            return
        }

        if (objeto == 'horarios') {
            curso.value[objeto] = detalles.value[objeto]

            if (detalles.value[objeto].length == 1) {
                labels.value.horarios = detalles.value[objeto][0].nombre_completo
            } else if (detalles.value[objeto].length > 1) {
                labels.value.horarios = `SELECCIONASTE ${curso.value[objeto].length} HORARIOS`
            }
        } else if(objeto == 'instructores'){
            curso.value[objeto] = detalles.value[objeto]

            if (detalles.value[objeto].length == 1) {
                labels.value.instructores = detalles.value[objeto][0].nombre
            } else if (detalles.value[objeto].length > 1) {
                labels.value.instructores = `SELECCIONASTE ${curso.value[objeto].length} INSTRUCTORES`
            }
        } else {
            curso.value[objeto] = detalles.value[objeto][0]
        }

        resetData()
    }

    const openModal = (objeto) => {
        if(['horarios','instructores'].includes(objeto)) {
            detalles.value[objeto] = Object.keys(curso.value[objeto]).length ? curso.value[objeto] : []
        } else {
            detalles.value[objeto] = Object.keys(curso.value[objeto]).length ? [curso.value[objeto]] : []
        }

        modal.value[objeto] = true
    }

    const resetData = () => {
        errors.value = []
        errorsDetails.value = []
        detalles.value = {
            curso: [],
            instructor: [],
            sede: [],
            horario: [],
        }
        modal.value = {
            curso: false,
            instructor: false,
            sede: false,
            horario: false,
        }
        cursosStore.resetData()
        instructoresStore.resetData()
        sedeStore.resetData()
        horarioStore.resetData()
    }

    return {
        search,
        editDetails,
        curso,
        labels,
        detalles,
        errors,
        errorsDetails,
        modal,

        openModal,
        addCurso,
        editCurso,
        removeItem,
        removeCurso,
        selectedItem,
        resetData,
    }
})
