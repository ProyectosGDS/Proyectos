import { defineStore } from 'pinia'
import { useGlobalStore } from '@/stores/global'
import { useProgramasStore } from '@/stores/Catalogos/programas'
import { useCursosModuloStore } from '@/stores/Asignaciones/cursos-modulo'
import { useHorariosStore } from '../Catalogos/horarios'
import { ref } from 'vue'
import axios from 'axios'
import { useInstructoresStore } from '../Catalogos/instructores'

export const useAsignacionesCursosModuloStore = defineStore('asignaciones-cursos-modulo', () => {
    
    const global = useGlobalStore()
    const programas = useProgramasStore()
    const cursosModulo = useCursosModuloStore()
    const storeHorarios = useHorariosStore()
    const storeInstructores = useInstructoresStore()

    const headers = [
        { title : 'id', key : 'detalle_curso_id', type : 'numeric' },
        { title : 'programa', key : 'curso.programa.nombre' },
        { title : 'modulo', key : 'modulo.nombre' },
        { title : 'curso', key : 'curso.curso.nombre' },
        { title : 'seccion', key : 'curso.seccion', width : '10px', align : 'center' },
        { title : 'instructor', key : 'curso.instructores.nombre', class: 'uppercase text-xs' },
        { title : 'sede', key : 'curso.sede.nombre_completo' },
        { title : 'horario', key : 'curso.horarios.nombre_completo' },
        { title : 'temporalidad', key : 'curso.temporalidad.nombre', class: 'uppercase text-xs', width : '10px', align : 'center' },
        { title : 'modalidad', key : 'curso.modalidad', width : '10px', align : 'center' },
        { title : '', key : 'actions', width : '10px', align : 'center' },
    ]
    
    const modulo_id = ref(null)
    const modulos = ref([])
    const cursos = ref([])
    const horarios = ref([])
    const instructores = ref([])
    const copy_cursos = ref([])
    const curso = ref({})
    const copy_curso = ref({})
    const loading = ref({
        fetch : false,
        show : false,
        update : false,
        destroy : false
    })
    const errors = ref([])
    const modal = ref({
        edit : false,
        delete : false,
        disabled : false,
        horarios : false,
        instructores : false,
    })

    const fetch = async (modulo_id) => {
        loading.value.fetch = true
        try {
            if(modulo_id != '') {
                const response = await axios.get('modulos/get-cursos/' + modulo_id)
                cursos.value = response.data
                copy_cursos.value = JSON.parse(JSON.stringify(response.data))
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
            copy_curso.value = JSON.parse(JSON.stringify(response.data))
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

    const showInstructores = async (id) => {
        storeInstructores.fetch()
        loading.value.show = true
        try {
            const response = await axios.get('detalles-curso/' + id)
            curso.value = response.data
            instructores.value = response.data.instructores
            modal.value.instructores = true
        } catch (error) {
            global.manejarError(error)
            if(error.status === 422) {
                errors.value = error.response.data.errors
            }
        } finally {
            loading.value.show = false
        }
    }

    const showHorarios = async (id) => {
        storeHorarios.fetch()
        loading.value.show = true
        try {
            const response = await axios.get('detalles-curso/' + id)
            curso.value = response.data
            horarios.value = response.data.horarios
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
            if(global.hasChanged(cursos.value, copy_cursos.value)) {
                const response = await axios.post('modulos/store-cursos', {
                    cursos: cursos.value
                })
                global.setAlert(response.data, 'success')
                fetch(cursosModulo.modulo.id)
            }
            resetData()
        } catch (error) {
            global.manejarError(error)
            if (error.status === 422) {
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
                fetch(cursosModulo.modulo.id)
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

    const disabledCurso = async () => {
        loading.value.destroy = true
        try {
            
            const response = await axios.post('detalles-curso/disabled/' + curso.value.detalle_curso_id)
            fetch(cursosModulo.modulo.id)
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

    const getModulosPrograma = () => {
        if(Object.keys(programas.programa).length > 0) {
            modulos.value = JSON.parse(programas.programa).modulos
        }
    }

    const disabled = (item) => {
        curso.value = item
        modal.value.disabled = true
    }

    const remove = (item) => {
        curso.value = item
        modal.value.delete = true
    }

    const syncHorarios = async () => {
        loading.value.update = true
        try {
            if(global.hasChanged(curso.value, copy_curso.value)) {
                const response = await axios.post('detalles-curso/sync-horarios/' + curso.value.id, {
                    horarios : horarios.value
                })
                fetch(cursosModulo.modulo.id)
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

    const syncInstructores = async () => {
        loading.value.update = true
        try {
            if(global.hasChanged(curso.value, copy_curso.value)) {
                const response = await axios.post('detalles-curso/sync-instructores/' + curso.value.id, {
                    instructores : instructores.value
                })
                fetch(cursosModulo.modulo.id)
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

    const validateDuplicateCourseList = () => {

        let error = false

        cursos.value.forEach(item => {
            if(
                item.curso.curso_id == curso.value.curso_id && 
                item.curso.sede_id == curso.value.sede_id &&
                item.curso.instructor_id == curso.value.instructor_id &&
                item.curso.seccion == curso.value.seccion &&
                item.curso.id != curso.value.id
            ) {
                errors.value = { detalles: ['El curso: '+item.curso.curso.nombre+' ya existen en el listado'] }
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

    const selectHorario = (item) => {
        horarios.value = item
    }
    const selectInstructores = (item) => {
        instructores.value = item
    }
    
    return {
        headers,
        modulo_id,
        modulos,
        cursos,
        curso,
        horarios,
        instructores,
        copy_curso,
        loading,
        errors,
        modal,
        
        fetch,
        show,
        showHorarios,
        showInstructores,
        store,
        update,
        disabledCurso,
        getModulosPrograma,
        validateDuplicateCourseList,
        remove,
        disabled,
        resetData,
        exportExcel,
        selectHorario,
        selectInstructores,
        syncHorarios,
        syncInstructores,
    }
})
