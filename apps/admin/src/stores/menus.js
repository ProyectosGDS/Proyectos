import { defineStore } from 'pinia'
import { useGlobalStore } from './global'
import { ref } from 'vue'
import axios from 'axios'

export const useMenusStore = defineStore('menus', () => {

    const global = useGlobalStore()

    const headers = [
        { title : 'id', key : 'id', type : 'numeric' },
        { title : 'nombre', key : 'nombre', class: 'uppercase text-xs' },
        { title : '', key : 'actions', width : '10px', align : 'center' },
    ]

    const menus = ref([])
    const menu = ref({})
    const copymenu = ref({})
    const paginas = ref([])
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
            const response = await axios.get('menus')
            menus.value = response.data
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
            const response = await axios.post('menus', menu.value)
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
            if (global.hasChanged(copymenu.value, menu.value)) {
                const response = await axios.put('menus/'+menu.value.id, menu.value)
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
            const response = await axios.delete('menus/'+menu.value.id)
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
        menu.value = item
        copymenu.value = JSON.parse(JSON.stringify(item))
        modal.value.edit = true
    }

    const remove = (item) => {
        menu.value = item
        modal.value.delete = true
    }

    const resetData = () => {
        menu.value = {}
        copymenu.value = {}
        modal.value = {
            new : false,
            edit : false,
            delete : false
        }
        errors.value = []
    }

    return {
        headers,
        menus,
        paginas,
        menu,
        copymenu,
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
