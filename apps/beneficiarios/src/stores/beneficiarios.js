import { defineStore } from 'pinia'
import { useGlobalStore } from './global'
import { ref } from 'vue'
import axios from 'axios'
import { useVerificacionDatosBeneficiarioStore } from './verificacion-datos-beneficiario'

export const useBeneficiariosStore = defineStore('beneficiarios', () => {

    const global = useGlobalStore()
    const verificacion = useVerificacionDatosBeneficiarioStore()

    const headers = [
        { title : 'id', key : 'id', type : 'numeric' },
        { title : 'cui', key : 'cui' },
        { title : 'beneficiario', key : 'nombre_completo', exclude : true },
        { title : 'sexo', key : 'sexo', width : '10px', align : 'center' },
        { title : 'fecha nacimiento', key : 'fecha_nacimiento', type : 'date' },
        { title : 'edad', key : 'edad', text : ' años', exclude : true, width : '70px', align : 'center' },
        { title : 'interlocutor', key : 'interlocutor' },
        { title : 'estado', key : 'estado', width : '10px', align : 'center' },
        { title : 'activo', key : 'deleted_at', width : '10px', align : 'center' },
        { title : '', key : 'actions', width : '10px', align : 'center', exclude : true },
    ]

    const headersHistorial = [
        { title : 'asignación', key: 'asignacion' },
        { title : 'cui', key: 'cui' },
        { title : 'beneficiario', key: 'beneficiario' },
        { title : 'dependencia', key: 'dependencia' },
        { title : 'curso', key: 'nombre_curso' },
        { title : 'programa', key: 'programa' },
        { title : 'año', key: 'anio' },
        { title : 'estado', key: 'estatus' }
    ]

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
    const codeFetchBeneficiario = ref(null)
    const cui = ref('')
    const reload = ref(false)
    const success = ref(false)

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
        historial : false,
    })

    const show = async (item) => {
        const id = item.beneficiario_id ?? item.id
        verificacion.inscripcion_id = item.id;
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

    async function fetchBeneficiarioUnico (cui) {
        try {
            loading.value.search = true
            const response = await axios.post('beneficiarios/consulta-back-up',{cui : cui})
                messageCui.value = response.data.message
                codeFetchBeneficiario.value = response.data.code
                updatePropertyBeneficiario(response.data.data)
                success.value = response.data.success

        } catch (error) {
            messageCui.value = 'Error al realizar la consulta'
            success.value = false
            console.error(error)
        }finally{
            loading.value.search = false
        }
    }

    async function historial (cui) {
        try {
            loading.value.search = true
            const response = await axios.get('beneficiarios/historial',{
                params : {
                    cui : cui
                }
            })
            
            beneficiario.value = response.data
            modal.value.historial = true

        } catch (error) {
            if(error.status === 422) {
                global.setAlert(error.response.data,'danger')
            }
        }finally{
            loading.value.search = false
        }
    }

    const changeStatus = async () => {
        loading.value.estado = true
        try {
            const response = await axios.post('beneficiarios/estado/' + beneficiario.value.id, {
                estado : beneficiario.value.estado
            })
            global.setAlert(response.data,'success')
            resetData()
        } catch (error) {
            global.manejarError(error)
            if(error.status === 422) {
                errors.value = error.response.data.errors
            }
        } finally {
            loading.value.estado = false
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

    const status = (item) => {
        beneficiario.value = item
        modal.value.estado = true
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
        codeFetchBeneficiario.value = null
        cui.value = ''
        success.value = false
    }

    function updatePropertyBeneficiario (newData){
        Object.assign(beneficiario.value,newData)
    }

    return {
        headers,
        headersHistorial,
        beneficiario,
        copy_beneficiario,
        messageCui,
        codeFetchBeneficiario,
        cui,
        reload,
        success,

        loading,
        errors,
        modal,
        
        show,
        store,
        update,
        destroy,
        edit,
        status,
        fetchBeneficiarioUnico,
        changeStatus,
        remove,
        resetData,
        historial,
    }
})
