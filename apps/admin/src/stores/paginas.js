import { defineStore } from 'pinia'
import { useGlobalStore } from './global'
import { ref } from 'vue'
import axios from 'axios'

export const usePaginasStore = defineStore('paginas', () => {

    const global = useGlobalStore()

    const headers = [
        { title : 'id', key : 'id', type : 'numeric' },
        { title : 'titulo', key : 'titulo' },
        { title : 'link', key : 'link'},
        { title : 'icon', key : 'icon' },
        { title : 'vista icono', key : 'vista-icono', width : '10px', align : 'center' },
        { title : 'orden', key : 'orden' },
        { title : 'pagina padre', key : 'padre.titulo' },
        { title : '', key : 'actions', width : '10px', align : 'center' },
    ]

    const paginas = ref([])
    const pagina = ref({})
    const copyPagina = ref({})
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
            const response = await axios.get('paginas')
            paginas.value = response.data
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
            const response = await axios.post('paginas', pagina.value)
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
            if (global.hasChanged(pagina.value,copyPagina.value)) {
                const response = await axios.put('paginas/'+pagina.value.id, pagina.value)
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
            const response = await axios.delete('paginas/'+pagina.value.id)
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
        pagina.value = item
        copyPagina.value = JSON.parse(JSON.stringify(item))
        modal.value.edit = true
    }

    const remove = (item) => {
        pagina.value = item
        modal.value.delete = true
    }

    const resetData = () => {
        pagina.value = {}
        modal.value = {
            new : false,
            edit : false,
            delete : false
        }
        errors.value = []
    }

    return {
        headers,
        paginas,
        pagina,
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
