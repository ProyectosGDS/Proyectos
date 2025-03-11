import { defineStore } from 'pinia'
import { useGlobalStore } from './global'
import { ref } from 'vue'
import axios from 'axios'

export const usePermisosStore = defineStore('permisos', () => {

    const global = useGlobalStore()

    const headers = [
        { title : 'id', key : 'id', type : 'numeric' },
        { title : 'nombre', key : 'nombre', class: 'uppercase text-xs' },
        { title : 'descripcion', key : 'descripcion', class: 'uppercase text-xs' },
        { title : 'grupo', key : 'grupo' },
        { title : 'app', key : 'app' },
        { title : '', key : 'actions', width : '10px', align : 'center' },
    ]

    const permisos = ref([])
    const permiso = ref({})
    const copyPermiso = ref({})
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
            const response = await axios.get('permisos')
            permisos.value = response.data
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
            const response = await axios.post('permisos', permiso.value)
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
            if (global.hasChanged(permiso.value,copyPermiso.value)) {
                const response = await axios.put('permisos/'+permiso.value.id, permiso.value)
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
            const response = await axios.delete('permisos/'+permiso.value.id)
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
        permiso.value = item
        copyPermiso.value = JSON.parse(JSON.stringify(item))
        modal.value.edit = true
    }

    const remove = (item) => {
        permiso.value = item
        modal.value.delete = true
    }

    const resetData = () => {
        permiso.value = {}
        modal.value = {
            new : false,
            edit : false,
            delete : false
        }
        errors.value = []
    }

    return {
        headers,
        permisos,
        permiso,
        loading,
        errors,
        modal,
        
        fetch,
        store,
        update,
        destroy,
        edit,
        remove,
        resetData,
    }
})
