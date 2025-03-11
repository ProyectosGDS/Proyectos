import { defineStore } from 'pinia'
import axios from 'axios'
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useGlobalStore } from './global'

export const useAuthStore = defineStore('auth', () => {

	const router = useRouter()
	const global = useGlobalStore()

	const user = ref({})
	const loading = ref(false)
	const errors = ref([])

	const login = () => {
		
		loading.value = true
		axios.post('login',{
			cui : user.value.cui,
			password : btoa(user.value.password)
		})
		.then(response => {
			user.value = JSON.parse(atob(response.data))
			localStorage.setItem(btoa('permisos'),btoa(JSON.stringify(user.value.permisos)))
			localStorage.setItem(btoa('menu'),btoa(JSON.stringify(user.value.menu)))
			localStorage.setItem(btoa('dependencia_id'),btoa(JSON.stringify(user.value.dependencia_id)))
			localStorage.setItem(btoa('id'),btoa(JSON.stringify(user.value.id)))
			router.push({ name: 'Home' })
		})
		.catch(error => {
			global.manejarError(error)
            if(error.status === 422) {
                errors.value = error.response.data.errors
            }
		})
		.finally(() => loading.value = false)
	}
	
	const validateAuth = () => {
		axios.post('me')
		.then(response =>{
			user.value = JSON.parse(atob(response.data))
			localStorage.setItem( btoa('permisos'), btoa(JSON.stringify(user.value.permisos)))
			localStorage.setItem(btoa('menu'),btoa(JSON.stringify(user.value.menu)))
			localStorage.setItem(btoa('id_dependencia'),btoa(JSON.stringify(user.value.id_dependencia)))
			localStorage.setItem(btoa('id_usuario'),btoa(JSON.stringify(user.value.id_usuario)))
		}) 		
		.catch(error => {
			resetData()
			router.push({name:'Login'})
		})
	}

	const resetData = () => {
		user.value = {}
		errors.value = []
	}

	const logout = async () => {
		sessionStorage.clear()
		localStorage.clear()
		try {
			const response = await axios.post('logout')
			resetData()
			window.location.href = import.meta.env.VITE_MY_URL + 'login'

		} catch (error) {
			console.error(error)
		}
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
		errors,
		loading,

		login,
		logout,
		validateAuth,
		checkPermission,
	}
})
