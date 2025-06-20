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
    const modal = ref({
        cursos : false,
    })
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
        modal.value = {
            cursos: false,
        }
    }
    
    const errors = ref([])

    const selectCurso = () => {
        
        if(catalogos.cursos.length > 1) {
            catalogos.errors = { curso: ['Debe seleccionar solo un curso'] }
            return
        }

        catalogos.curso = catalogos.cursos[0]
        getBeneficiariosCurso()
        modal.value.cursos = false
        catalogos.errors = []
    }

    const getBeneficiariosCurso = async () => {
        catalogos.loading.beneficiarios = true
        try {
            if(catalogos.programa != null && catalogos.curso.hasOwnProperty('id') && date.value) {
                const response = await axios.get('control-asistencia/beneficiarios-curso', {
                    params: {
                        detalle_curso_id : catalogos.curso.id,
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
            
            const response = await axios.post('control-asistencia/registrar/' + catalogos.curso.id, {
                asistencia : asistencia.value,
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

        // const response = await axios.get('control-asistencia/listado-asistencia',
        //     {
        //         params: {
        //                 detalle_curso_id : catalogos.curso.id,
        //                 year : year.value,
        //                 fecha : date.value,
        //         }

        //     },
        //     {
        //         responseType: 'blob',
        //         headers: {
        //             'Content-Type': 'application/pdf',
        //         }
        //     })

        // const url = window.URL.createObjectURL(new Blob([response.data]));
        const url = import.meta.env.VITE_MY_API_URL_BASE 
                    + 'control-asistencia/listado-asistencia?detalle_curso_id=' 
                    + catalogos.curso.id 
                    + '&year=' 
                    + year.value 
                    + '&fecha=' 
                    + date.value
                    + '&tipo=curso'

        const link = document.createElement('a')
        link.href = url
        link.setAttribute('download', 'listado-asistencia.pdf')

        document.body.appendChild(link)
        link.click();

        window.URL.revokeObjectURL(url)
        document.body.removeChild(link)


    } catch (error) {
        global.manejarError(error);

    } finally {

        loading.value.download = false
    }
}
    

    return {

        asistencia,
        year,
        date,
        modal,
        loading,

        fetchCursos,
        store,
        selectCurso,
        getBeneficiariosCurso,
        download,
        resetData,
    }
})
