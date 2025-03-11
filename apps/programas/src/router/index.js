import { createRouter, createWebHistory } from 'vue-router'
import NotFound from '@/views/404.vue'
import UnaAthorized from '@/views/401.vue'
import Layout from '@/layouts/Default.vue' 


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
					path : 'programas',
					name : 'Programas',
					component : () => import('@/views/Catalogos/Programas.vue'),
					meta : {
						auth : true,
					}
				},
				{
					path : 'modulos',
					name : 'Módulos',
					component : () => import('@/views/Catalogos/Modulos.vue'),
					meta : {
						auth : true,
					}
				},
				{
					path : 'cursos',
					name : 'Cursos',
					component : () => import('@/views/Catalogos/Cursos.vue'),
					meta : {
						auth : true,
					}
				},
				{
					path : 'instructores',
					name : 'Instructores',
					component : () => import('@/views/Catalogos/Instructores.vue'),
					meta : {
						auth : true,
					}
				},
				{
					path : 'horarios',
					name : 'Horarios',
					component : () => import('@/views/Catalogos/Horarios.vue'),
					meta : {
						auth : true,
					}
				},
				{
					path : 'sedes',
					name : 'Sedes',
					component : () => import('@/views/Catalogos/Sedes.vue'),
					meta : {
						auth : true,
					}
				},
				{
					path : 'requisitos',
					name : 'Requisitos',
					component : () => import('@/views/Catalogos/Requisitos.vue'),
					meta : {
						auth : true,
					}
				},
				{
					path : 'actividades',
					name : 'Actividades',
					component : () => import('@/views/Catalogos/Actividades.vue'),
					meta : {
						auth : true,
					}
				},
				{
					path : 'cursos-por-programa',
					name : 'Cursos por programa',
					component : () => import('@/views/Asignaciones/CursosPrograma.vue'),
					meta : {
						auth : true,
					}
				},
				{
					path : 'cursos-por-modulo',
					name : 'Cursos por módulos',
					component : () => import('@/views/Asignaciones/CursosModulo.vue'),
					meta : {
						auth : true,
					}
				},
				{
					path : 'actividades-por-programa',
					name : 'Actividades por programa',
					component : () => import('@/views/Asignaciones/ActividadesPrograma.vue'),
					meta : {
						auth : true,
					}
				},
				{
					path : 'beneficiarios-por-programa',
					name : 'Beneficiarios por programa',
					component : () => import('@/views/Inscripciones/BeneficiariosPrograma.vue'),
					meta : {
						auth : true,
					}
				},
				{
					path : 'beneficiarios-por-curso',
					name : 'Beneficiarios por curso',
					component : () => import('@/views/Inscripciones/BeneficiariosCurso.vue'),
					meta : {
						auth : true,
					}
				},
				{
					path : 'beneficiarios-por-modulo',
					name : 'Beneficiarios por módulo',
					component : () => import('@/views/Inscripciones/BeneficiariosModulo.vue'),
					meta : {
						auth : true,
					}
				},
				{
					path : 'beneficiarios-por-actividad',
					name : 'Beneficiarios por actividad',
					component : () => import('@/views/Inscripciones/BeneficiariosActividad.vue'),
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

router.beforeEach((to, from) => {
	
	const accessToken = (document.cookie.split('=')[0] === btoa('access_token'))

	if (to.meta.auth) {

		if (!accessToken && to.name != 'Login') {		

			window.location.href = import.meta.env.VITE_MY_URL + 'login';
			
		}
	}
	
	return true

})

export default router
