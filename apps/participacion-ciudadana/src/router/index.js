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
						},
					]
				},
				// {
				// 	path: 'modulos',
				// 	name: 'Módulos',
				// 	redirect : { name : 'Programas'},
				// 	children : [
				// 		{
				// 			path: '',
				// 			name : 'Programas',
				// 			component: () => import('@/views/Programas/Programas.vue'),
				// 		},
				// 		{
				// 			path: 'detalle-programa/:programa_id',
				// 			name: 'Detalle del programa',
				// 			component: () => import('@/views/Programas/DetallePrograma.vue'),
				// 			props : true,
				// 		}
				// 	]
				// },
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
