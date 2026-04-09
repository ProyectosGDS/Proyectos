import { defineStore } from 'pinia'
import { ref } from 'vue'
import { useCatalogosStore } from './catalogos'
import { useGlobalStore } from './global'
import axios from 'axios'

export const useCursoStore = defineStore('curso', () => {
    

    const catalogos = useCatalogosStore()
    const global = useGlobalStore()

    const asistencia = ref([])
    const year = ref(new Date().getFullYear())
    const date = ref(new Date().toISOString().slice(0, 10))
    const escuela = ref(null)
    const modal = ref({
        cursos : false,
    })

    const label_curso = ref('')


    const loading = ref({
        store : false,
        download : false,
    })

    const fetchCursos = () => {
        modal.value.cursos = true
    }

    const resetData = () => {
        catalogos.cursos = []
        catalogos.beneficiarios = []
        catalogos.curso = {}
        catalogos.errors = []
        label_curso.value = ''
        modal.value = {
            cursos: false,
        }
    }
    
    const errors = ref([])

    const selectCurso = async () => {
        
        if(catalogos.cursos.length > 1) {
            catalogos.errors = { curso: ['Debe seleccionar solo un curso'] }
            return
        }

        catalogos.curso = catalogos.cursos[0]
        label_curso.value = catalogos.curso.curso.nombre
        await getBeneficiariosCurso()
        modal.value.cursos = false
        catalogos.errors = []
    }

    const getBeneficiariosCurso = async () => {
        catalogos.loading.beneficiarios = true
        asistencia.value = []
        try {
            if(catalogos.programa != null && catalogos.curso.hasOwnProperty('id') && date.value) {
                const response = await axios.get('control-asistencia/beneficiarios-curso', {
                    params: {
                        detalle_curso_id : catalogos.curso.id,
                        year : year.value,
                        fecha : date.value,
                    }
                })
                catalogos.beneficiarios = await response.data
                syncAsistencia()
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

    const syncAsistencia = () => {
        asistencia.value = []
        if(catalogos.beneficiarios) {
            asistencia.value = catalogos.beneficiarios
                    .filter(beneficiario => beneficiario?.beneficiario?.control_asistencia)
        }
    }

    const store = async () => {
        loading.value.store = true
        try {
            
            const response = await axios.post('control-asistencia/registrar/' + catalogos.curso.id, {
                asistencia : asistencia.value.map(beneficiario => beneficiario.beneficiario_id),
                tipo : 'curso',
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
                    detalle_curso_id : catalogos.curso.id,
                    year : year.value,
                    fecha : date.value,
                    tipo : 'curso'
                }, // los query params que quieras mandar
                responseType: "blob", // MUY IMPORTANTE
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
        } finally {
            loading.value.download = false
        }
    }

    
    return {

        asistencia,
        year,
        date,
        escuela,
        modal,
        loading,
        label_curso,

        fetchCursos,
        store,
        selectCurso,
        getBeneficiariosCurso,
        download,
        resetData,
    }
})
