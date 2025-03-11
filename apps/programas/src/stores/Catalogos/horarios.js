import { defineStore } from 'pinia'
import { useGlobalStore } from '@/stores/global'
import { ref } from 'vue'
import axios from 'axios'

export const useHorariosStore = defineStore('horarios', () => {
    
    const global = useGlobalStore()
    
    const headers = [
        { title : 'id', key : 'id', type : 'numeric' },
        { title : 'hora', key : 'hora'},
        { title : 'días', key : 'dias'},
        { title : 'estado', key : 'estado', width : '10px', align : 'center' },
        { title : '', key : 'actions', width : '10px', align : 'center' },
    ]

    const horarios = ref([])
    const horario = ref({})
    const dias = ref([])
    const copy_horario = ref({})
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
            const response = await axios.get('horarios')
            horarios.value = response.data
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
        horario.value.dias = dias.value
        try {
            const response = await axios.post('horarios', horario.value)
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
            horario.value.dias = dias.value
            if(global.hasChanged(horario.value, copy_horario.value)) {
                const response = await axios.put('horarios/' + horario.value.id, horario.value)
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
            const response = await axios.delete('horarios/' + horario.value.id)
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

        const days = [
            'lun',
            'mar',
            'mie',
            'jue',
            'vie',
            'sab',
            'dom',
        ]

        horario.value = item

        days.forEach((day) => {
            if(horario.value[day]) {
                dias.value.push(horario.value[day])
            }        
        })
        
        copy_horario.value = JSON.parse(JSON.stringify(item))
        modal.value.edit = true
    }

    const remove = (item) => {
        horario.value = item
        modal.value.delete = true
    }

    const resetData = () => {
        horario.value = {}
        dias.value = []
        copy_horario.value = {}
        errors.value = []
        modal.value = {
            new : false,
            edit : false,
            delete : false
        }
    }
    
    return {
        headers,
        dias,
        copy_horario,
        horarios,
        horario,
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
