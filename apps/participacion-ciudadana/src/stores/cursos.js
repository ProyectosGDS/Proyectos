import axios from 'axios'
import { defineStore } from 'pinia'
import { ref } from 'vue'
import { useRouter } from 'vue-router'

export const useCursosStore = defineStore('cursos', () => {

    const router = useRouter()

    const headers = [
        { title : 'id', key : 'id', type : 'numeric' },
        { title : 'modulo/curso', key : 'curso' },
        { title : 'tipo', key : 'tipo' },
        { title : 'modalidad', key : 'modalidad', width : '10px', align : 'center' },

    ]

    const categorias = ref([])
    const cursos = ref([])
    const curso = ref({})
    const inscripcion = ref({})
    const pivoteCurso = ref([])
    const modulo = ref({})
    const loading = ref(false)
    const errors = ref([])


    const detalleHeaders = [
        { title : 'curso id', key: 'id' },
        { title : 'zona', key: 'sede.zona_id' },
        { title : 'sede', key: 'sede.nombre_completo' },
        { title : 'sección', key: 'seccion' },
        { title : 'cupo disponible', key: 'capacidad' },
        { title : 'temporalidad', key: 'temporalidad.nombre' },
        { title : 'programa', key: 'programa.nombre' },
    ]

    
    async function fetch () {
        try {
            loading.value = true
            const response = await axios.get('participacion-ciudadana')
            cursos.value = response.data
        } catch (error) {
            console.error(error)
            errors.value = error
        } finally {
            loading.value = false
        }
    }

    async function show_curso(curso_id) {
        try {
            loading.value = true
            const response = await axios.get('participacion-ciudadana/curso/' + curso_id )
            curso.value = response.data
        } catch (error) {
            console.error(error)
            errors.value = error
        } finally {
            loading.value = false
        }
    }

    async function show_modulo(modulo_id) {
        try {
            loading.value = true
            const response = await axios.get('participacion-ciudadana/modulo/' + modulo_id )
            modulo.value = response.data
        } catch (error) {
            console.error(error)
            errors.value = error
        } finally {
            loading.value = false
        }
    }

    function detalleCurso (item) {
        router.push({ name : 'Detalle del curso', params : { curso_id : item.id } } )
    }

    function detalleModulo (item) {
        router.push({ name : 'Detalle del módulo', params : { modulo_id : item.id } } )
    }

    function fetchCategorias () {
        axios.get('categorias/index')
        .then(response => categorias.value = response.data)
        .catch(error => console.error(error))
    }


    return {
        headers,
        detalleHeaders,
        router,
        categorias,
        inscripcion,
        cursos,
        curso,
        modulo,
        loading,
        errors,

        fetch,
        fetchCategorias,
        detalleCurso,
        detalleModulo,
        show_curso,
        show_modulo,
    }
})
