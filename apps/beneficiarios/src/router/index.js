import { createRouter, createWebHistory } from 'vue-router'
import NotFound from '@/views/404.vue'
import UnaAthorized from '@/views/401.vue'
import Layout from '@/layouts/Default.vue' 
import { useAuthStore } from '@/stores/auth'


const router = createRouter({
	history: createWebHistory(import.meta.env.VITE_MY_BASE),
	routes: [
		{
			path: '/',
			name: '',
			component: Layout,
			meta : {
				auth : true
			},
			children : [
				{
					path : 'beneficiarios',
					name : 'Beneficiarios',
					component : () => import('@/views/Beneficiarios.vue'),
					meta : {
						auth : true,
					}
				},
				{
					path : 'verificacion-datos-beneficiario',
					name : 'Verificación de datos beneficiario',
					component : () => import('@/views/VerificacionDatosBeneficiario.vue'),
					meta : {
						auth : true,
					}
				},
			]
		},
		{
			path : '/401',
			name : '401',
			component : UnaAthorized
		},
		{
			//MANEJA TODAS LAS PAGINAS QUE NO EXISTEN Y LA REDIRIJE AL 404 NOT FOUND
			path: '/:catchAll(.*)',
			component: NotFound,
		}
	]
})

router.beforeEach((to) => {

    const authStore = useAuthStore()

    if (to.meta.auth && !authStore.isLoggedIn && to.name != 'Login') {
        window.location.href = import.meta.env.VITE_MY_URL + 'login';
    }

    if (to.name === 'Login' && authStore.isLoggedIn) {
        return true
    }

    return true
})

export default router
