import { ref, computed, watch } from 'vue'
import { defineStore } from 'pinia'
import axios from '@/services/axios'
import { useGlobalStore } from './global' // usamos tu servicio de axios ya configurado

export const useAuthStore = defineStore('auth', () => {

    const global = useGlobalStore()

    const user = ref(JSON.parse(localStorage.getItem('user')) || null)
    const accessToken = ref(localStorage.getItem('access_token') || null)
    const userPermissions = ref(JSON.parse(localStorage.getItem('user_permissions')) || [])
    const userMenu = ref(JSON.parse(localStorage.getItem('user_menu')) || [])
    const loading = ref(false)
    const credentials = ref({})
    const errors = ref([])

    const isLoggedIn = computed(() => !!accessToken.value)

    // Mantener sincronizado con localStorage
    watch(accessToken, (val) => {
        if (val) {
            localStorage.setItem('access_token', val)
        } else {
            localStorage.removeItem('access_token')
        }
    })

    watch(user, (val) => {
        if (val) {
            localStorage.setItem('user', JSON.stringify(val))
        } else {
            localStorage.removeItem('user')
        }
    })

    watch(userPermissions, (val) => {
        localStorage.setItem('user_permissions', JSON.stringify(val))
    })

    watch(userMenu, (val) => {
        localStorage.setItem('user_menu', JSON.stringify(val))
    })

    const getCsrfCookie = async () => {
        try {
            await axios.get('auth/csrf-cookie')
            return true
        } catch (error) {
            console.error('Failed to get CSRF cookie:', error)
            global.manejarError(error)
            return false
        }
    }

    const login = async () => {
        loading.value = true
        try {
            const csrfSuccess = await getCsrfCookie()
            if (!csrfSuccess) return false

            const response = await axios.post('login', credentials.value)

            accessToken.value = response.data.access_token
            user.value = response.data.user
            userPermissions.value = response.data.user.permisos || []
            userMenu.value = response.data.user.menu || []
            credentials.value = {}

            return true
        } catch (error) {
            errors.value = []
            if(error.response.status == 422) {
                errors.value = error.response.data.errors
            }

            logout()
            return false
        } finally {
            loading.value = false
        }
    }

    const verifyAuth = async () => {
        loading.value = true
        try {
            const response = await axios.post('me')
            user.value = response.data.user
            userPermissions.value = response.data.user.permisos || []
            userMenu.value = response.data.user.menu || []
            return true
        } catch (error) {
            errors.value = []
            if(error.response.status == 422) {
                errors.value = error.response.data.errors
            }
            return false
        } finally {
            loading.value = false
        }
    }

    const logout = () => {
        user.value = null
        accessToken.value = null
        userPermissions.value = []
        userMenu.value = []
    }

    

	const checkPermission = (el) => {
		for (var key in user.value.permisos) {
			if (user.value.permisos.hasOwnProperty(key)) {
				var value = user.value.permisos[key];

				if (value === el) {
					return true;
				}

				if (typeof value === 'object' && checkPermission(el)) {
					return true;
				}
			}
		}
		return false;
	}

    return {
        user,
        accessToken,
        userPermissions,
        userMenu,
        isLoggedIn,
        credentials,
        loading,
        errors,

        getCsrfCookie,
        login,
        verifyAuth,
        logout,
        checkPermission,
    }
})
