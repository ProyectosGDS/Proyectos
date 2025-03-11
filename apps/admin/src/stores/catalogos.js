import { defineStore } from 'pinia'
import { ref } from 'vue'
import axios from 'axios'

export const useCatalogosStore = defineStore('catalogos', () => {


    const dependencias = ref([])
    const perfiles = ref([])
    const loading = ref({
        fetchDependencias : false,
        fetchPerfiles : false,
    })
    const errors = ref([])

    const fetchDependencias = async () => {
        loading.value.fetchDependencias = true
        try {
            const response = await axios.get('dependencias')
            dependencias.value = response.data
        } catch (error) {
            global.manejarError(error)
            if(error.status === 422) {
                errors.value = error.response.data.errors
            }
        } finally {
            loading.value.fetchDependencias = false
        }
    }

    const fetchPerfiles = async () => {
        loading.value.fetchPerfiles = true
        try {
            const response = await axios.get('perfiles')
            perfiles.value = response.data
        } catch (error) {
            global.manejarError(error)
            if(error.status === 422) {
                errors.value = error.response.data.errors
            }
        } finally {
            loading.value.fetchPerfiles = false
        }
    }
    
    return {

        dependencias,
        perfiles,
        loading,
        errors,

        fetchDependencias,
        fetchPerfiles,

    }
})
