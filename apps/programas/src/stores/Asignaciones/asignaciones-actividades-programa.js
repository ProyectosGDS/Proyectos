import { defineStore } from 'pinia'
import { useGlobalStore } from '../global'
import { ref } from 'vue'
import axios from 'axios'

export const useAsignacionesActividadesProgramaStore = defineStore('asignaciones-actividades-programa', () => {
    
    const global = useGlobalStore()

    const headers = [
        { title : 'id', key : 'id', type : 'numeric' },
        { title : 'programa', key : 'programa' },
        { title : 'actividad', key : 'actividad' },
        { title : 'responsable', key : 'responsable' },
        { title : 'zona', key : 'zona' },
        { title : 'distrito', key : 'distrito' },
        { title : 'direccion', key : 'direccion' },
        { title : 'coordenadas', key : 'coordenadas' },
        { title : 'horario', key : 'horario' },
        { title : 'fechas', key : 'fechas' },
        { title : 'tipo', key : 'tipo' },
        { title : 'estado', key : 'estado' },
    ]

    const indice = ref(null)
    const year = ref(null)
    const programa_id = ref(null)
    const actividades = ref([])
    const actividad = ref({})
    const copy_actividad = ref({})
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
        show : false,
        delete : false,
    })

    const fetch = async (programa_id) => {
        loading.value.fetch = true
        try {
            if(programa_id != '') {
                const response = await axios.get('programas/get-actividades/' + programa_id + '/' + year.value)
                actividades.value = response.data
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

    const show = async (item) => {
        loading.value.show = true
        try {
            const response = await axios.get('detalles-actividades/' + item.id)
            actividad.value = response.data
            actividad.value.zona = response.data.zona == null ? {} : response.data.zona
            actividad.value.distrito = response.data.distrito == null ? {} : response.data.distrito

            console.log(actividad.value)
            
            copy_actividad.value = JSON.parse(JSON.stringify(actividad.value))
            modal.value.show = true
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
            const response = await axios.post('programas/store-actividades', {
                actividades : actividades.value
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
            if(global.hasChanged(actividad.value, copy_actividad.value)) {
                const response = await axios.put('detalles-actividades/' + actividad.value.id, actividad.value)
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

    const destroy = async () => {
        loading.value.destroy = true
        try {
            
            const response = await axios.delete('detalles-actividades/' + actividad.value.id)
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

    const remove = (item) => {
        actividad.value = item
        modal.value.delete = true
    }

    const resetData = () => {
        actividad.value = {}
        copy_actividad.value = {}
        errors.value = []
        modal.value = {
            show : false,
            delete : false
        }
    }

    const validateDuplicateActivityList = () => {

        let error = false


        actividades.value.forEach(item => {
            if(
                item.programa_id == actividad.value.programa_id && 
                item.actividad_id == actividad.value.actividad_id && 
                item.zona_id == actividad.value.zona_id && 
                item.distrito_id == actividad.value.distrito_id && 
                item.direccion.toUpperCase() == actividad.value.direccion.toUpperCase() && 
                item.hora_inicio == actividad.value.hora_inicio && 
                item.fecha_inicial == actividad.value.fecha_inicial+' 00:00:00' && 
                item.fecha_final == actividad.value.fecha_final+' 00:00:00' && 
                item.tipo_actividad_id == actividad.value.tipo_actividad_id &&
                item.id != actividad.value.id
            ) {
                errors.value = { detalles: ['La actividad: '+item.actividad+' ya existen en el listado'] }
                error = true
                return
            }
        })

        if(error) {
            return
        }
        
        update()
    }

    const exportExcel = async () => {

        loading.value.excel = true
    
        try {
    
            const response = await axios.post('exportar-excel',
                {
                    columns: headers,
                    data: actividades.value
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
        indice,
        year,
        programa_id,
        actividades,
        actividad,
        copy_actividad,
        loading,
        errors,
        modal,
        
        fetch,
        show,
        store,
        update,
        destroy,
        remove,
        validateDuplicateActivityList,
        exportExcel,
        resetData
    }
})
