import { defineStore } from 'pinia'
import { ref } from 'vue'


export const useGlobalStore = defineStore('global', () => {

    // AÑOS
    //----------------------------------------
    const date = new Date()
    //----------------------------------------

    // INICIO SIDEBAR
    //----------------------------------------
    const openSidebar = ref(false)
    
    function updateOpenSidebar () {
        openSidebar.value = !openSidebar.value
    }
    //----------------------------------------
    // FIN SIDEBAR

    // INICIO TITLE PAGE
    //----------------------------------------

    const titlePage = ref({
        title : '',
        icon : 'fas fa-home',
        textColor : 'text-white',
        color : 'bg-blue-muni'
    })

    function changeTitlePage (title = 'Home',icon = 'fas fa-home',color = 'bg-blue-muni',textColor = 'text-white') {
        titlePage.value.title = title
        titlePage.value.icon = icon
        titlePage.value.textColor = textColor
        titlePage.value.color = color
    }   
    //----------------------------------------
    // FIN TITLE PAGE


    // INICIO ALERTA TOAST
    //----------------------------------------
    const toasts = ref([])

    function setAlert(message,type,title = ' A T E N C I Ó N '){
        toasts.value.push({ message : message, type : type, title : title })
    }
    //----------------------------------------
    // FIN ALERTA TOAST


    function getNestedValue(obj, key) {
        const keys = key.split('.');
        for (const innerKey of keys) {
            if (obj.hasOwnProperty(innerKey)) {
                obj = obj[innerKey];
            } else {
                return null;
            }
        }
        return obj;
    }

    function manejarError(error) {
        if (error.response) {

            const { status, data } = error.response
    
            if (status === 422) {
                setAlert(data.message,'danger','ERROR DE VALIDACIÓN')
                console.error('Error de validación:', data.errors)
            } else if (status === 401) {
                setAlert(data.message,'danger','NO AUTORIZADO')
                console.error('No autorizado:', data.message)
            } else if (status === 404) {
                setAlert(data.message,'danger','RECURSO NO ENCONTRADO')
                console.error('Recurso no encontrado:', data.message)
            } else if (status >= 500) {
                setAlert(data.message,'danger','ERROR DEL SERVIDOR')
                console.error('Error del servidor:', data.message)
            } else {
                setAlert(data,'danger','ERROR')
                console.error('Error :',data)
            }
        } else if (error.request) {
            console.error('No se recibió respuesta del servidor:', error.request)
        } else {
            console.error('Error en la solicitud:', error.message)
        }
    }

    function goHome() {
        window.location.href = import.meta.env.VITE_MY_URL;
    }

    return {
        openSidebar,
        updateOpenSidebar,

        titlePage,
        changeTitlePage,

        toasts,
        setAlert,

        date,

        getNestedValue,
        manejarError,
        goHome,

    }
})