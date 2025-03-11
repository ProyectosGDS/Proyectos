import { defineStore } from 'pinia'
import { ref } from 'vue'
import axios from 'axios'

import { useGlobalStore } from '../global'
import { useBeneficiariosActividadStore } from './beneficiarios-actividad'

export const useInscripcionesActividadStore = defineStore('inscripciones-actividad', () => {

    const global = useGlobalStore()
    const beneficiario_actividad = useBeneficiariosActividadStore()

    const headers = [
        { title : 'id', key : 'id', type : 'numeric' },
        { title : 'cui', key : 'beneficiario.cui' },
        { title : 'beneficiario', key : 'beneficiario.nombre_completo' },
        { title : 'programa', key : 'actividad.programa.nombre' },
        { title : 'actividad', key : 'actividad.actividad.nombre' },
        { title : 'sede', key : 'actividad.sede.nombre_completo' },
        { title : 'horario', key : 'actividad.horario.nombre_completo' },
        { title : 'fecha inscripción', key : 'created_at', type : 'date', width :'10px', align : 'center' },
        { title : 'estado inscripción', key : 'estado', width :'10px', align : 'center' },
        { title : '', key : 'actions', width :'10px', align : 'center' },
    ]

    const programa_id  = ref(null)
    const actividad_id  = ref(null)
    const year = ref(0)
    const beneficiarios = ref([])
    const inscripcion = ref({})
    const loading = ref({
        fetch : false,
        store : false,
        update :false,
        destroy : false,
        excel : false,
    })
    const errors = ref([])
    const modal = ref({
        new : false,
        edit : false,
        delete :false,
        disabled : false,
    })

    const fetch = async (actividad_id) => {
        loading.value.fetch = true
        try {
            if(actividad_id != '') {
                const response = await axios.get('inscripciones-actividad/get-beneficiarios/' + actividad_id + '/' + year.value)
                beneficiarios.value = response.data
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
            const response = await axios.post('inscripciones-actividad/store-beneficiarios', {
                beneficiarios: beneficiarios.value
            })
            global.setAlert(response.data, 'success')
            fetch(beneficiario_actividad.actividad.id)
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
            const response = await axios.put('inscripciones-actividad/' + inscripcion.value.id, inscripcion.value)
            global.setAlert(response.data, 'success')
            fetch(beneficiario_actividad.actividad.id)
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

    const destroy = async () => {
        loading.value.destroy = true
        try {
            const response = await axios.delete('inscripciones-actividad/' + inscripcion.value.id)
            global.setAlert(response.data, 'success')
            fetch(beneficiario_actividad.actividad.id)
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
        programa_id,
        actividad_id,
        beneficiarios,
        inscripcion,
        loading,
        modal,
        errors,

        fetch, 
        store,
        update,
        destroy,
        resetData,
        exportExcel,
    }
})
