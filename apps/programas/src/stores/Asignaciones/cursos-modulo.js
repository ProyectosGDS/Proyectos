import { defineStore } from 'pinia'
import { useModulosStore } from '@/stores/Catalogos/modulos'
import { useCursosStore } from '@/stores/Catalogos/cursos'
import { useInstructoresStore } from '@/stores/Catalogos/instructores'
import { useSedesStore } from '@/stores/Catalogos/sedes'
import { useHorariosStore } from '@/stores/Catalogos/horarios'
import { useAsignacionesCursosModuloStore } from './asignaciones-cursos-modulo'
import { ref } from 'vue'
import { useGlobalStore } from '../global'

export const useCursosModuloStore = defineStore('cursos-modulo', () => {

    const global = useGlobalStore()
    const asignaciones = useAsignacionesCursosModuloStore()
    const cursosStore = useCursosStore()
    const modulosStore = useModulosStore()
    const instructoresStore = useInstructoresStore()
    const sedeStore = useSedesStore()
    const horarioStore = useHorariosStore()


    const search = ref('')
    const indice = ref(null)
    const IndexesError = ref([])
    const editDetails = ref(false)
    const label_curso = ref('')
    const programa_id = ref('')
    const modulo = ref({})
    const curso = ref({
        curso: [],
        instructor: {},
        sede: {},
        horario: {},
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
        modulo: false,
    })

    const isFormValid = () => {
        return (
            programa_id.value &&
            Object.keys(modulo.value).length > 0 &&
            Object.keys(curso.value.curso).length > 0 &&
            Object.keys(curso.value.sede).length > 0 &&
            curso.value.modalidad &&
            curso.value.temporalidad
        )
    }

    const addCurso = () => { 

        if (isFormValid()) {

            let news_course = []

            curso.value.curso.forEach(item => {
                news_course.push({
                    curso : item,
                    sede : curso.value.sede,
                    modalidad : curso.value.modalidad,
                    temporalidad : curso.value.temporalidad,
                    seccion : curso.value.seccion ?? null,
                })
            })

            let asignar_cursos = true

            news_course.forEach(item => {
                if(asignaciones.cursos.find(asignacion => asignacion.curso.curso.id == item.curso.id && asignacion.curso.sede.id == item.sede.id)) {
                    errorsDetails.value = { detalles: ['El curso: '+item.curso.nombre+' ya existen en el listado'] }
                    asignar_cursos = false
                    return
                }
            })

            if(asignar_cursos) {
                news_course.forEach(item => {
                    asignaciones.cursos.unshift({ 
                        curso : {
                            programa_id: programa_id.value,
                            curso: item.curso,
                            modalidad: item.modalidad,
                            sede: item.sede,
                            temporalidad: item.temporalidad,
                            seccion: item.seccion ?? null,
                            instructor: {},
                            horario: {},
                        },
                        modulo : modulo.value,
                    })
                })
    
                curso.value = {
                    curso: [],
                    instructor: {},
                    sede: {},
                    horario: {},
                }
                label_curso.value = ''
                errors.value = []
                return
            }

        } else {
            errorsDetails.value = { detalles: ['Hay datos que no se seleccionaron'] }
            return
        }
    }

    const removeCurso = (index) => {
        asignaciones.cursos.splice(index, 1)
    }

    const removeItem = (objeto) => {
        if (objeto == 'curso') {
            curso.value[objeto] = []
            label_curso.value = ''
        } else if (objeto == 'modulo') {
            modulo.value = {}
            asignaciones.cursos = []
        } else {
            curso.value[objeto] = {}
        }
    }

    const selectedItem = (objeto) => {

        if (detalles.value[objeto].length != 1 && objeto != 'curso') {
            errors.value = { seleccion: ['Seleccione un solo registro'] }
            return
        }

        if (objeto == 'curso') {
            curso.value[objeto] = detalles.value[objeto]

            if (detalles.value[objeto].length == 1) {
                label_curso.value = detalles.value[objeto][0].nombre
            } else if (detalles.value[objeto].length > 1) {
                label_curso.value = `SELECCIONASTE ${curso.value[objeto].length} CURSOS`
            }
        } else if (objeto == 'modulo') {
            modulo.value = detalles.value[objeto][0]
            asignaciones.fetch(modulo.value.id)
        } else {
            curso.value[objeto] = detalles.value[objeto][0]
        }
        resetData()
    }

    const openModal = (objeto) => {
        if (objeto == 'modulo' && !programa_id.value) {
            errorsDetails.value = { programa_id: ['Seleccione un programa'] }
            return
        }

        if (objeto == 'curso' && !Object.keys(modulo.value).length) {
            errorsDetails.value = { modulo_id: ['Seleccione un módulo'] }
            return
        }

        if (objeto == 'curso') {
            detalles.value[objeto] = Object.keys(curso.value[objeto]).length ? curso.value[objeto] : []
        } else if (objeto == 'modulo') {
            detalles.value[objeto] = Object.keys(modulo.value).length ? [modulo.value] : []
        } else {
            detalles.value[objeto] = Object.keys(curso.value[objeto]).length ? [curso.value[objeto]] : []
        }
        modal.value[objeto] = true
    }

    const openModalDetails = (objeto, index) => {
        indice.value = index
        detalles.value[objeto] = Object.keys(asignaciones.cursos[index]['curso'][objeto]).length ? [asignaciones.cursos[index]['curso'][objeto]] : []
        modal.value[objeto] = true
    }

    const selectDetails = (objeto) => {

        if (detalles.value[objeto].length != 1) {
            errors.value = { seleccion: ['Seleccione un solo registro'] }
            return
        }

        asignaciones.cursos[indice.value]['curso'][objeto] = detalles.value[objeto][0]
        resetData()
    }

    const validateDataCourse = () => {
        
        IndexesError.value = []

        asignaciones.cursos.forEach((item, index) => {
            if(!item.detalle_curso_id && !item.modulo_id) {
                if((Object.keys(item.curso.instructor).length == 0) || (Object.keys(item.curso.horario).length == 0)) {
                    IndexesError.value.push(index)
                    return
                }
            }
        })

        if(IndexesError.value.length) {
            global.setAlert('Hay cursos recien asignados sin horario o instructor','danger')
            return
        }

        asignaciones.store()
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
        modulosStore.resetData()
    }

    return {
        search,
        label_curso,
        programa_id,
        editDetails,
        modulo,
        curso,
        detalles,
        errors,
        errorsDetails,
        modal,
        indice,
        IndexesError,

        isFormValid,
        openModal,
        addCurso,
        openModalDetails,
        removeItem,
        removeCurso,
        selectedItem,
        selectDetails,
        resetData,
        validateDataCourse,
    }
})
