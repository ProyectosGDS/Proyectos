import { defineStore } from 'pinia'
import { useGlobalStore } from '@/stores/global'
import { ref } from 'vue'
import axios from 'axios'

export const useInstructoresStore = defineStore('instructores', () => {
    
    const global = useGlobalStore()
    
    const headers = [
        { title : 'id', key : 'id', type : 'numeric' },
        { title : 'nombre', key : 'nombre', class: 'uppercase text-xs' },
        { title : 'estado', key : 'estado', width : '10px', align : 'center' },
        { title : '', key : 'actions', width : '10px', align : 'center' },
    ]

    const instructores = ref([])
    const instructor = ref({})
    const copy_instructor = ref({})
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
            const response = await axios.get('instructores')
            instructores.value = response.data
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
            const response = await axios.post('instructores', instructor.value)
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
            if(global.hasChanged(instructor.value, copy_instructor.value)) {
                const response = await axios.put('instructores/' + instructor.value.id, instructor.value)
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
            
            const response = await axios.delete('instructores/' + instructor.value.id)
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
        instructor.value = item
        copy_instructor.value = JSON.parse(JSON.stringify(item))
        modal.value.edit = true
    }

    const remove = (item) => {
        instructor.value = item
        modal.value.delete = true
    }

    const resetData = () => {
        instructor.value = {}
        copy_instructor.value = {}
        errors.value = []
        modal.value = {
            new : false,
            edit : false,
            delete : false
        }
    }
    
    return {
        headers,
        instructores,
        instructor,
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
