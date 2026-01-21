import { defineStore } from 'pinia'
import { ref } from 'vue'
import axios from 'axios'

import { useGlobalStore } from '../global'

export const useExtranjerosStore = defineStore('extranjeros', () => {

    const global = useGlobalStore()

    const extranjero = ref({
        cui: null,
        pasaporte: null,
        primer_nombre: null,
        segundo_nombre: null,
        primer_apellido: null,
        segundo_apellido: null,
        celular: null,
        correo: null,
        sexo: null,
        fecha_nacimiento: null,
        estado_civil_id: null,
        interlocutor: null,
        nombre_completo: null,
        edad: null,
        domicilio: {
            departamento_id: 7,
            municipio_id: null,
            zona_id: null,
            grupo_zona_id: null,
            grupo_habitacional_id: null,
            direccion: null,
        },
        datos_medicos : {
            enfermedades_alergias: null,
            medicamentos: null,
            dosis: null,
            beneficiario_id: null,
            tipo_sangre_id: null,
        },
        datos_academicos: {
            id: null,
            establecimiento: null,
            tipo: null,
            titulo_carrera: null,
            escolaridad_id: null,
            beneficiario_id: null,
        },
        responsable: {
            cui: null,
            pasaporte : null,
            nombres: null,
            apellidos: null,
            celular: null,
            email: null,
            sexo: null,
            parentesco_id: null,
            direccion: null,
            zona_id: null,
            fecha_nacimiento: null,
            categoria: 'R'
        },
        emergencia: {
            cui: null,
            pasaporte : null,
            nombres: null,
            apellidos: null,
            celular: null,
            email: null,
            sexo: null,
            parentesco_id: null,
            direccion: null,
            zona_id: null,
            fecha_nacimiento: null,
            categoria: 'E'
        }
        
    })

    const pasaporte = ref(null)

    const loading = ref({
        searchExtranjero : false,
        store : false,
    })
    const codeResponse = ref(null)
    const messageResponse = ref(null)
    const errors = ref([])

    const searchExtranjero = async() => {
        loading.value.searchExtranjero = true
        try {
            
            const response = await axios.post('extranjeros/buscar-extranjero',{
                pasaporte : pasaporte.value
            })

            codeResponse.value = response.data.code
            messageResponse.value = response.data.message

            if(response.data.code != 3) {
                Object.assign(extranjero.value,response.data.data)
                extranjero.value.pasaporte = pasaporte.value
            } else {
                resetData()
            }

        } catch (error) {
            
            global.manejarError(error)
            if(error.status === 422) {
                errors.value = error.response.data.errors
            }
            resetData()
        } finally {
            loading.value.searchExtranjero = false
        }
    }

    const store = async () => {

        loading.value.store = true

        try {
            
            const response = await axios.post('extranjeros/store', extranjero.value)

            resetData()
            global.setAlert(response.data.message,'success')
            Object.assign(extranjero.value,response.data.data)

        } catch (error) {
            
            global.manejarError(error)

            if(error.status === 422) {
                errors.value = error.response.data.errors
            }

            resetData()

        } finally {

            loading.value.store = false
        }
    }

    const resetData = () => {
        extranjero.value = {
            cui: null,
            pasaporte: null,
            primer_nombre: null,
            segundo_nombre: null,
            primer_apellido: null,
            segundo_apellido: null,
            celular: null,
            correo: null,
            sexo: null,
            fecha_nacimiento: null,
            estado_civil_id: null,
            interlocutor: null,
            nombre_completo: null,
            edad: null,
            domicilio: {
                departamento_id: 7,
                municipio_id: null,
                zona_id: null,
                grupo_zona_id: null,
                grupo_habitacional_id: null,
                direccion: null,
            },
            datos_medicos : {
                enfermedades_alergias: null,
                medicamentos: null,
                dosis: null,
                beneficiario_id: null,
                tipo_sangre_id: null,
            },
            datos_academicos: {
                id: null,
                establecimiento: null,
                tipo: null,
                titulo_carrera: null,
                escolaridad_id: null,
                beneficiario_id: null,
            },
            responsable: {
                cui: null,
                pasaporte : null,
                nombres: null,
                apellidos: null,
                celular: null,
                email: null,
                sexo: null,
                parentesco_id: null,
                direccion: null,
                zona_id: null,
                fecha_nacimiento: null,
                categoria: 'R'
            },
            emergencia: {
                cui: null,
                pasaporte : null,
                nombres: null,
                apellidos: null,
                celular: null,
                email: null,
                sexo: null,
                parentesco_id: null,
                direccion: null,
                zona_id: null,
                fecha_nacimiento: null,
                categoria: 'E'
            } 
        }
    }

    return {
        extranjero,
        pasaporte,
        codeResponse,
        messageResponse,
        loading,
        errors,

        store,
        searchExtranjero,
        resetData,
    }
})
