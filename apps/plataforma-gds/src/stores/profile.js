import { defineStore } from 'pinia'
import { useGlobalStore } from './global'
import { ref } from 'vue'
import axios from 'axios'
import { useAuthStore } from './auth'

export const useProfileStore = defineStore('profile', () => {
    
    const auth = useAuthStore()
    const global = useGlobalStore()
    const user = ref({})
    const loading = ref(false)
    const errors = ref([])

    const updatePassword = async () => {
        loading.value = true
        try {
            const response = await axios.put('usuarios/actualizar-password/' + auth.user.id, user.value)
            global.setAlert(response.data,'success')
            resetData()
            auth.logout()
        } catch (error) {
            global.manejarError(error)
            if(error.status === 422) {
                errors.value = error.response.data.errors
            }
        } finally {
            loading.value = false
        }
    }

    const resetData = () => {
        user.value = {}
        errors.value = []
    }
    
    return {
        user,
        loading,
        errors,

        updatePassword,
    }
})
