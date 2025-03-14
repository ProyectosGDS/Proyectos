import { defineStore } from 'pinia';
import { useGlobalStore } from './global'
import { ref } from 'vue';
import axios from 'axios';

export const useEventosStore = defineStore('eventos', () => {
    
    const global = useGlobalStore()

    const headers = [
        { title : 'id', key : 'id', type : 'numeric' },
        { title : 'nombre', key : 'nombre' },
        { title : 'descripcion', key : 'descripcion' },
        { title : 'ubicacion', key : 'ubicacion' },
        { title : 'responsable', key : 'responsable' },
        { title : 'duracion', key : 'duracion' },
        { title : 'fecha inicial', key : 'fecha_inicial', type : 'date' },
        { title : 'fecha final', key : 'fecha_final', type : 'date'},
        { title : 'horario', key : 'horario' },
        { title : 'tipo', key : 'tipo' },
        { title : 'estado', key : 'estado' },
        { title : 'creado por', key : 'usuario' },
        { title : 'dependencia', key : 'dependencia' },
        { title : '', key : 'actions', width : '10px', align : 'center' },
    ]

    const year = ref(null)
    const eventos = ref([])
    const evento = ref({})
    const copy_evento = ref({})
    const loading = ref({
        fetch : false,
        show : false,
        store : false,
        update : false,
        destroy : false,
    })

    const modal = ref({
        new : false,
        edit : false,
        delete : false,
    })

    const errors = ref([])

    const fetch = async (year) => {
        loading.value.fetch = true
        try {
            const response = await axios.get('eventos',{
                params : {
                    year : year
                }
            })
            eventos.value = response.data
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
            const response = await axios.post('eventos', evento.value)
            fetch(year.value)
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

    const show = async (id) => {
        loading.value.show = true
        try {
            const response = await axios.get('eventos/' + id)
            evento.value = response.data
            copy_evento.value = JSON.parse(JSON.stringify(response.data))
            modal.value.edit = true
        } catch (error) {
            global.manejarError(error)
            if(error.status === 422) {
                errors.value = error.response.data.errors
            }
        } finally {
            loading.value.show = false
        }
    }

    const update = async () => {
        loading.value.update = true
        try {
            if(global.hasChanged(evento.value, copy_evento.value)) {
                const response = await axios.put('eventos/' + evento.value.id, evento.value)
                fetch(year.value)
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
            
            const response = await axios.delete('eventos/' + evento.value.id)
            fetch(year.value)
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

    const remove = (item) => {
        evento.value = item
        modal.value.delete = true
    }

    const resetData = () => {
        evento.value = {}
        copy_evento.value = {}
        errors.value = []
        modal.value = {
            new : false,
            edit : false,
            delete : false
        }
    }

    return {
        headers,
        eventos,
        year,
        evento,
        loading,
        errors,
        modal,
        
        fetch,
        show,
        store,
        update,
        destroy,
        remove,
        resetData
    }
})
