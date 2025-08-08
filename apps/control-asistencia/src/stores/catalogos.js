import { defineStore } from 'pinia'
import { useGlobalStore } from './global'
import { ref } from 'vue'
import axios from 'axios'

export const useCatalogosStore = defineStore('catalogos', () => {
    
    const global = useGlobalStore()

    const programas = ref([])
    const programa = ref(null)
    const modulo = ref({})
    const curso = ref({})
    const modulos = ref([])
    const cursos_programa = ref([])
    const modulos_programa = ref([])
    const cursos = ref([])
    const beneficiarios = ref([])
    const escuelas = ref([])
    const errors = ref([])
    const loading = ref({
        programas : false,
        modulos_programa: false,
        cursos_programa: false,
        cursos: false,
        beneficiarios : false,
        escuelas : false,
    })

    const getProgramas = async () => {
        loading.value.programas = true
        try {
            const response = await axios.get('programas')
            programas.value = response.data
        } catch (error) {
            global.manejarError(error)
            if(error.status === 422) {
                errors.value = error.response.data.errors
            }
        } finally {
            loading.value.programas = false
        }
    }

    const getCursosPrograma = async () => {
        loading.value.cursos_programa = true
        try {
            if(programa.value != '') {
                const response = await axios.get('programas/get-cursos/' + programa.value + '/0')
                cursos_programa.value = response.data
            }
        } catch (error) {
            global.manejarError(error)
            if(error.status === 422) {
                errors.value = error.response.data.errors
            }
        } finally {
            loading.value.cursos_programa = false
        }
    }

    const getModulosPrograma = async () => {
        loading.value.cursos_programa = true
        try {
            if(programa.value != '') {
                const response = await axios.get('programas/get-modulos/' + programa.value)
                modulos_programa.value = response.data
            }
        } catch (error) {
            global.manejarError(error)
            if(error.status === 422) {
                errors.value = error.response.data.errors
            }
        } finally {
            loading.value.modulos_programa = false
        }
    }

    const getEscuelas = async () => {
        loading.value.escuelas = true
        try {                
            const response = await axios.get('programas/get-escuelas')
            escuelas.value = response.data
        } catch (error) {
            global.manejarError(error)
            if(error.status === 422) {
                errors.value = error.response.data.errors
            }
        } finally {
            loading.value.escuelas = false
        }
    }

    const getProgramasFromEscuelas = async (escuela) => {
        programas.value = []
        loading.value.programas = true
        try {
            if(!escuela){
                return
            }
            const response = await axios.get('programas/escuela/'+ escuela )
            programas.value = response.data
        } catch (error) {
            programas.value = []
            global.manejarError(error)
            if(error.status === 422) {
                errors.value = error.response.data.errors
            }
        } finally {
            loading.value.programas = false
        }
    }

    return {

        programas,
        programa,
        modulos,
        modulo,
        cursos,
        curso,
        modulos_programa,
        cursos_programa,
        beneficiarios,
        escuelas,
        errors,
        loading,
        
        getProgramas,
        getCursosPrograma,
        getModulosPrograma,
        getEscuelas,
        getProgramasFromEscuelas,
        
    }
})
