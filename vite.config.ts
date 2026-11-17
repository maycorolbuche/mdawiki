import { defineConfig, loadEnv } from 'vite'
import vue from '@vitejs/plugin-vue'

export default defineConfig(({ mode }) => {
  const env = loadEnv(mode, process.cwd(), '')
  return {
    plugins: [vue()],
    base: process.env.VITE_BASE_PATH || env.VITE_BASE_PATH || '/',
    build: { target: 'es2022' },
  }
})
