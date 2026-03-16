import axios from 'axios'
import { defineStore } from 'pinia'
import { ref } from 'vue'


export const useModulosStore = defineStore('modulos', () => {


    const modulos = ref([])
    const errors = ref([])
    const loading = ref(false)
    const headers = [
        { title : 'id', key : 'id', type : 'numeric' },
        { title : 'programa', key : 'programa' },
    ];

    async function fetch () {
        try {
            loading.value = true
            const response = await axios.get('participacion-ciudadana',{
                params : {
                    tipo : 'MODULO',
                }
            })
            modulos.value = response.data
        } catch (error) {
            console.error(error)
            errors.value = error
        } finally {
            loading.value = false
        }
    }

    async function show_programa(modulo_id) {
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

    function detallesPrograma (item) {
        router.push({ name : 'Detalle del módulo', params : { modulo_id : item.id } } )
    }

    return {
        headers,
        modulos,
        errors,
        loading,

        fetch,
        show_programa,
        detallesPrograma,
    }
})
