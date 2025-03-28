import { defineStore } from 'pinia'
import { useGlobalStore } from '@/stores/global'
import { ref } from 'vue'
import axios from 'axios'

export const useBeneficiariosStore = defineStore('beneficiarios', () => {

    const global = useGlobalStore()

    const beneficiario = ref({
        sexo : 'M',
        domicilio : {
            departamento_id : 7,
            grupo_zona : {},
        },
        datos_academicos : {},
        datos_medicos : {},
        responsable : {},
        emergencia : {},
        estado : 'V',
    })
    const copy_beneficiario = ref({})
    
    const messageCui = ref('Ingrese cui')
    const cui = ref('')
    const reload = ref(false)
    const success = ref(false)
    const nuevo_registro = ref(false)

    const loading = ref({
        show : false,
        store : false,
        update : false,
        destroy : false,
        search : false,
        estado : false,
    })
    const errors = ref([])
    const modal = ref({
        new : false,
        edit : false,
        delete : false,
        estado : false,
    })

    const show = async (id) => {
        loading.value.show = true
        try {
            const response = await axios.get('beneficiarios/' + id)
            beneficiario.value = response.data
            beneficiario.value.domicilio == null ? beneficiario.value.domicilio = {} : null
            beneficiario.value.datos_medicos == null ? beneficiario.value.datos_medicos = {} : null
            beneficiario.value.datos_academicos == null ? beneficiario.value.datos_academicos = {} : null
            beneficiario.value.responsable == null ? beneficiario.value.responsable = {} : null
            beneficiario.value.emergencia == null ? beneficiario.value.emergencia = {} : null
            copy_beneficiario.value = JSON.parse(JSON.stringify(response.data))
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

    const store = async () => {
        loading.value.store = true
        try {
            const response = await axios.post('beneficiarios', beneficiario.value)
            reload.value = true
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

    const create = async () => {
        loading.value.store = true
        try {
            const response = await axios.post('beneficiarios/create', beneficiario.value)
            beneficiario.value = response.data
            errors.value = []
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
            if (global.hasChanged(beneficiario.value, copy_beneficiario.value)) {
                const response = await axios.put('beneficiarios/'+ beneficiario.value.id, beneficiario.value)
                global.setAlert(response.data,'success')
                reload.value = true
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
            const response = await axios.delete('beneficiarios/'+ beneficiario.value.id)
            global.setAlert(response.data,'success')
            reload.value = true
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

    async function getBeneficiarioUnico (cui) {
        loading.value.show = true
        try {
            const response = await axios.post('beneficiarios/consulta-beneficiario-unico',{
               cui : cui
            })

            success.value = true
            const beneficiario = response.data

            if(!beneficiario.id) {
                nuevo_registro.value = true
                messageCui.value = 'Se encontro información en sistema antiguo'
            } else {
                messageCui.value = 'Se encontro información'
            }

            beneficiario.domicilio = response.data.domicilio == null ? { departamento_id : 7, grupo_zona : {} } : response.data.domicilio
            beneficiario.datos_medicos = {}
            beneficiario.datos_academicos = {}
            beneficiario.responsable = {}
            beneficiario.emergencia = {}
            updatePropertyBeneficiario(beneficiario)
    
        } catch (error) {
            nuevo_registro.value = true
            messageCui.value = error.response.data
            success.value = true
            console.error(error)
        }finally {
            loading.value.show = false
        }
    }

    async function getBeneficiarioUnicoDetalles (cui) {
        loading.value.show = true
        try {
            const response = await axios.post('beneficiarios/consulta-beneficiario-unico',{
               cui : cui
            })
            
            success.value = true
            const beneficiario = response.data

            if(!beneficiario.id) {
                nuevo_registro.value = true
                messageCui.value = 'Se encontro información en sistema antiguo'
            } else {
                messageCui.value = 'Se encontro información'
            }

            beneficiario.domicilio = response.data.domicilio == null ? { departamento_id : 7, grupo_zona : {} } : response.data.domicilio
            beneficiario.datos_medicos = response.data.datos_medicos == null ? {} : response.data.datos_medicos
            beneficiario.datos_academicos = response.data.datos_academicos == null ? {} : response.data.datos_academicos
            beneficiario.responsable = response.data.responsable == null ? {} : response.data.responsable
            beneficiario.emergencia = response.data.emergencia == null ? {} : response.data.emergencia
            updatePropertyBeneficiario(beneficiario)
    
        } catch (error) {
            nuevo_registro.value = true
            messageCui.value = error.response.data
            success.value = true
            console.error(error)
        }finally {
            loading.value.show = false
        }
    }

    const edit = (item) => {
        beneficiario.value = item
        copy_beneficiario.value = JSON.parse(JSON.stringify(item))
        modal.value.edit = true
    }

    const remove = (item) => {
        beneficiario.value = item
        modal.value.delete = true
    }

    const resetData = () => {
        beneficiario.value = {
            sexo : 'M',
            domicilio : {
                departamento_id : 7,
                grupo_zona : {},
            },
            datos_academicos : {},
            datos_medicos : {},
            responsable : {},
            emergencia : {},
            estado : 'V',
        }
        modal.value = {
            new : false,
            edit : false,
            delete : false
        }
        copy_beneficiario.value = {}
        errors.value = []
        messageCui.value = ''
        cui.value = ''
        success.value = false
    }

    function updatePropertyBeneficiario (newData){
        Object.assign(beneficiario.value,newData)
    }

    return {
        beneficiario,
        messageCui,
        cui,
        reload,
        success,
        nuevo_registro,

        loading,
        errors,
        modal,
        
        show,
        store,
        create,
        update,
        destroy,
        edit,
        getBeneficiarioUnico,
        getBeneficiarioUnicoDetalles,
        remove,
        resetData,
    }
})
