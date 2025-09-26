import { defineStore } from 'pinia'
import { useGlobalStore } from '../global'
import { ref } from 'vue'
import axios from 'axios'
import { useHorariosStore } from '../Catalogos/horarios'

export const useAsignacionesCursosProgramaStore = defineStore('asignaciones-cursos-programa', () => {
    
    const global = useGlobalStore()
    const storeHorarios = useHorariosStore()

    const headers = [
        { title : 'id', key : 'id', type : 'numeric' },
        { title : 'escuela', key : 'programa.escuela' },
        { title : 'programa', key : 'programa.nombre' },
        { title : 'curso', key : 'curso.nombre' },
        { title : 'seccion', key : 'seccion', width : '10px', align : 'center' },
        { title : 'instructor', key : 'instructor.nombre', class: 'uppercase text-xs' },
        { title : 'sede', key : 'sede.nombre_completo' },
        { title : 'horario', key : 'horarios.nombre_completo' },
        { title : 'temporalidad', key : 'temporalidad.nombre', class: 'uppercase text-xs', width : '10px', align : 'center' },
        { title : 'modalidad', key : 'modalidad', width : '10px', align : 'center' },
        { title : 'capacidad', key : 'capacidad', width : '10px', align : 'center' },
        { title : 'inicia', key : 'fecha_inicial', type : 'date' },
        { title : 'termina', key : 'fecha_final', type : 'date' },
        { title : 'impulsatec', key : 'curso.impulsatec', width : '10px', align : 'center' },
        { title : 'público', key : 'publico', width : '10px', align : 'center' },
        { title : 'paga', key : 'paga', width : '10px', align : 'center' },
        { title : 'estado', key : 'estado', width : '10px', align : 'center' },
        { title : '', key : 'actions', width : '10px', align : 'center' },
    ]


    const programa_id = ref(null)
    const cursos = ref([])
    const curso = ref({})
    const copy_curso = ref({})
    const selected_requirements = ref([])
    const loading = ref({
        fetch : false,
        show : false,
        store : false,
        update : false,
        excel : false,
        destroy : false
    })

    const horarios = ref([])

    const errors = ref([])
    const modal = ref({
        edit : false,
        delete : false,
        requisitos : false,
        horarios:  false,
    })

    const fetch = async (programa_id, all = true) => {
        loading.value.fetch = true
        try {
            if(programa_id != '') {
                const response = await axios.get('programas/get-cursos/' + programa_id + '/' + all)
                cursos.value = response.data
            }
        } catch (error) {
            global.manejarError(error)
            if(error.status === 422) {
                errors.value = error.response.data.errors
            }
        } finally {
            loading.value.fetch = false
        }
    }

    const show = async (id) => {
        loading.value.show = true
        try {
            const response = await axios.get('detalles-curso/' + id)
            curso.value = response.data
            curso.value.tarifas = !response.data.tarifas ? {} : response.data.tarifas
            copy_curso.value = JSON.parse(JSON.stringify(curso.value))
            modal.value.edit = true
        } catch (error) {
            global.manejarError(error)
            if(error.status === 422) {
                errors.value = error.response.data.errors
            }
        } finally {
            loading.value.show = false
        }
    }

    const editHorarios = async (id) => {
        storeHorarios.fetch()
        loading.value.show = true
        try {
            const response = await axios.get('detalles-curso/' + id)
            curso.value = response.data
            horarios.value = response.data.horarios ?? []
            modal.value.horarios = true
        } catch (error) {
            global.manejarError(error)
            if(error.status === 422) {
                errors.value = error.response.data.errors
            }
        } finally {
            loading.value.show = false
        }
    }

    const store = async () => {
        loading.value.store = true
        try {
            const response = await axios.post('programas/store-cursos', {
                cursos : cursos.value
            })
            global.setAlert(response.data,'success')
            resetData()
            fetch(programa_id.value)
        } catch (error) {
            global.manejarError(error)
            if(error.status === 422) {
                errors.value = error.response.data.errors
            }
        } finally {
            loading.value.store = false
        }
    }

    const update = async () => {
        loading.value.update = true
        try {
            if(global.hasChanged(curso.value, copy_curso.value)) {
                const response = await axios.put('detalles-curso/' + curso.value.id, curso.value)
                fetch(programa_id.value)
                global.setAlert(response.data,'success')
            }
            resetData()
        } catch (error) {
            global.manejarError(error)
            if(error.status === 422) {
                errors.value = error.response.data.errors
            }
        } finally {
            loading.value.update = false
        }
    }

    const disabled = async () => {
        loading.value.destroy = true
        try {
            const response = await axios.post('detalles-curso/disabled/' + curso.value.id)
            fetch(programa_id.value)
            global.setAlert(response.data,'success')
            resetData()
        } catch (error) {
            global.manejarError(error)
            if(error.status === 422) {
                errors.value = error.response.data.errors
            }
        } finally {
            loading.value.destroy = false
        }
    }

    const destroy = async () => {
        loading.value.destroy = true
        try {
            
            const response = await axios.delete('detalles-curso/' + curso.value.id)
            fetch(programa_id.value)
            global.setAlert(response.data,'success')
            resetData()
        } catch (error) {
            global.manejarError(error)
            if(error.status === 422) {
                errors.value = error.response.data.errors
            }
        } finally {
            loading.value.destroy = false
        }
    }

    const syncHorarios = async () => {
        loading.value.update = true
        try {
            const response = await axios.post('detalles-curso/sync-horarios/' + curso.value.id, {
                horarios : horarios.value
            })
            fetch(programa_id.value)
            global.setAlert(response.data,'success')
            resetData()
        } catch (error) {
            global.manejarError(error)
            if(error.status === 422) {
                errors.value = error.response.data.errors
            }
        } finally {
            loading.value.update = false
        }
    }

    const assign = async () => {
        loading.value.update = true
        try {
            const response = await axios.post('detalles-curso/asignar-requisitos/' + curso.value.id, {
                requisitos : selected_requirements.value
            })
            fetch(programa_id.value)
            global.setAlert(response.data,'success')
            resetData()
        } catch (error) {
            global.manejarError(error)
            if(error.status === 422) {
                errors.value = error.response.data.errors
            }
        } finally {
            loading.value.update = false
        }
    }

    const assignRequirements = async (item) => {

        loading.value.requisitos = true
        try {
            const response = await axios.get('detalles-curso/get-requisitos/' + item.id)
            curso.value = response.data
            curso.value.curso = item.curso.nombre            
            selected_requirements.value = response.data.requisitos.map(requisito => requisito.id)
            modal.value.requisitos = true
        } catch (error) {
            global.manejarError(error)
            if(error.status === 422) {
                errors.value = error.response.data.errors
            }
        } finally {
            loading.value.requisitos = false
        }

        modal.value.requisitos = true
        curso.value = item
    }

    const disabledCurso = (item) => {
        curso.value = item
        modal.value.delete = true
    }

    const validateDuplicateCourseList = () => {

        let error = false

        cursos.value.forEach(item => {
            if(
                item.curso_id == curso.value.curso_id && 
                item.sede_id == curso.value.sede_id &&
                item.instructor_id == curso.value.instructor_id &&
                item.seccion == curso.value.seccion &&
                item.id != curso.value.id
            ) {
                errors.value = { detalles: ['El curso: '+item.curso.nombre+' ya existen en el listado'] }
                error = true
                return
            }
        })

        if(error) {
            return
        }
        
        update()
    }

    const selectHorarios = (item) => {
        horarios.value = item
    }

    const resetData = () => {
        curso.value = {}
        copy_curso.value = {}
        errors.value = []
        modal.value = {
            new : false,
            edit : false,
            delete : false,
            requisitos : false,
            horarios:  false,
        }
    }

    const exportExcel = async () => {

        loading.value.excel = true
    
        try {
    
            const response = await axios.post('exportar-excel',
                {
                    columns: headers,
                    data: cursos.value
                },
                {
                    responseType: 'blob'
                })
    
            const url = window.URL.createObjectURL(new Blob([response.data]));
    
            const link = document.createElement('a')
            link.href = url
            link.setAttribute('download', 'export.xlsx')
    
            document.body.appendChild(link)
            link.click();
    
            window.URL.revokeObjectURL(url)
            document.body.removeChild(link)
    
    
        } catch (error) {
            global.manejarError(error);
    
        } finally {
    
            loading.value.excel = false
        }
    }
    
    return {
        headers,
        programa_id,
        cursos,
        curso,
        copy_curso,
        selected_requirements,
        loading,
        errors,
        modal,
        horarios,
        
        fetch,
        show,
        store,
        update,
        destroy,
        disabledCurso,
        disabled,
        assign,
        assignRequirements,
        validateDuplicateCourseList,
        exportExcel,
        resetData,
        selectHorarios,
        editHorarios,
        syncHorarios,
    }
})
