import { defineStore } from 'pinia'
import axios from 'axios'
import { ref } from 'vue'

import { useGlobalStore } from './global'

export const useInscripcionStore = defineStore('inscripcion', () => {

    const global = useGlobalStore()

    const beneficiario = ref({
        sexo : 'M',
        domicilios : {
            grupo_x_zona : {},
            departamento_id : 7,
            municipio_id : 108,
        },
        responsables : {
            sexo : 'M',
        },
        emergencia : {
            sexo : 'M',
        },
        datos_academicos : {
            tipo_establecimiento : 'PU'
        },
        datos_medicos : {},
    })

    const loading = ref({
        store : false,
    })

    const errors = ref([])
    const modal = ref({
        new : false,
    })

    function inscripcion (id_clase) {
        beneficiario.value.id_clase = id_clase
        modal.value.new = true
    }

    async function store() {
        loading.value.store = true
        beneficiario.value.estatus = 'P'
        try {
            const response = await axios.post('participacion-ciudadana/inscripcion',beneficiario.value)
            global.setAlert(response.data,'success')
            resetData()
        } catch (error) {
            if(error.response.data.hasOwnProperty('message') && error.response.data.hasOwnProperty('line')) {
                global.setAlert(error.response.data.message,'danger')
            }else if(error.response.data.hasOwnProperty('errors')){
                errors.value = error.response.data.errors
            }else{
                console.error(error.response.data)
            }
        } finally {
            loading.value.store = false
        }
    }

    function resetData() {
        beneficiario.value = {
            sexo : 'M',
            domicilios : {
                grupo_x_zona : {},
                departamento_id : 7,
                municipio_id : 108,
            },
            responsables : {
                sexo : 'M',
            },
            emergencia : {
                sexo : 'M',
            },
            datos_academicos : {
                tipo_establecimiento : 'PU'
            },
            datos_medicos : {},
        }
        modal.value = {
            new : false,
        }
        errors.value = []
    }
    
    return {
        beneficiario,
        loading,
        modal,
        errors,

        store,
        inscripcion,
        resetData,
    }
})
