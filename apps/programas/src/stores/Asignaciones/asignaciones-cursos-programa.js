import { defineStore } from 'pinia'
import { useGlobalStore } from '../global'
import { ref } from 'vue'
import axios from 'axios'

export const useAsignacionesCursosProgramaStore = defineStore('asignaciones-cursos-programa', () => {
    
    const global = useGlobalStore()

    const headers = [
        { title : 'id', key : 'id', type : 'numeric' },
        { title : 'programa', key : 'programa' },
        { title : 'curso', key : 'curso' },
        { title : 'seccion', key : 'seccion', width : '10px', align : 'center' },
        { title : 'instructor', key : 'instructor', class: 'uppercase text-xs' },
        { title : 'sede', key : 'sede' },
        { title : 'horario', key : 'horario' },
        { title : 'temporalidad', key : 'temporalidad', class: 'uppercase text-xs', width : '10px', align : 'center' },
        { title : 'modalidad', key : 'modalidad', width : '10px', align : 'center' },
        { title : 'capacidad', key : 'capacidad', width : '10px', align : 'center' },
        { title : 'inicia', key : 'fecha_inicial', type : 'date' },
        { title : 'termina', key : 'fecha_final', type : 'date' },
        { title : 'público', key : 'publico', width : '10px', align : 'center' },
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
    const errors = ref([])
    const modal = ref({
        edit : false,
        delete : false,
        requisitos : false,
    })

    const fetch = async (programa_id) => {
        loading.value.fetch = true
        try {
            if(programa_id != '') {
                const response = await axios.get('programas/get-cursos/' + programa_id)
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
                item.horario_id == curso.value.horario_id &&
                item.id != curso.value.id
            ) {
                errors.value = { detalles: ['El curso: '+item.curso+' ya existen en el listado'] }
                error = true
                return
            }
        })

        if(error) {
            return
        }
        
        update()
    }

    const resetData = () => {
        curso.value = {}
        copy_curso.value = {}
        errors.value = []
        modal.value = {
            new : false,
            edit : false,
            delete : false
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
        resetData
    }
})
