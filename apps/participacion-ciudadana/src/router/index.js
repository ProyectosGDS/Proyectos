import { createRouter, createWebHistory } from 'vue-router'
import NotFound from '../views/404.vue'
import Layout from '@/layouts/Default.vue' 


const router = createRouter({
	history: createWebHistory(import.meta.env.VITE_MY_BASE),
	routes: [
		{
			path: '/',
			name: 'Participacion ciudadana',
			component: Layout,
			redirect : { name : 'Cursos'},
			children : [
				{
					path: 'cursos',
					name: 'Cursos',
					redirect : { name : 'Cursos1'},
					children : [
						{
							path: '',
							name : 'Cursos1',
							component: () => import('@/views/Cursos/Cursos.vue'),
						},
						{
							path: 'detalle-curso/:curso_id',
							name: 'Detalle del curso',
							component: () => import('@/views/Cursos/DetalleCurso.vue'),
							props : true,
						}
					]
				},
				// {
				// 	path: 'carreras',
				// 	name: 'Carreras',
				// 	redirect : { name : 'Carreras1'},
				// 	children : [
				// 		{
				// 			path : '',
				// 			name : 'Carreras1',
				// 			component: () => import('@/views/Carreras/Carreras.vue'),
				// 		},
				// 		{
				// 			path : 'detalle-carrera/:programa_id',
				// 			name : 'Detalle carrera',
				// 			component : () => import('@/views/Carreras/Detalle.vue'),
				// 			props : true,
				// 		}	
				// 	]
				// },
				{
					path: 'eventos',
					name: 'Eventos',
					component: () => import('@/views/Eventos.vue'),
				},
				// {
				// 	path: 'servicios',
				// 	name: 'Servicios',
				// 	component: () => import('@/views/Servicios.vue'),
				// },
				
			]
		},
		{
			//MANEJA TODAS LAS PAGINAS QUE NO EXISTEN Y LA REDIRIJE AL 404 NOT FOUND
			path: '/:catchAll(.*)',
			component: NotFound,
		}
	]
})

export default router
