import { defineStore } from 'pinia'
import { ref } from 'vue'
import axios from 'axios'

import { useGlobalStore } from '../global'

export const useInscripcionesModuloStore = defineStore('inscripciones-modulo', () => {

    const global = useGlobalStore()

    const headers = [
        { title : 'id', key : 'id', type : 'numeric' },
        { title : 'cui', key : 'beneficiario.cui' },
        { title : 'beneficiario', key : 'beneficiario.nombre_completo' },
        { title : 'programa', key : 'modulo.programa.nombre' },
        { title : 'modulo', key : 'modulo.nombre' },
        { title : 'sede', key : 'modulo.sede.nombre_completo' },
        { title : 'horario', key : 'modulo.horario.nombre_completo' },
        { title : 'fecha inscripción', key : 'created_at', type : 'date', width :'10px', align : 'center' },
        { title : 'estado inscripción', key : 'estado', width :'10px', align : 'center' },
        { title : '', key : 'actions', width :'10px', align : 'center' },
    ]

    const programa_id  = ref(null)
    const cupo = ref(0)
    const modulo  = ref({})
    const year = ref(0)
    const month = ref(0)
    const beneficiarios = ref([])
    const inscripcion = ref({})
    const loading = ref({
        fetch : false,
        store : false,
        update :false,
        destroy : false,
        excel : false,
        pdf : false,
    })
    const errors = ref([])
    const modal = ref({
        new : false,
        edit : false,
        delete :false,
        disabled : false,
    })

    const fetch = async () => {        
        loading.value.fetch = true
        try {
            if(typeof(modulo.value) === 'string') {
                const module = JSON.parse(modulo.value)
                const response = await axios.get('inscripciones-modulo/get-beneficiarios/' + module.id + '/' + year.value)
                beneficiarios.value = response.data
                cupo.value = module.capacidad - response.data.length 
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
            const response = await axios.post('inscripciones-modulo/store-beneficiarios', {
                beneficiarios: beneficiarios.value,
                year : year.value,
                month : month.value
            })
            global.setAlert(response.data, 'success')
            fetch()
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
            const response = await axios.put('inscripciones-modulo/' + inscripcion.value.id, inscripcion.value)
            global.setAlert(response.data, 'success')
            fetch()
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
    const changeStatusInscripcion = async () => {
        loading.value.update = true
        inscripcion.value.estado = inscripcion.value.estado == 'A' ? 'I' : 'A'
        await update()
    }

    const destroy = async () => {
        loading.value.destroy = true
        try {
            const response = await axios.delete('inscripciones-modulo/' + inscripcion.value.id)
            global.setAlert(response.data, 'success')
            fetch()
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

    const showBeca = (item) => {
        inscripcion.value = item
        modal.value.beca = true
    }

    const assignBeca = async () => {
        loading.value.update = true
        try {
            const response = await axios.put('inscripciones-modulo/asignar-beca/' + inscripcion.value.id, {
                becado : inscripcion.value.becado
            })
            global.setAlert(response.data.message, 'success')
            fetch()
            resetData()
        } catch (error) {
            global.manejarError(error)
            if (error.status === 422) {
                errors.value = error.response.data.errors
            }
        } finally {
            loading.value.update = false
        }
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

    const exportPdf = async () => {

        loading.value.pdf = true
    
        try {
    
            const response = await axios.post('inscripciones-modulo/export-pdf',
                {
                    modulo_id: JSON.parse(modulo.value).id,
                    anio_inscripcion: year.value
                },
                {
                    responseType: 'blob'
                })
    
            const url = window.URL.createObjectURL(new Blob([response.data]));
    
            const link = document.createElement('a')
            link.href = url
            link.setAttribute('download', 'listados_inscritos.pdf')
    
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
        month,
        cupo,
        programa_id,
        modulo,
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
        assignBeca,
        resetData,
        exportExcel,
        exportPdf,
        changeStatusInscripcion,
    }
})
