import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'

// https://vitejs.dev/config/
export default defineConfig({
  plugins: [vue()],
  base: './',
  server: {
    proxy: {
      '/api': {
        target: 'http://localhost:8080', // Point to the Nginx service
        changeOrigin: true,
        // No rewrite rule needed here, Nginx handles the /api prefix
      },
    },
  },
})