import { defineStore } from 'pinia'
import { ref } from 'vue'
import axios from 'axios'
import { useGlobalStore } from '../global'

export const useBeneficiariosProgramaStore = defineStore('beneficiarios-programa', () => {
    
    const global = useGlobalStore()

    const headers = [
        { title : 'id', key : 'inscripcion_id', type : 'numeric' },
        { title : 'cui', key : 'cui' },
        { title : 'beneficiario', key : 'beneficiario' },
        { title : 'programa', key : 'programa' },
        { title : 'módulo/curso', key : 'modulo_curso' },
        { title : 'fecha inscripcion', key : 'fecha_inscripcion', type : 'date', width :'10px', align : 'center' },
        { title : 'tipo', key : 'tipo' },
        { title : 'estado', key : 'estado', width :'10px', align : 'center' },
        { title : '', key : 'actions', width :'10px', align : 'center' },
    ]

    const year = ref(0)
    const programa_id  = ref(null)
    const beneficiarios = ref([])
    const beneficiario = ref({})
    const loading = ref({
        fetch : false,
    })
    const errors = ref([])
    const modal = ref({
        new : false,
    })

    const fetch = async (programa_id) => {
        loading.value.fetch = true
        try {
            if(programa_id != '') {
                const response = await axios.get('programas/get-beneficiarios/' + programa_id + '/' + year.value)
                beneficiarios.value = response.data
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

    return {
        year,
        headers,
        programa_id,
        beneficiarios,
        beneficiario,
        loading,
        modal,
        errors,

        fetch, 
    }
})
