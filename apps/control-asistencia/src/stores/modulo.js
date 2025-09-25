import { defineStore } from 'pinia'
import { ref } from 'vue'
import { useCatalogosStore } from './catalogos'
import { useGlobalStore } from './global'
import axios from 'axios'

export const useModuloStore = defineStore('modulo', () => {
    

    const catalogos = useCatalogosStore()
    const global = useGlobalStore()

    const asistencia = ref([])
    const year = ref(new Date().getFullYear())
    const date = ref(new Date().toISOString().slice(0, 10))
    const modal = ref({
        modulos : false,
    })
    const loading = ref({
        store : false,
        download : false,
    })

    const fetchModulos = () => {
        modal.value.modulos = true
    }

    const resetData = () => {
        catalogos.modulos = []
        catalogos.beneficiarios = []
        catalogos.modulo = {}
        catalogos.errors = []
        modal.value = {
            modulos: false,
        }
    }
    
    const errors = ref([])

    const selectModulo = () => {
        
        if(catalogos.modulos.length > 1) {
            catalogos.errors = { modulo: ['Debe seleccionar solo un modulo'] }
            return
        }

        catalogos.modulo = catalogos.modulos[0]
        getBeneficiariosModulo()
        modal.value.modulos = false
        catalogos.errors = []
    }

    const getBeneficiariosModulo = async () => {
        catalogos.loading.beneficiarios = true
        try {
            if(catalogos.programa != null && catalogos.modulo.hasOwnProperty('id') && date.value) {
                const response = await axios.get('control-asistencia/beneficiarios-modulo', {
                    params: {
                        modulo_id : catalogos.modulo.id,
                        year : year.value,
                        fecha : date.value,
                    }
                })
                catalogos.beneficiarios = response.data
                asistencia.value = response.data
                    .filter(beneficiario => beneficiario.beneficiario.control_asistencia)
                    .map(beneficiario => beneficiario.beneficiario.id)
            }

            return
        } catch (error) {
            global.manejarError(error)
            if(error.status === 422) {
                catalogos.errors = error.response.data.errors
            }
        } finally {
            catalogos.loading.beneficiarios = false
        }
    }

    const store = async () => {
        loading.value.store = true
        try {
            
            const response = await axios.post('control-asistencia/registrar/' + catalogos.modulo.id, {
                asistencia : asistencia.value,
                tipo : 'modulo',
                fecha : date.value,
            })

            global.setAlert(response.data,'success')
                
        } catch (error) {
            global.manejarError(error)
            if(error.status === 422) {
                catalogos.errors = error.response.data.errors
            }
        } finally {
            loading.value.store = false
        }
    }

    const download = async () => {
        loading.value.download = true
        try {
            const response = await axios.get("control-asistencia/listado-asistencia", {
                params : {
                    detalle_curso_id : catalogos.modulo.id,
                    year : year.value,
                    fecha : date.value,
                    tipo : 'modulo'
                }, 
                responseType: "blob",
            })

            // Crear blob con la respuesta
            const blob = new Blob([response.data], { type: "application/pdf" })
            const url = window.URL.createObjectURL(blob)

            // Crear un enlace temporal para forzar la descarga
            const link = document.createElement("a")
            link.href = url
            link.setAttribute("download", "listado-asistencia.pdf") // nombre del archivo
            document.body.appendChild(link)
            link.click()

            // Limpiar
            link.remove()
            window.URL.revokeObjectURL(url)

        } catch (error) {
            console.error("Error al descargar el PDF:", error)
        }finally {
            loading.value.download = false
        }
    }
    
    return {

        asistencia,
        year,
        date,
        modal,
        loading,

        fetchModulos,
        store,
        selectModulo,
        getBeneficiariosModulo,
        download,
        resetData,
    }
})
