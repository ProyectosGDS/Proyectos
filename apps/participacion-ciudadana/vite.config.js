import { fileURLToPath, URL } from 'node:url'

import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'

// https://vitejs.dev/config/
export default defineConfig({
	plugins: [vue()],
	resolve: {
		alias: {
			'@': fileURLToPath(new URL('./src', import.meta.url))
		}
	},
	build: {
		outDir: 'C:/laragon/www/participacion-ciudadana',
		minify: 'terser', // Activo por defecto
		terserOptions: {
			compress: {
				drop_console: true, // Elimina console.log
				drop_debugger: true, // Elimina debugger
			},
			format: {
				comments: false, // Elimina comentarios
			},
		},
	},
	// base : '/gds/participacion-ciudadana'
	base: '/participacion-ciudadana'
})
