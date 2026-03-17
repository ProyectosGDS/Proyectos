import axios from 'axios'
import { defineStore } from 'pinia'
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useBeneficiariosStore } from './Inscripciones/beneficiarios'


export const useProgramasStore = defineStore('programas', () => {

    const router = useRouter()
    const beneficiariosStore = useBeneficiariosStore()

    const programas = ref([])
    const programa = ref({})
    const modulo = ref({})
    const errors = ref([])
    const loading = ref(false)

    const headers = [
        { title : 'id', key : 'id', type : 'numeric' },
        { title : 'programa', key : 'programa' },
    ];

    const detalleHeaders = [
        { title : 'id', key : 'id', type : 'numeric' },
        { title : 'zona', key : 'sede.zona_id' },
        { title : 'carrera / diplomados', key : 'nombre' },
        { title : 'sección', key : 'seccion' },
        { title : 'cupo / capacidad', key : 'capacidad', align : 'center' },
        { title : 'sede', key : 'sede.nombre_completo' },
        { title : 'modalidad', key : 'modalidad' },
    ];

    async function fetch () {
        try {
            loading.value = true
            const response = await axios.get('participacion-ciudadana',{
                params : {
                    tipo : 'PROGRAMA',
                }
            })
            programas.value = response.data
        } catch (error) {
            console.error(error)
            errors.value = error
        } finally {
            loading.value = false
        }
    }

    async function show_programa(programa_id) {
        try {
            loading.value = true
            const response = await axios.get('participacion-ciudadana/programa/' + programa_id )
            programa.value = response.data
        } catch (error) {
            console.error(error)
            errors.value = error
        } finally {
            loading.value = false
        }
    }

    function detallesPrograma (item) {
        router.push({ name : 'Detalle del programa', params : { programa_id : item.id } } )
    }

    const getModulo = (item, type) => {
        modulo.value = item
        beneficiariosStore.inscripcion(item.id, type)
    }

    return {
        headers,
        detalleHeaders,
        programas,
        programa,
        modulo,
        errors,
        loading,

        fetch,
        show_programa,
        detallesPrograma,
        getModulo,
    }
})
