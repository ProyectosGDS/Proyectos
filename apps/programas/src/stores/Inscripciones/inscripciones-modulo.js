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
        { title : 'programa', key : 'curso.programa.nombre' },
        { title : 'curso', key : 'curso.curso.nombre' },
        { title : 'sede', key : 'curso.sede.nombre_completo' },
        { title : 'horario', key : 'curso.horario.nombre_completo' },
        { title : 'fecha inscripción', key : 'created_at', type : 'date', width :'10px', align : 'center' },
        { title : 'estado inscripción', key : 'estado', width :'10px', align : 'center' },
        { title : '', key : 'actions', width :'10px', align : 'center' },
    ]

    const programa_id  = ref(null)
    const cupo = ref(0)
    const modulo  = ref({})
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
                beneficiarios: beneficiarios.value
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
        resetData,
        exportExcel,
    }
})
