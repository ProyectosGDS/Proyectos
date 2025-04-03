import { defineStore } from 'pinia'
import { useGlobalStore } from '@/stores/global'
import { ref } from 'vue'
import axios from 'axios'

export const useModulosStore = defineStore('modulos', () => {
    
    const global = useGlobalStore()
    
    const headers = [
        { title : 'id', key : 'id', type : 'numeric' },
        { title : 'nombre', key : 'nombre', class: 'uppercase text-xs' },
        { title : 'descripcion', key : 'descripcion', class: 'uppercase text-xs' },
        { title : 'programa', key : 'programa.nombre' },
        { title : 'inicia', key : 'fecha_inicial', type : 'date' },
        { title : 'termina', key : 'fecha_final', type : 'date' },
        { title : 'público', key : 'publico', width : '10px', align : 'center' },
        { title : 'capacidad', key : 'capacidad', width : '10px', align : 'center' },
        { title : 'estado', key : 'estado', width : '10px', align : 'center' },
        { title : '', key : 'actions', width : '10px', align : 'center' },
    ]

    const programa_id = ref(null)
    const modulos = ref([])
    const modulo = ref({})
    const requisitos = ref([])
    const selected_requirements = ref([])
    const copy_modulo = ref({})
    const loading = ref({
        fetch : false,
        store : false,
        update : false,
        destroy : false,
        requisitos : false,
    })
    const errors = ref([])
    const modal = ref({
        new : false,
        edit : false,
        delete : false,
        requisitos : false,
    })

    const getRequirements = async () => {
        loading.value.requisitos = true
        try {
            const response = await axios.get('requisitos')
            requisitos.value = response.data
        } catch (error) {
            global.manejarError(error)
            if(error.status === 422) {
                errors.value = error.response.data.errors
            }
        } finally {
            loading.value.requisitos = false
        }
    }

    const fetch = async (programa_id) => {
        loading.value.fetch = true
        try {
            if(programa_id !== null) {
                const response = await axios.get('programas/' + programa_id)
                modulos.value = response.data.modulos
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
            const response = await axios.post('modulos', modulo.value)
            fetch(programa_id.value)
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
            if(global.hasChanged(modulo.value, copy_modulo.value)) {
                const response = await axios.put('modulos/' + modulo.value.id, modulo.value)
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
            
            const response = await axios.delete('modulos/' + modulo.value.id)
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

    const assign = async (item) => {
        loading.value.update = true
        try {
            const response = await axios.post('modulos/asignar-requisitos/' + modulo.value.id, {
                requisitos : selected_requirements.value
            })
            fetch(programa_id.value)
            global.setAlert(response.data,'success')
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

    const edit = (item) => {
        modulo.value = item
        copy_modulo.value = JSON.parse(JSON.stringify(item))
        modal.value.edit = true
    }

    const remove = (item) => {
        modulo.value = item
        modal.value.delete = true
    }

    const resetData = () => {
        modulo.value = {}
        copy_modulo.value = {}
        errors.value = []
        modal.value = {
            new : false,
            edit : false,
            delete : false
        }
    }

    const assignRequirements = (item) => {
        selected_requirements.value = item.requisitos.map(requisito => requisito.id)
        modulo.value = item
        modal.value.requisitos = true
    }

    return {
        headers,
        programa_id,
        modulos,
        modulo,
        requisitos,
        selected_requirements,
        loading,
        errors,
        modal,
        
        getRequirements,
        fetch,
        store,
        update,
        destroy,
        assign,
        edit,
        remove,
        assignRequirements,
        resetData
    }
})
