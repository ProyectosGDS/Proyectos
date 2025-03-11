import { defineStore } from 'pinia'
import { useGlobalStore } from './global'
    
export const useVerificacionDatosStore = defineStore('verificacion-datos', () => {

    const global = useGlobalStore()
    const headers = [
        { title : 'id', key : 'id', type : 'numeric' },
        { title : 'cui', key : 'id' },
        { title : 'beneficiario', key : 'nombre_completo' },
        { title : 'sexo', key : 'sexo' },
        { title : 'edad', key : 'edad' },
        { title : 'estado', key : 'estado', width : '10px', align : 'center' },
        { title : '', key : 'actions', width : '10px', align : 'center' },
    ]

    return {
        
    }
})
