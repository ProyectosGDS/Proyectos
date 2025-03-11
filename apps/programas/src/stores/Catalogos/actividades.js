import { defineStore } from 'pinia'
import { useGlobalStore } from '@/stores/global'
import { ref } from 'vue'
import axios from 'axios'

export const useActividadesStore = defineStore('actividades', () => {
    
    const global = useGlobalStore()
    
    const headers = [
        { title : 'id', key : 'id', type : 'numeric' },
        { title : 'nombre', key : 'nombre' },
        { title : 'descripcion', key : 'descripcion', class: 'uppercase text-xs' },
        { title : '', key : 'actions', width : '10px', align : 'center' },
    ]


    const actividades = ref([])
    const actividad = ref({})
    const copy_actividad = ref({})
    const loading = ref({
        fetch : false,
        store : false,
        update : false,
        destroy : false
    })
    const errors = ref([])
    const modal = ref({
        new : false,
        edit : false,
        delete : false
    })

    const fetch = async () => {
        loading.value.fetch = true
        try {
            const response = await axios.get('actividades')
            actividades.value = response.data
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
            const response = await axios.post('actividades', actividad.value)
            fetch()
            global.setAlert(response.data,'success')
            resetData()
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
                const response = await axios.put('actividades/' + actividad.value.id, actividad.value)
                fetch()
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
            
            const response = await axios.delete('actividades/' + actividad.value.id)
            fetch()
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

    const edit = (item) => {
        actividad.value = item
        copy_actividad.value = JSON.parse(JSON.stringify(item))
        modal.value.edit = true
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
            new : false,
            edit : false,
            delete : false
        }
    }
    
    return {
        headers,
        actividades,
        actividad,
        loading,
        errors,
        modal,
        
        fetch,
        store,
        update,
        destroy,
        edit,
        remove,
        resetData
    }
})
