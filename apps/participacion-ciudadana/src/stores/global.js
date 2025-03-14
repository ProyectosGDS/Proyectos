import { defineStore } from 'pinia'
import { ref } from 'vue'


export const useGlobalStore = defineStore('global', () => {

    // AÑOS
    //----------------------------------------
    const year = new Date()
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


    // HISTORIAL DE RUTAS
    //-------------------------

    const routesHistory = ref([])
    
    const addRouteHistory = (route) => {
        routesHistory.value.push(route)
    }


    return {
        openSidebar,
        updateOpenSidebar,

        titlePage,
        changeTitlePage,

        toasts,
        setAlert,

        year,

        getNestedValue,

        routesHistory,
        addRouteHistory
    }
})