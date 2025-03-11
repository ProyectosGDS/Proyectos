import { defineStore } from 'pinia'
import { useGlobalStore } from './global'
import { ref } from 'vue'
import axios from 'axios'

export const useRolesStore = defineStore('roles', () => {

    const global = useGlobalStore()

    const headers = [
        { title : 'id', key : 'id', type : 'numeric' },
        { title : 'nombre', key : 'nombre', class: 'uppercase text-xs' },
        { title : 'descripcion', key : 'descripcion', class: 'uppercase text-xs' },
        { title : '', key : 'actions', width : '10px', align : 'center' },
    ]

    const roles = ref([])
    const rol = ref({})
    const copyRol = ref({})
    const permisos = ref([])
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
            const response = await axios.get('roles')
            roles.value = response.data
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
        // rol.value.permisos = permisos.value
        try {
            const response = await axios.post('roles', rol.value)
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
        // rol.value.permisos = permisos.value
        try {
            if (global.hasChanged(copyRol.value, rol.value)) {
                const response = await axios.put('roles/'+rol.value.id, rol.value)
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
            const response = await axios.delete('roles/'+rol.value.id)
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

    // const edit = (item) => {
    //     const clone = JSON.parse(JSON.stringify(item))
    //     rol.value = clone
    //     permisos.value = clone.permisos.map(permiso => permiso.id)
    //     rol.value.permisos = permisos.value
    //     copyRol.value = JSON.parse(JSON.stringify(rol.value))
    //     modal.value.edit = true
    // }

    const edit = (item) => {
        rol.value = item
        // const clone = JSON.parse(JSON.stringify(item))
        // rol.value = clone
        // permisos.value = clone.permisos.map(permiso => permiso.id)
        // rol.value.permisos = permisos.value
        copyRol.value = JSON.parse(JSON.stringify(item))
        modal.value.edit = true
    }

    const remove = (item) => {
        rol.value = item
        modal.value.delete = true
    }

    const resetData = () => {
        rol.value = {}
        copyRol.value = {}
        modal.value = {
            new : false,
            edit : false,
            delete : false
        }
        errors.value = []
    }

    return {
        headers,
        roles,
        permisos,
        rol,
        copyRol,
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
