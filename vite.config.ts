import { defineConfig, loadEnv } from 'vite'
import vue from '@vitejs/plugin-vue'
import publicConfig from './config/public.json'
import googleConfig from './config/google.json'

export default defineConfig(({ mode }) => {
  const env = loadEnv(mode, process.cwd(), '')
  const analyticsId = (process.env.VITE_GOOGLE_ANALYTICS_ID || env.VITE_GOOGLE_ANALYTICS_ID || googleConfig.analyticsId).trim()
  if (analyticsId && !/^G-[A-Z0-9]+$/.test(analyticsId)) throw new Error('VITE_GOOGLE_ANALYTICS_ID deve ser um ID GA4 no formato G-..., não UA-...')
  return {
    plugins: [vue(), { name: 'google-publisher-meta', transformIndexHtml: () => [{ tag: 'meta', attrs: { name: 'google-adsense-account', content: googleConfig.adsenseClient }, injectTo: 'head' }] }],
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

