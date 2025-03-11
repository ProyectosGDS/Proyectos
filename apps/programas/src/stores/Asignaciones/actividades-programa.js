import { defineStore } from 'pinia'
import { useActividadesStore } from '@/stores/Catalogos/actividades'
import { useInstructoresStore } from '@/stores/Catalogos/instructores'
import { useSedesStore } from '@/stores/Catalogos/sedes'
import { useHorariosStore } from '@/stores/Catalogos/horarios'
import { useAsignacionesActividadesProgramaStore } from './asignaciones-actividades-programa'
import { ref } from 'vue'

export const useActividadesProgramaStore = defineStore('actividades-programa', () => {

    const asignaciones = useAsignacionesActividadesProgramaStore()
    const actividadesStore = useActividadesStore()
    const instructoresStore = useInstructoresStore()
    const sedeStore = useSedesStore()
    const horarioStore = useHorariosStore()

    const editDetails = ref(false)

    const search = ref('')


    const actividad = ref({
        actividad: {},
        tipo_actividad : {}
    })

    const detalles = ref({
        actividad: []
    })

    const errors = ref({})

    const modal = ref({
        actividad: false,
    })


    const addActividad = () => {

        let tipo_actividad = {}
        tipo_actividad = typeof(actividad.value.tipo_actividad) === 'string' && actividad.value.tipo_actividad != '' ? JSON.parse(actividad.value.tipo_actividad) : {}        

        
        if(!asignaciones.programa_id) {
            errors.value.programa_id = ['Seleccione un programa']
        } else {
            errors.value = {}
        }

        if(!Object.keys(actividad.value.actividad).length) {
            errors.value.actividad_id = ['Seleccione una actividad']
        } else {
            errors.value = {}
        }

        if(!Object.keys(tipo_actividad).length) {
            errors.value.tipo_actividad_id = ['Seleccione una tipo de actividad']
        } else {
            errors.value = {}
        }

        if(!actividad.value.fecha_inicial) {
            errors.value.fecha_inicial = ['Seleccione una fecha inicial']
        } else {
            errors.value = {}
        }

        if(!actividad.value.fecha_final) {
            errors.value.fecha_final = ['Seleccione una fecha final']
        } else {
            errors.value = {}
        }
        
        if (Object.keys(errors.value).length == 0) {

            let zona = actividad.value.zona ? JSON.parse(actividad.value.zona) : ''
            let distrito = actividad.value.distrito ? JSON.parse(actividad.value.distrito) : ''

            asignaciones.actividades.unshift({
                programa_id: asignaciones.programa_id,
                actividad_id: actividad.value.actividad.id,
                actividad: actividad.value.actividad.nombre,
                tipo : tipo_actividad.nombre,
                tipo_actividad_id : tipo_actividad.id,

                responsable: actividad.value.responsable ?? '',
                zona: zona.nombre ?? '',
                zona_id : zona.id ?? '',
                distrito: distrito.nombre ?? '',
                distrito_id : distrito.id ?? '',
                direccion: actividad.value.direccion ?? '',
                fechas : actividad.value.fecha_final ? actividad.value.fecha_inicial + ' - ' + actividad.value.fecha_final : '',
                fecha_inicial: actividad.value.fecha_inicial ?? '',
                fecha_final: actividad.value.fecha_final ?? '',
                horario: actividad.value.horario_inicio ? actividad.value.hora_inicio + ' A ' + actividad.value.hora_final : '',
                hora_inicio : actividad.value.hora_inicio ?? '',
                hora_final : actividad.value.hora_final ?? '',
            })

            actividad.value = {
                actividad: {},
                tipo_actividad : {}
            }

            errors.value = {}
            return

        }
    }

    const removeCurso = (index) => {
        asignaciones.actividades.splice(index, 1)
    }

    const removeItem = (objeto) => {
        actividad.value[objeto] = {}
    }


    const selectedItem = (objeto) => {

        if (detalles.value[objeto].length != 1) {
            errors.value = { seleccion: ['Seleccione un solo registro'] }
            return
        }

        actividad.value[objeto] = detalles.value[objeto][0]
        resetData()
    }

    const openModal = (objeto) => {
        detalles.value[objeto] = Object.keys(actividad.value[objeto]).length ? [actividad.value[objeto]] : []
        modal.value[objeto] = true
    }

    const resetData = () => {
        errors.value = []
        detalles.value = {
            actividad: [],
            instructor: [],
            sede: [],
            horario: [],
        }
        modal.value = {
            actividad: false,
            instructor: false,
            sede: false,
            horario: false,
        }
        actividadesStore.resetData()
        instructoresStore.resetData()
        sedeStore.resetData()
        horarioStore.resetData()
    }

    return {
        search,
        editDetails,
        actividad,
        detalles,
        errors,
        modal,

        openModal,
        addActividad,
        removeItem,
        removeCurso,
        selectedItem,
        resetData,
    }
})
