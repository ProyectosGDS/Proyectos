import { defineStore } from 'pinia'
import { useGlobalStore } from './global'
import { ref } from 'vue'
import axios from 'axios'

export const useUsuariosStore = defineStore('usuarios', () => {

    const global = useGlobalStore()

    const headers = [
        { title : 'id', key : 'id', type : 'numeric' },
        { title : 'cui', key : 'cui' },
        { title : 'nombre', key : 'nombre', class: 'uppercase text-xs' },
        { title : 'dependencia', key : 'dependencia.nombre', },
        { title : 'perfil', key : 'perfil.nombre' },
        { title : 'fecha creación', key : 'created_at', type : 'date' },
        { title : 'status', key : 'deleted_at', width : '10px', align : 'center' },
        { title : '', key : 'actions', width : '10px', align : 'center' },
    ]

    const usuarios = ref([])
    const usuario = ref({})
    const copyUsuario = ref({})
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
        delete : false,
        resetPassword : false,
    })

    const fetch = async () => {
        loading.value.fetch = true
        try {
            const response = await axios.get('usuarios')
            usuarios.value = response.data
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
            const response = await axios.post('usuarios', usuario.value)
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
            if (global.hasChanged(usuario.value, copyUsuario.value)) {
                const response = await axios.put('usuarios/'+usuario.value.id, usuario.value)
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
            const response = await axios.delete('usuarios/'+usuario.value.id)
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

    const resetPassword = async () => {
        loading.value.destroy = true
        try {
            const response = await axios.delete('usuarios/reiniciar-password/'+usuario.value.id)
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
        usuario.value = item
        copyUsuario.value = JSON.parse(JSON.stringify(item))
        modal.value.edit = true
    }

    const remove = (item) => {
        usuario.value = item
        modal.value.delete = true
    }

    const resetPass = (item) => {
        usuario.value = item
        modal.value.resetPassword = true
    }

    const resetData = () => {
        usuario.value = {}
        modal.value = {
            new : false,
            edit : false,
            delete : false
        }
        errors.value = []
    }

    return {
        headers,
        usuarios,
        usuario,
        loading,
        errors,
        modal,
        
        fetch,
        store,
        update,
        destroy,
        resetPassword,
        edit,
        remove,
        resetPass,
        resetData,
    }
})
