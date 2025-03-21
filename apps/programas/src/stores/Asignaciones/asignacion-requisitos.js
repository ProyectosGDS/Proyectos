import { defineStore } from 'pinia'
import { ref } from 'vue'

import { useGlobalStore } from '../global'

export const useAsignacionRequisitosStore = defineStore('asignacion-requisitos', () => {

    const global = useGlobalStore()
    const headers = [
        { title : 'id', key : 'id', type : 'numeric' },
        { title : 'módulo/curso', key : 'modulo_curso' },
        { title : 'id', key : 'id' },
        { title : 'id', key : 'id' },
        { title : 'id', key : 'id' },
        { title : 'id', key : 'id' },
    ]
    
    return {
        
    }
})
