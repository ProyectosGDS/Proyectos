import { defineStore } from 'pinia'
import { useGlobalStore } from '@/stores/global'
import { ref } from 'vue'
import axios from 'axios'

export const useSedesStore = defineStore('sedes', () => {
    
    const global = useGlobalStore()
    
    const headers = [
        { title : 'id', key : 'id', type : 'numeric' },
        { title : 'nombre', key : 'nombre', class: 'uppercase text-xs' },
        { title : 'dirección', key : 'direccion', class: 'uppercase text-xs' },
        { title : 'zona', key : 'zona.descripcion', class: 'uppercase text-xs' },
        { title : 'distrito', key : 'distrito.nombre', class: 'uppercase text-xs' },
        { title : 'estado', key : 'estado', width : '10px', align : 'center' },
        { title : '', key : 'actions', width : '10px', align : 'center' },
    ]

    const sedes = ref([])
    const sede = ref({})
    const copy_sede = ref({})
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
            const response = await axios.get('sedes')
            sedes.value = response.data
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
            const response = await axios.post('sedes', sede.value)
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
            if(global.hasChanged(sede.value, copy_sede.value)) {
                const response = await axios.put('sedes/' + sede.value.id, sede.value)
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
            
            const response = await axios.delete('sedes/' + sede.value.id)
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
        sede.value = item
        copy_sede.value = JSON.parse(JSON.stringify(item))
        modal.value.edit = true
    }

    const remove = (item) => {
        sede.value = item
        modal.value.delete = true
    }

    const resetData = () => {
        sede.value = {}
        copy_sede.value = {}
        errors.value = []
        modal.value = {
            new : false,
            edit : false,
            delete : false
        }
    }
    
    return {
        headers,
        sedes,
        sede,
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
