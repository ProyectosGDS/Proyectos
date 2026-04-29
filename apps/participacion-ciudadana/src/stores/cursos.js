import axios from 'axios'
import { defineStore } from 'pinia'
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useBeneficiariosStore } from './Inscripciones/beneficiarios'

export const useCursosStore = defineStore('cursos', () => {

    const router = useRouter()
    const beneficiariosStore = useBeneficiariosStore()

    const headers = [
        { title : 'id', key : 'id', type : 'numeric' },
        { title : 'curso', key : 'curso' },
        { title : 'descripcion', key : 'descripcion'}
    ]

    const categorias = ref([])
    const cursos = ref([])
    const curso = ref({})
    const detalle = ref({})
    const inscripcion = ref({})
    const modulo = ref({})
    const loading = ref(false)
    const errors = ref([])

    const detalleHeaders = [
        // { title : 'programa', key: 'programa.nombre' },
        { title : 'curso id', key: 'id' },
        { title : 'curso', key: 'curso.nombre' },
        // { title : 'zona', key: 'sede.zona_id', type : 'numeric' },
        { title : 'sección', key: 'seccion' },
        { title : 'sede', key: 'sede.nombre_completo' },
        { title : 'horarios', key: 'horarios' },
        { title : 'cupo disponible', key: 'capacidad' },
        { title : 'modalidad', key: 'modalidad' },
        { title : 'inicio', key: 'fecha_inicial', type : 'dateformat' },
        { title : 'final', key: 'fecha_final', type : 'dateformat' },
    ]

    async function fetch () {
        try {
            loading.value = true
            const response = await axios.get('participacion-ciudadana',{
                params : {
                    tipo : 'CURSO',
                }
            })
            cursos.value = response.data
        } catch (error) {
            console.error(error)
            errors.value = error
        } finally {
            loading.value = false
        }
    }

    async function show_curso(zona) {
        try {
            loading.value = true
            const response = await axios.get('participacion-ciudadana/zona/' + zona )
            cursos.value = response.data
        } catch (error) {
            console.error(error)
            errors.value = error
        } finally {
            loading.value = false
        }
    }    

    // function detalleCurso (item) {
    //     router.push({ name : 'Detalle del curso', params : { curso_id : item.id } } )
    // }

    function detalleCurso (zona) {
        router.push({ name : 'Detalle del curso', params : { zona : zona } } )
    }

    function fetchCategorias () {
        axios.get('categorias/index')
        .then(response => categorias.value = response.data)
        .catch(error => console.error(error))
    }

    const getCurso = (item, type) => {
        detalle.value = item
        beneficiariosStore.inscripcion(item.id, type)
    }


    return {
        headers,
        detalleHeaders,
        router,
        categorias,
        inscripcion,
        cursos,
        curso,
        detalle,
        modulo,
        loading,
        errors,

        fetch,
        fetchCategorias,
        detalleCurso,
        show_curso,
        getCurso,
    }
})
