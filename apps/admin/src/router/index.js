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
					path : 'permisos',
					name : 'Permisos',
					component : () => import('@/views/Permisos.vue'),
					meta : {
						auth : true,
					}
				},
				{
					path : 'roles',
					name : 'Roles',
					component : () => import('@/views/Roles.vue'),
					meta : {
						auth : true,
					}
				},
				{
					path : 'perfiles',
					name : 'Perfiles',
					component : () => import('@/views/Perfiles.vue'),
					meta : {
						auth : true,
					}
				},
				{
					path : 'paginas',
					name : 'Páginas',
					component : () => import('@/views/Paginas.vue'),
					meta : {
						auth : true,
					}
				},
				{
					path : 'menus',
					name : 'Menús',
					component : () => import('@/views/Menus.vue'),
					meta : {
						auth : true,
					}
				},
				{
					path : 'usuarios',
					name : 'Usuarios',
					component : () => import('@/views/Usuarios.vue'),
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
