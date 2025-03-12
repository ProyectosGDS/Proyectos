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
        instructor: {},
        sede: {},
        horario: {},
        publico: 'S',
        seccion : null,
    })

    const detalles = ref({
        curso: [],
        instructor: [],
        sede: [],
        horario: [],
    })

    const errors = ref([])
    const errorsDetails = ref([])
    const modal = ref({
        curso: false,
        instructor: false,
        sede: false,
        horario: false,
    })



    const addCurso = () => {

        if (
            asignaciones.programa_id &&
            Object.keys(curso.value.curso).length &&
            Object.keys(curso.value.instructor).length &&
            Object.keys(curso.value.sede).length &&
            Object.keys(curso.value.horario).length &&
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
                    item.instructor_id == curso.value.instructor.id &&
                    item.sede_id == curso.value.sede.id &&
                    item.horario_id == curso.value.horario.id
                )
            })

            if(!Object.keys(new_curso).length > 0) {
                asignaciones.cursos.unshift({
                    programa_id: asignaciones.programa_id,
                    curso_id: curso.value.curso.id,
                    curso: curso.value.curso.nombre,
                    seccion: curso.value.seccion,
                    instructor_id: curso.value.instructor.id,
                    instructor: curso.value.instructor.nombre,
                    sede_id: curso.value.sede.id,
                    sede: curso.value.sede.nombre_completo,
                    horario_id: curso.value.horario.id,
                    horario: curso.value.horario.nombre_completo,
                    temporalidad_id: temporalidad.id,
                    temporalidad: temporalidad.nombre,
                    modalidad: curso.value.modalidad,
                    capacidad: curso.value.capacidad,
                    fecha_inicial: curso.value.fecha_inicial,
                    fecha_final: curso.value.fecha_final,
                    publico: 'S',
                })
                curso.value = {
                    curso: {},
                    instructor: {},
                    sede: {},
                    horario: {},
                    publico: 'S'
                }
                errors.value = []
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
        curso.value[objeto] = {}
    }

    const editCurso = (item) => {
        curso.value = item
        editDetails.value = true
    }

    const selectedItem = (objeto) => {

        if (detalles.value[objeto].length != 1) {
            errors.value = { seleccion: ['Seleccione un solo registro'] }
            return
        }

        curso.value[objeto] = detalles.value[objeto][0]
        resetData()
    }

    const openModal = (objeto) => {
        detalles.value[objeto] = Object.keys(curso.value[objeto]).length ? [curso.value[objeto]] : []
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
