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
        if (!error.response) {
            console.error('No se recibió respuesta del servidor:', error.request)
            setAlert('No se pudo conectar con el servidor','danger','ERROR')
            return
        }

        const { status, data } = error.response

        switch (status) {
            case 422:
                setAlert(data.message || 'Error de validación','warning','ERROR DE VALIDACIÓN')
                console.error('Error de validación:', data.errors)
                break
            case 401:
                setAlert(data.message || 'No autorizado','danger','NO AUTORIZADO')
                console.error('No autorizado',data.errors ?? 'No hay errores')
                break
            case 404:
                setAlert(data.message || 'Recurso no encontrado','danger','RECURSO NO ENCONTRADO')
                console.error('Recurso no encontrado:', data.message)
                break
            default:
                if (status >= 500) {
                    setAlert(data.message || 'Error del servidor','danger','ERROR DEL SERVIDOR')
                    console.error('Error del servidor:', data.message)
                } else {
                    setAlert(data.message || 'Error desconocido','danger','ERROR')
                    console.error('Error desconocido:', data)
                }
                break
        }
    }

    function goHome() {
        window.location.href = import.meta.env.VITE_MY_URL;
    }

    function checkIfCookieExists(cookieName) {
        
        const cookies = document.cookie.split(';');
        
        for (let i = 0; i < cookies.length; i++) {
            const cookie = cookies[i].trim();
            
            if (cookie.startsWith(cookieName + '=')) {
                return true;
            }
        }
        
        return false;
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
        checkIfCookieExists,

    }
})