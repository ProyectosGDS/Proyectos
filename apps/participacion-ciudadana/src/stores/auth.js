import { defineStore } from 'pinia'
import axios from 'axios'
import { ref } from 'vue'
import { useRouter } from 'vue-router'

export const useAuthStore = defineStore('auth', () => {

	const router = useRouter()

	const user = ref({})
	const permisos = ref([])
	const roles = ref([])
	const loading = ref(false)
	const errors = ref([])

	const login = () => {
		
		loading.value = true
		axios.post('login',user.value)
		.then(response => {
			user.value = JSON.parse(atob(response.data))
			roles.value = user.value.roles
			permisos.value = roles.value.map(role => role.permisos.map(permiso => permiso.nombre)).flat(Infinity)
			permisos.value = Array.from(new Set(permisos.value))
			router.push({ name: 'Home' })
		})
		.catch(error => {
			if(error.response.data.errors) {
				errors.value = error.response.data.errors
			}else {
				errors.value = error.response.data.message
			}
		})
		.finally(() => loading.value = false)
		

	}
	
	const validateAuth = () => {
		axios.post('me')
		.then(response =>{
			user.value = JSON.parse(atob(response.data))
			roles.value = user.value.roles
			permisos.value = roles.value.map(role => role.permisos.map(permiso => permiso.nombre)).flat(Infinity)
			permisos.value = Array.from(new Set(permisos.value))
		}) 		
		.catch(error => {
			resetData()
			router.push({name:'Login'})
		})
	}

	const resetData = () => {
		user.value = {}
		roles.value = []
		permisos.value = []
		errors.value = []
	}

	const logout = () => {
		axios.post('logout')
		resetData()
		router.push({name:'Login'})
	}

	const checkPermission = (el) => {
		for (var key in permisos.value) {
			if (permisos.value.hasOwnProperty(key)) {
				var value = permisos.value[key];

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
		permisos, 
		errors,
		loading,

		login,
		logout,
		validateAuth,
		checkPermission,
	}
})
