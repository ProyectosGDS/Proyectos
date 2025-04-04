import { defineStore } from 'pinia'
import { useGlobalStore } from './global'
import axios from 'axios'
import { ref } from 'vue'
import { useCatalogosStore } from './catalogos'
import { useBeneficiariosStore } from './beneficiarios'


export const useVerificacionDatosBeneficiarioStore = defineStore('verificacion-datos-beneficiario', () => {
  
    const global = useGlobalStore()
    const catalogos = useCatalogosStore()
    const beneficiario =  useBeneficiariosStore()

    const headers = [
        { title : 'id', key : 'id', type : 'numeric' },
        { title : 'cui', key : 'beneficiario.cui' },
        { title : 'beneficiario', key : 'beneficiario.nombre_completo' },
        { title : 'estado', key : 'beneficiario.estado',  align : 'center', width : '10px' },
        { title : 'fecha inscripción', key : 'created_at', type : 'date', align : 'center', width : '10px' },
        { title : '', key : 'actions', width : '10px', align : 'center' },

    ]

    const beneficiarios = ref([])
    const year = ref(0)
    const modulo_curso_id = ref(null)

    const loading = ref({
        fetch : false,
        update : false,
    })

    const modal = ref({
        edit : false,
        change_status : false,
    })

    const errors = ref([])

    const fetch = async () => {
        loading.value.fetch = true
        try {
            const response = await axios.get('programas/beneficiarios-modulo-curso', {
                params : {
                    modulo_curso_id : modulo_curso_id.value,
                    year : year.value,
                    tipo : catalogos.tipo
                }
            })
            beneficiarios.value = response.data
        } catch (error) {
            global.manejarError(error)
            if(error.status === 422) {
                errors.value = error.response.data.errors
            }
        } finally {
            loading.value.fetch = false
        }
    }

    const update = async () => {
        loading.value.update = true
        beneficiario.beneficiario.estado = 'V'
        try {
            if (global.hasChanged(beneficiario.beneficiario, beneficiario.copy_beneficiario)) {
                const response = await axios.put('beneficiarios/'+ beneficiario.beneficiario.id, beneficiario.beneficiario)
                global.setAlert(response.data,'success')
                fetch()
            }
            beneficiario.resetData()
        } catch (error) {
            global.manejarError(error)
            if(error.status === 422) {
                errors.value = error.response.data.errors
            }
        } finally {
            loading.value.update = false
        }
    }

    const changeStatus = async () => {
        try {
            await beneficiario.changeStatus()
            fetch()
        } catch (error) {
            console.error(error)
        }
    }
    
    return {
        headers,
        beneficiarios,
        year,
        modulo_curso_id,
        loading,
        modal,
        errors,

        fetch,
        update,
        changeStatus,
    }
})
