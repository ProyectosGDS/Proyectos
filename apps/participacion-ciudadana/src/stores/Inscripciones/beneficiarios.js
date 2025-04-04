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
        estado : 'P',
    })

    const copy_beneficiario = ref({})
    
    const messageCui = ref('Ingrese cui')
    const cui = ref('')
    const reload = ref(false)
    const success = ref(false)
    const nuevo_registro = ref(false)
    const params = ref({
        formacion_id : null,
        formacion_tipo : ''
    })

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

    function inscripcion (id, formacion_tipo) {
        params.value = {
            formacion_id : id,
            formacion_tipo : formacion_tipo
        }
        modal.value.new = true
    }


    const store = async () => {
        loading.value.store = true
        beneficiario.value.formacion_id = params.value.formacion_id
        beneficiario.value.formacion_tipo = params.value.formacion_tipo

        beneficiario.value.estado = 'P'
        try {
            const response = await axios.post('participacion-ciudadana/inscripcion', beneficiario.value)
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

    async function getBeneficiarioUnico (cui) {
        loading.value.show = true
        try {
            const response = await axios.post('participacion-ciudadana/consulta-beneficiario-unico',{
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

    function updatePropertyBeneficiario (newData){
        Object.assign(beneficiario.value,newData)
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
            estado : 'P',
        }
        modal.value = {
            new : false,
            edit : false,
            delete : false
        }
        copy_beneficiario.value = {}
        nuevo_registro.value = false
        errors.value = []
        messageCui.value = ''
        cui.value = ''
        success.value = false
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
        
        inscripcion,
        getBeneficiarioUnico,
        store,
        resetData,
    }
})
