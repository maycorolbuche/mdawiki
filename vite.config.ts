import { defineConfig, loadEnv } from 'vite'
import vue from '@vitejs/plugin-vue'
import publicConfig from './config/public.json'

export default defineConfig(({ mode }) => {
  const env = loadEnv(mode, process.cwd(), '')
  return {
    plugins: [vue()],
    base: process.env.VITE_BASE_PATH || env.VITE_BASE_PATH || '/',
    build: { target: 'es2022' },
    server: {
      proxy: {
        '/api': {
          target: process.env.API_PROXY_TARGET || env.API_PROXY_TARGET || publicConfig.apiBaseUrl,
          changeOrigin: true,
          rewrite: path => path.replace(/^\/api(?=\/|$)/, ''),
        },
      },
    },
  }
})
