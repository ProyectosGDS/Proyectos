import axios from 'axios'
import { defineStore } from 'pinia'
import { ref } from 'vue'
import { useGlobalStore } from '../global'

export const useGenerarReporteStore = defineStore('generar-reporte', () => {
    
    const global = useGlobalStore()

    const anio_inscripcion = ref(0)
    const escuela_id = ref(null)
    const programas = ref([])
    const loading = ref(false)
    const errors = ref([])



    const reporte_excel = async () => {

        loading.value = true

        try {

            const response = await axios.post('programas/generar-reporte',
                {
                    anio_inscripcion : anio_inscripcion.value,
                    programas :  programas.value
                },
                {
                    responseType: 'blob'
                })

            const url = window.URL.createObjectURL(new Blob([response.data]));

            const link = document.createElement('a')
            link.href = url
            link.setAttribute('download', 'reporte_generado.xlsx')

            document.body.appendChild(link)
            link.click();

            window.URL.revokeObjectURL(url)
            document.body.removeChild(link)


        } catch (error) {
            if(error.response.status == 422) {
                errors.value = error.response.data.errors
            } else {
                global.manejarError(error);
            }

        } finally {

            loading.value = false
        }
    }
    
    return {
        anio_inscripcion,
        escuela_id,
        programas,
        loading,
        errors,

        reporte_excel,
    }
})
