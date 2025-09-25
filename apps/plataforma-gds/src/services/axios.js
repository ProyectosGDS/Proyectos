import axios from 'axios'
import { useAuthStore } from '@/stores/auth'
import router from '@/router'
import { useGlobalStore } from '@/stores/global'


axios.defaults.withCredentials = true
axios.defaults.baseURL = import.meta.env.VITE_MY_API_URL_BASE || 'http://localhost:8000/api'
axios.defaults.headers.common['app'] = import.meta.env.VITE_MY_APPNAME

axios.interceptors.request.use(config => {
    const authStore = useAuthStore()
    if (authStore.accessToken) {
        config.headers.Authorization = `Bearer ${authStore.accessToken}`
    }
    return config
}, error => Promise.reject(error))

axios.interceptors.response.use(
    response => response,
    error => {
        const global = useGlobalStore()

        if (error.response?.status === 401 && error.config.url !== '/login') {
            const authStore = useAuthStore()
            authStore.logout()
            router.replace({ name: 'Login' })
        }

        global.manejarError(error)
        
        return Promise.reject(error)
    }
)

export default axios