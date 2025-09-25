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
			component: Layout,
			children : [
				{
					path: '',
					name: 'Home',
					component : () => import('@/views/Home.vue'),
				},
				{
					path: 'profile',
					name: 'Perfil',
					component : () => import('@/views/Profile.vue'),

				},
			]
		},
		{
			path: '/login',
			name: 'Login',
			component : () => import('@/views/Login.vue'),
			meta : {
				auth : true
			}
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
        return { name: 'Login' }
    }

    if (to.name === 'Login' && authStore.isLoggedIn) {
        return { name: 'Home' }
    }

    return true
})

export default router
