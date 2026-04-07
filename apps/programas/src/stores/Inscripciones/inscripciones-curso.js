import { defineStore } from 'pinia'
import { ref } from 'vue'
import axios from 'axios'

import { useGlobalStore } from '../global'
import { useBeneficiariosCursoStore } from './beneficiarios-curso'

export const useInscripcionesCursoStore = defineStore('inscripciones-curso', () => {

    const global = useGlobalStore()
    const beneficiario_curso = useBeneficiariosCursoStore()

    const headers = [
        { title : 'id', key : 'id', type : 'numeric' },
        { title : 'cui', key : 'beneficiario.cui' },
        { title : 'beneficiario', key : 'beneficiario.nombre_completo' },
        { title : 'programa', key : 'curso.programa.nombre' },
        { title : 'curso', key : 'curso.curso.nombre' },
        { title : 'sede', key : 'curso.sede.nombre_completo' },
        // { title : 'horario', key : 'curso.horario.nombre_completo' },
        { title : 'fecha inscripción', key : 'created_at', type : 'date', width :'10px', align : 'center' },
        { title : 'estado inscripción', key : 'estado', width :'10px', align : 'center' },
        { title : '', key : 'actions', width :'10px', align : 'center' },
    ]

    const cupo = ref(0)

    const programa_id  = ref(null)
    const curso_id  = ref(null)
    const year = ref(0)
    const beneficiarios = ref([])
    const inscripcion = ref({})
    const loading = ref({
        fetch : false,
        store : false,
        update :false,
        destroy : false,
        excel : false,
        pdf : false,
        beca : false,
    })
    const errors = ref([])
    const modal = ref({
        new : false,
        edit : false,
        delete :false,
        disabled : false,
        beca : false,
    })

    const fetch = async (curso_id) => {
        loading.value.fetch = true
        try {
            if(curso_id != '') {
                const response = await axios.get('inscripciones-curso/get-beneficiarios/' + curso_id + '/' + year.value)
                beneficiarios.value = response.data
                cupo.value = beneficiario_curso.curso.capacidad - beneficiarios.value.length
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

    const store = async () => {
        loading.value.store = true
        try {
            const response = await axios.post('inscripciones-curso/store-beneficiarios', {
                beneficiarios: beneficiarios.value,
                year : year.value,
            })
            global.setAlert(response.data, 'success')
            fetch(beneficiario_curso.curso.id)
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
            const response = await axios.put('inscripciones-curso/' + inscripcion.value.id, inscripcion.value)
            global.setAlert(response.data, 'success')
            fetch(beneficiario_curso.curso.id)
            resetData()
        } catch (error) {
            inscripcion.value.estado = inscripcion.value.estado == 'A' ? 'I' : 'A'
            global.manejarError(error)
            if (error.status === 422) {
                errors.value = error.response.data.errors
            }
        } finally {
            loading.value.update = false
        }
    }

    const destroy = async () => {
        loading.value.destroy = true
        try {
            const response = await axios.delete('inscripciones-curso/' + inscripcion.value.id)
            global.setAlert(response.data, 'success')
            fetch(beneficiario_curso.curso.id)
            resetData()
        } catch (error) {
            global.manejarError(error)
            if (error.status === 422) {
                errors.value = error.response.data.errors
            }
        } finally {
            loading.value.destroy = false
        }
    }

    const showBeca = async (item) => {
        inscripcion.value = item
        modal.value.beca = true
    }

    const assingBeca = async () => {
        loading.value.beca = true
        try {
            const response = await axios.put('inscripciones-curso/asignar-beca/' + inscripcion.value.id, {
                becado : inscripcion.value.becado
            })
            
            global.setAlert(response.data.message, 'success')
            fetch(beneficiario_curso.curso.id)
            resetData()
        } catch (error) {
            global.manejarError(error)
            if (error.status === 422) {
                errors.value = error.response.data.errors
            }
        } finally {
            loading.value.beca = false
        }
    }

    const changeStatusInscripcion = async () => {
        loading.value.update = true
        inscripcion.value.estado = inscripcion.value.estado == 'A' ? 'I' : 'A'
        await update()
    }


    const exportExcel = async () => {

        loading.value.excel = true
    
        try {
    
            const response = await axios.post('exportar-excel',
                {
                    columns: headers,
                    data: beneficiarios.value
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

    const exportPdf = async (curso_id) => {

        loading.value.pdf = true
    
        try {
    
            const response = await axios.post('inscripciones-curso/export-pdf',
                {
                    detalle_curso_id: curso_id,
                    anio_inscripcion: year.value,
                },
                {
                    responseType: 'blob'
                })
    
            const url = window.URL.createObjectURL(new Blob([response.data]));
    
            const link = document.createElement('a')
            link.href = url
            let nombre_archivo = 'listado_inscritos.pdf'
            link.setAttribute('download', nombre_archivo)
            // link.setAttribute('target', '_blank')
    
            document.body.appendChild(link)
            link.click();
    
            window.URL.revokeObjectURL(url)
            document.body.removeChild(link)
    
    
        } catch (error) {
            global.manejarError(error);
    
        } finally {
    
            loading.value.pdf = false
        }
    }

    const resetData = () => {
        errors.value = []
        modal.value = {
            new : false,
            edit : false,
            delete :false,
        }
        inscripcion.value = {}
    }

    return {
        headers,
        year,
        cupo,
        programa_id,
        curso_id,
        beneficiarios,
        inscripcion,
        loading,
        modal,
        errors,

        fetch, 
        store,
        update,
        destroy,
        showBeca,
        assingBeca,
        resetData,
        exportExcel,
        exportPdf,
        changeStatusInscripcion,
    }
})
