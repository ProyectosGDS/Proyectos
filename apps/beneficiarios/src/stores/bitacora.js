import { defineStore } from 'pinia'
import { ref } from 'vue'
import { useGlobalStore } from './global'
import axios from 'axios'

export const useBitacoraStore = defineStore('bitacora', () => {
    
    const global = useGlobalStore()

    const headers = [
        { title : 'id', key : 'id', type : 'numeric' },
        { title : 'acción', key : 'accion' },
        { title : 'descripción', key : 'descripcion' },
        { title : 'usuario', key : 'usuario.nombre' },
        { title : 'fecha creación', key : 'created_at', type : 'date' },
    ]

    const headersInscripciones = [
        { title : 'id', key : 'inscripcion_id', type : 'numeric' },
        { title : 'tipo', key : 'tipo' },
        { title : 'programa', key : 'programa' },
        { title : 'módulo / curso', key : 'modulo_curso' },
        { title : 'fecha inscripción', key : 'fecha_inscripcion', type : 'date', width : '10px', align : 'center' },
    ]

    const bitacoras = ref([])
    const bitacora = ref({})

    const loading = ref({
        show : false,
        store : false,
    })
    const modal = ref({
        bitacora : false,
        observaciones : false,
    })
    const errors = ref([])

    const show = async (id) => {
        loading.value.show = true
        try {
            const response = await axios.get('beneficiarios/bitacora/' + id )
            bitacoras.value = response.data
            modal.value.bitacora = true
        } catch (error) {
            global.manejarError(error)
            if(error.status === 422) {
                errors.value = error.response.data.errors
            }
        } finally {
            loading.value.show = false
        }
    }

    function observacion (item) {

        bitacora.value.beneficiario = item.nombre_completo
        bitacora.value.index = 2
        bitacora.value.tabla = 'BITACORA'
        bitacora.value.beneficiario_id = item.id

        modal.value.observaciones = true
    }

    const store = async () => {
        loading.value.store = true
        try {
            const response = await axios.post('bitacora',bitacora.value)
            global.setAlert(response.data,'success');
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

    

    function resetData() {
        modal.value = {
            bitacora : false,
            observaciones : false,
        }

        bitacoras.value = {
            observaciones : [],
            acciones: []
        }
        bitacora.value = {}
    }

    return {
        headersInscripciones,
        headers,
        bitacoras,
        bitacora,
        modal,
        loading,
        errors,
        
        show,
        store,
        observacion,
        resetData,

    }
})
